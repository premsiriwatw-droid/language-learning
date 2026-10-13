<?php

namespace Tests\Feature\TeacherCourses;

use App\Models\AssignmentStatus;
use App\Models\CourseAssignment;
use App\Models\CourseEnrollment;
use App\Models\LearningAttempt;
use App\Models\Lesson;
use App\Models\LessonProgress;
use App\Models\TeacherCourse;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Tests\Support\CreatesTeacherCourses;
use Tests\TestCase;

class TeacherLearningToolsTest extends TestCase
{
    use CreatesTeacherCourses;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->travelTo(Carbon::parse('2026-10-15 12:00:00'));
    }

    public function test_recommendations_are_private_and_keep_server_owned_ids(): void
    {
        $teacher = $this->approvedTeacher();
        $course = $this->teacherCourse($teacher);
        $student = $this->learner();
        $this->enroll($course, $student);
        $params = $this->teacherParams($course, $student);
        $payload = [
            'lesson_id' => $course->lessons->first()->id,
            'reason' => 'Private recommendation',
            'student_id' => $this->learner()->id,
            'teacher_course_id' => $this->teacherCourse($this->approvedTeacher())->id,
        ];

        $this->get(route('teacher-courses.learning-tools', $course))
            ->assertRedirect(route('login'));
        $this->actingAs($this->approvedTeacher())
            ->post(route('teacher.courses.students.recommend', $params), $payload)
            ->assertForbidden();
        $this->actingAs($teacher)
            ->post(route('teacher.courses.students.recommend', $params), $payload)
            ->assertRedirect(route('teacher.courses.students.show', $params));

        $this->assertDatabaseHas('teacher_recommendations', [
            'teacher_course_id' => $course->id,
            'student_id' => $student->id,
            'reason' => 'Private recommendation',
        ]);
        $this->actingAs($student)->get(route('teacher-courses.learning-tools', $course))
            ->assertOk()->assertSee('Private recommendation');
        $this->actingAs($this->learner())->get(route('teacher-courses.learning-tools', $course))
            ->assertNotFound()->assertDontSee('Private recommendation');

        $pending = $this->learner();
        $this->enroll($course, $pending, CourseEnrollment::PENDING);
        $this->actingAs($teacher)->post(
            route('teacher.courses.students.recommend', $this->teacherParams($course, $pending)),
            $payload
        )->assertNotFound();
        $this->assertDatabaseCount('teacher_recommendations', 1);
    }

    public function test_a_lesson_or_unit_outside_the_course_cannot_be_sent(): void
    {
        $teacher = $this->approvedTeacher();
        $course = $this->teacherCourse($teacher);
        $student = $this->learner();
        $this->enroll($course, $student);
        $other = $this->teacherCourse($this->approvedTeacher());
        $params = $this->teacherParams($course, $student);

        $this->actingAs($teacher)->post(route('teacher.courses.students.recommend', $params), [
            'lesson_id' => $other->lessons->first()->id, 'reason' => 'Out of scope',
        ])->assertNotFound();
        $this->post(route('teacher.courses.students.assign', $params), [
            'title' => 'Invalid task',
            'target' => 'unit:'.$other->lessons->first()->unit_id,
            'due_at' => now()->addDay()->format('Y-m-d\TH:i'),
        ])->assertNotFound();

        $this->post(route('teacher.courses.students.recommend', $params), [
            'lesson_id' => $course->lessons->first()->id, 'reason' => '   ',
        ])->assertSessionHasErrors('reason');
        $this->post(route('teacher.courses.students.assign', $params), [
            'title' => 'Past due',
            'target' => 'lesson:'.$course->lessons->first()->id,
            'due_at' => now()->subDay()->format('Y-m-d\TH:i'),
        ])->assertSessionHasErrors('due_at');
        $this->assertDatabaseCount('teacher_recommendations', 0);
        $this->assertDatabaseCount('course_assignments', 0);
    }

    public function test_unit_assignment_requires_new_completions_for_its_snapshot(): void
    {
        $teacher = $this->approvedTeacher();
        [$language, $lessons] = $this->languageWithLessons('Unit assignment', 3);
        $course = $this->teacherCourse($teacher, $language);
        $course->lessons()->sync([
            $lessons[0]->id => ['position' => 1],
            $lessons[1]->id => ['position' => 2],
        ]);
        $student = $this->learner();
        $this->enroll($course, $student);
        $this->attempt($student, $lessons[0]);
        $reward = LessonProgress::create([
            'user_id' => $student->id, 'lesson_id' => $lessons[0]->id,
            'completed_at' => now(), 'xp' => 100, 'stars' => 3,
        ]);
        $beforeReward = $reward->refresh()->getRawOriginal();

        $this->actingAs($teacher)->post(
            route('teacher.courses.students.assign', $this->teacherParams($course, $student)),
            [
                'title' => 'Review basics', 'target' => 'unit:'.$lessons[0]->unit_id,
                'due_at' => now()->addDay()->format('Y-m-d\TH:i'),
            ]
        )->assertRedirect();

        $assignment = CourseAssignment::query()->sole();
        $this->assertSame([$lessons[0]->id, $lessons[1]->id], $assignment->lesson_ids);
        $params = ['teacherCourse' => $course->id, 'assignment' => $assignment->id];
        $complete = route('teacher-courses.assignments.complete', $params);

        $this->actingAs($student)->post($complete)->assertSessionHasErrors('assignment');
        $this->assertSame(AssignmentStatus::PENDING, AssignmentStatus::query()->sole()->status);
        $this->post(route('teacher-courses.assignments.start', $params))->assertRedirect();
        $this->assertSame(AssignmentStatus::IN_PROGRESS, AssignmentStatus::query()->sole()->status);

        $this->attempt($student, $lessons[0]);
        $this->post($complete)->assertSessionHasErrors('assignment');
        $this->attempt($student, $lessons[1]);
        $this->post($complete)->assertRedirect();

        $status = AssignmentStatus::query()->sole();
        $this->assertSame(AssignmentStatus::COMPLETED, $status->status);
        $completedAt = $status->completed_at->toDateTimeString();
        $this->travel(1)->hours();
        $this->post($complete)->assertRedirect();
        $this->assertSame($completedAt, $status->fresh()->completed_at->toDateTimeString());
        $this->assertDatabaseCount('learning_attempts', 3);
        $this->assertSame($beforeReward, $reward->fresh()->getRawOriginal());

        $anotherStudent = $this->learner();
        $this->enroll($course, $anotherStudent);
        $this->actingAs($anotherStudent)->post($complete)->assertNotFound();
        $other = $this->teacherCourse($teacher);
        $this->enroll($other, $student);
        $this->actingAs($student)->post(route('teacher-courses.assignments.complete', [
            'teacherCourse' => $other->id, 'assignment' => $assignment->id,
        ]))->assertNotFound();
    }

    public function test_messages_are_scoped_to_course_and_participants(): void
    {
        $teacher = $this->approvedTeacher();
        $course = $this->teacherCourse($teacher);
        $student = $this->learner();
        $this->enroll($course, $student);
        $params = $this->teacherParams($course, $student);

        $this->actingAs($teacher)->post(route('teacher.courses.students.message', $params), [
            'body' => 'Private teacher message', 'sender_id' => $student->id,
        ])->assertRedirect();
        $this->assertDatabaseHas('course_messages', [
            'sender_id' => $teacher->id, 'body' => 'Private teacher message',
        ]);
        $this->actingAs($student)->get(route('teacher-courses.learning-tools', $course))
            ->assertOk()->assertSee('Private teacher message');

        $this->post(route('teacher-courses.messages.store', $course), [
            'body' => '<script>alert("reply")</script>', 'sender_id' => $teacher->id,
        ])->assertRedirect();
        $this->assertDatabaseHas('course_messages', [
            'sender_id' => $student->id, 'body' => '<script>alert("reply")</script>',
        ]);
        $this->get(route('teacher-courses.learning-tools', $course))
            ->assertSee('<script>alert("reply")</script>')
            ->assertDontSee('<script>alert("reply")</script>', false);
        $this->assertDatabaseCount('course_conversations', 1);
        $this->assertDatabaseCount('course_messages', 2);

        $other = $this->teacherCourse($this->approvedTeacher());
        $this->enroll($other, $student);
        $this->get(route('teacher-courses.learning-tools', $other))
            ->assertOk()->assertDontSee('Private teacher message');

        $outsider = $this->learner();
        $this->enroll($course, $outsider);
        $this->actingAs($outsider)->get(route('teacher-courses.learning-tools', $course))
            ->assertOk()->assertDontSee('Private teacher message');
        $this->actingAs($this->approvedTeacher())
            ->post(route('teacher.courses.students.message', $params), ['body' => 'Not allowed'])
            ->assertForbidden();

        $this->actingAs($student)->post(route('teacher-courses.messages.store', $course), [
            'body' => str_repeat('x', 2001),
        ])->assertSessionHasErrors('body');
        $this->assertDatabaseCount('course_messages', 2);
    }

    public function test_panels_show_role_actions_without_creating_records(): void
    {
        $teacher = $this->approvedTeacher();
        $course = $this->teacherCourse($teacher);
        $student = $this->learner();
        $this->enroll($course, $student);

        $this->actingAs($teacher)->get(route('teacher.courses.students.show', $this->teacherParams($course, $student)))
            ->assertOk()->assertSee('name="reason"', false)
            ->assertSee('name="target"', false)->assertSee('name="body"', false);
        $this->actingAs($student)->get(route('teacher-courses.learning-tools', $course))
            ->assertOk()->assertDontSee('name="reason"', false)
            ->assertDontSee('name="target"', false)->assertSee('name="body"', false);
        $this->get(route('teacher-courses.show', $course))
            ->assertOk()->assertSee(route('teacher-courses.learning-tools', $course), false);

        $pending = $this->learner();
        $this->enroll($course, $pending, CourseEnrollment::PENDING);
        $this->actingAs($pending)->get(route('teacher-courses.show', $course))
            ->assertOk()->assertDontSee(route('teacher-courses.learning-tools', $course), false);

        foreach (['teacher_recommendations', 'course_assignments', 'assignment_statuses', 'course_conversations', 'course_messages'] as $table) {
            $this->assertDatabaseCount($table, 0);
        }
    }

    public function test_losing_course_membership_revokes_existing_tools_access(): void
    {
        $teacher = $this->approvedTeacher();
        $course = $this->teacherCourse($teacher);
        $student = $this->learner();
        $this->enroll($course, $student, CourseEnrollment::COMPLETED);
        $params = $this->teacherParams($course, $student);

        $this->actingAs($teacher)->post(route('teacher.courses.students.assign', $params), [
            'title' => 'Private task', 'target' => 'lesson:'.$course->lessons->first()->id,
            'due_at' => now()->addDay()->format('Y-m-d\TH:i'),
        ])->assertRedirect();
        $this->post(route('teacher.courses.students.message', $params), ['body' => 'Private history'])
            ->assertRedirect();
        $assignment = CourseAssignment::query()->sole();
        $this->actingAs($student)->get(route('teacher-courses.learning-tools', $course))
            ->assertOk()->assertSee('Private history');

        CourseEnrollment::query()->where('teacher_course_id', $course->id)->where('user_id', $student->id)
            ->update(['status' => CourseEnrollment::REJECTED]);
        $this->get(route('teacher-courses.learning-tools', $course))
            ->assertNotFound()->assertDontSee('Private history');
        $this->post(route('teacher-courses.messages.store', $course), ['body' => 'Unauthorized reply'])
            ->assertNotFound();
        foreach (['start', 'complete'] as $action) {
            $this->post(route('teacher-courses.assignments.'.$action, [
                'teacherCourse' => $course->id, 'assignment' => $assignment->id,
            ]))->assertNotFound();
        }
        $this->actingAs($teacher)->get(route('teacher.courses.students.show', $params))
            ->assertNotFound()->assertDontSee('Private history');
        $this->assertDatabaseCount('course_messages', 1);
        $this->assertSame(AssignmentStatus::PENDING, AssignmentStatus::query()->sole()->status);
    }

    public function test_deleted_assignment_content_is_reported_without_false_completion(): void
    {
        $teacher = $this->approvedTeacher();
        $course = $this->teacherCourse($teacher);
        $student = $this->learner();
        $this->enroll($course, $student);
        $lesson = $course->lessons->first();

        $this->actingAs($teacher)->post(route('teacher.courses.students.assign', $this->teacherParams($course, $student)), [
            'title' => 'Deleted content task', 'target' => 'lesson:'.$lesson->id,
            'due_at' => now()->addDay()->format('Y-m-d\TH:i'),
        ])->assertRedirect();
        $assignment = CourseAssignment::query()->sole();
        $lesson->delete();

        $this->actingAs($student)->get(route('teacher-courses.learning-tools', $course))
            ->assertOk()->assertSee('บทเรียนไม่พร้อมใช้งาน กรุณาติดต่อผู้สอน');
        $this->post(route('teacher-courses.assignments.complete', [
            'teacherCourse' => $course->id, 'assignment' => $assignment->id,
        ]))->assertSessionHasErrors('assignment');
        $this->assertSame(AssignmentStatus::PENDING, AssignmentStatus::query()->sole()->status);
    }

    public function test_late_work_can_be_completed_and_shows_the_late_label(): void
    {
        $teacher = $this->approvedTeacher();
        $course = $this->teacherCourse($teacher);
        $student = $this->learner();
        $this->enroll($course, $student);
        $lesson = $course->lessons->first();

        $this->actingAs($teacher)->post(route('teacher.courses.students.assign', $this->teacherParams($course, $student)), [
            'title' => 'Late task', 'target' => 'lesson:'.$lesson->id,
            'due_at' => now()->addHour()->format('Y-m-d\TH:i'),
        ])->assertRedirect();
        $assignment = CourseAssignment::query()->sole();
        $this->travel(2)->hours();
        $this->attempt($student, $lesson);
        $this->actingAs($student)->post(route('teacher-courses.assignments.complete', [
            'teacherCourse' => $course->id, 'assignment' => $assignment->id,
        ]))->assertRedirect();
        $this->get(route('teacher-courses.learning-tools', $course))->assertOk()->assertSee('ส่งช้า');
        $this->assertSame(AssignmentStatus::COMPLETED, AssignmentStatus::query()->sole()->status);
    }

    private function teacherParams(TeacherCourse $course, User $student): array
    {
        return ['teacherCourse' => $course->id, 'student' => $student->id];
    }

    private function enroll(
        TeacherCourse $course,
        User $student,
        string $status = CourseEnrollment::ACTIVE
    ): void {
        CourseEnrollment::forceCreate([
            'teacher_course_id' => $course->id, 'user_id' => $student->id,
            'status' => $status, 'payment_status' => CourseEnrollment::PAYMENT_NOT_REQUIRED,
            'requested_at' => now()->subDay(),
        ]);
    }

    private function attempt(User $student, Lesson $lesson): void
    {
        LearningAttempt::create([
            'user_id' => $student->id, 'lesson_id' => $lesson->id,
            'run_key' => Str::random(64), 'lesson_title' => $lesson->title,
            'vocabulary_count' => 1, 'question_count' => 0, 'correct_count' => 0,
            'wrong_count' => 0, 'wrong_attempts' => 0, 'score_percent' => null,
            'elapsed_seconds' => 60, 'started_at' => now()->subMinute(),
            'completed_at' => now(), 'results' => [],
        ]);
    }
}
