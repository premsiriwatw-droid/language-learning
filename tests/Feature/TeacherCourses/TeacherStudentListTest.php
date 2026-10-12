<?php

namespace Tests\Feature\TeacherCourses;

use App\Models\CourseEnrollment;
use App\Models\LearningAttempt;
use App\Models\Lesson;
use App\Models\LessonProgress;
use App\Models\TeacherApplication;
use App\Models\TeacherCourse;
use App\Models\User;
use App\Services\Progress\LearningProgress;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Tests\Support\CreatesTeacherCourses;
use Tests\TestCase;

class TeacherStudentListTest extends TestCase
{
    use CreatesTeacherCourses;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->travelTo(Carbon::parse('2026-10-14 12:00:00'));
    }

    public function test_student_list_requires_the_approved_course_owner(): void
    {
        $teacher = $this->approvedTeacher();
        $course = $this->teacherCourse($teacher, attributes: ['title' => 'Private teacher course']);
        $student = User::factory()->create(['name' => 'Private student']);
        $this->enroll($course, $student);
        $url = route('teacher.courses.students', $course);

        $this->get($url)->assertRedirect(route('login'));
        foreach ([$student, $this->pendingTeacher(), $this->approvedTeacher(), User::factory()->create(['role' => 'admin']), User::factory()->create(['role' => 'teacher'])] as $unauthorized) {
            $this->actingAs($unauthorized)->get($url)
                ->assertForbidden()->assertDontSee('Private student')->assertDontSee('Private teacher course');
        }
        $this->actingAs($teacher)->get($url)->assertOk()->assertSee('Private student');
        $this->get('/teacher/courses/999999/students')->assertNotFound();

        foreach ([TeacherApplication::PENDING, TeacherApplication::REJECTED, TeacherApplication::NEEDS_MORE_INFO] as $status) {
            $teacher->teacherApplication->forceFill(['status' => $status])->save();
            $this->actingAs($teacher)->get($url)->assertForbidden()->assertDontSee('Private student');
        }
    }

    public function test_course_and_dashboard_link_to_the_student_list(): void
    {
        $teacher = $this->approvedTeacher();
        $course = $this->teacherCourse($teacher);
        $otherCourse = $this->teacherCourse($this->approvedTeacher());

        $this->actingAs($teacher)->get(route('teacher.courses.index'))
            ->assertOk()->assertSee(route('teacher.courses.students', $course), false)
            ->assertDontSee(route('teacher.courses.students', $otherCourse), false);
        $this->get(route('teacher.courses.dashboard', $course))
            ->assertOk()->assertSee(route('teacher.courses.students', $course), false);
        $this->get(route('teacher.courses.students', $course))
            ->assertOk()->assertSee(route('teacher.courses.dashboard', $course), false);
    }

    public function test_only_active_and_completed_enrollments_in_this_course_are_listed(): void
    {
        $teacher = $this->approvedTeacher();
        $course = $this->teacherCourse($teacher);
        $active = User::factory()->create(['name' => 'Alice active']);
        $completed = User::factory()->create(['name' => 'Bob completed']);
        $this->enroll($course, $active);
        $this->enroll($course, $completed, CourseEnrollment::COMPLETED);
        $excludedNames = [];
        foreach ([CourseEnrollment::PENDING, CourseEnrollment::APPROVED, CourseEnrollment::REJECTED] as $status) {
            $excluded = User::factory()->create(['name' => 'Excluded '.$status]);
            $this->enroll($course, $excluded, $status);
            $excludedNames[] = $excluded->name;
        }
        $outsider = User::factory()->create(['name' => 'Different course learner']);
        $this->enroll($this->teacherCourse($this->approvedTeacher()), $outsider);
        $this->progress($outsider, $course->lessons->first(), ['completed_at' => now()]);

        $response = $this->actingAs($teacher)->get(route('teacher.courses.students', $course, false).'?student_id='.$outsider->id)
            ->assertOk()->assertSee($active->name)->assertSee($completed->name)->assertDontSee($outsider->name);
        foreach ($excludedNames as $name) {
            $response->assertDontSee($name);
        }
        $this->assertSame(2, $response->viewData('students')->total());
        $this->assertSame([$active->id, $completed->id], $response->viewData('students')->getCollection()->pluck('id')->all());
    }

    public function test_course_progress_and_activity_are_scoped_but_xp_and_level_are_global(): void
    {
        config()->set('learning.levels.xp_per_level', 100);
        $teacher = $this->approvedTeacher();
        [$language, $lessons] = $this->languageWithLessons('English', 3);
        $course = $this->teacherCourse($teacher, $language);
        $student = $this->learner();
        $this->enroll($course, $student);
        $lastVisit = now()->subHours(2);
        $this->progress($student, $lessons[0], ['completed_at' => now()->subWeek(), 'xp' => 120]);
        $this->progress($student, $lessons[1], ['last_visited_at' => $lastVisit, 'xp' => 999]);
        $this->progress($student, $lessons[2], ['last_visited_at' => now()->subWeek()]);
        $this->attempt($student, $lessons[0], now()->subHours(3));

        $otherCourse = $this->teacherCourse($this->approvedTeacher());
        $otherLesson = $otherCourse->lessons->first();
        $this->progress($student, $otherLesson, ['completed_at' => now()->subHour(), 'last_visited_at' => now(), 'xp' => 130]);
        $this->attempt($student, $otherLesson, now());
        $otherStudent = $this->learner();
        $this->progress($otherStudent, $lessons[0], ['completed_at' => now(), 'xp' => 2000]);

        $response = $this->actingAs($teacher)->get(route('teacher.courses.students', $course))->assertOk();
        $row = $response->viewData('students')->getCollection()->sole();
        $this->assertSame(1, $row['completed_lessons']);
        $this->assertSame(33.3, $row['progress_percent']);
        $this->assertSame(250, $row['xp']);
        $this->assertSame(app(LearningProgress::class)->summary($student)['xp'], $row['xp']);
        $this->assertSame(3, $row['level']);
        $this->assertTrue($row['last_activity_at']->equalTo($lastVisit));
        $this->assertSame($lessons[1]->id, $row['current_lesson']->id);
        $this->assertTrue($row['current_lesson_started']);
        $this->assertSame(3, $response->viewData('lessonsCount'));
    }

    public function test_unstarted_next_and_finished_states_respect_curriculum_order(): void
    {
        $teacher = $this->approvedTeacher();
        [$language, $lessons] = $this->languageWithLessons();
        $course = $this->teacherCourse($teacher, $language);
        $course->lessons()->sync([
            $lessons[1]->id => ['position' => 1], $lessons[0]->id => ['position' => 2],
        ]);
        $notStarted = $this->learner();
        $next = $this->learner();
        $finished = $this->learner();
        foreach ([$notStarted, $next, $finished] as $student) {
            $this->enroll($course, $student);
        }
        $this->progress($next, $lessons[1], ['completed_at' => now()->subDay()]);
        foreach ($lessons as $lesson) {
            $this->progress($finished, $lesson, ['completed_at' => now()->subDay()]);
        }

        $response = $this->actingAs($teacher)->get(route('teacher.courses.students', $course))
            ->assertOk()->assertSee('ยังไม่เริ่มเรียน')->assertSee('บทถัดไปในหลักสูตร')->assertSee('เรียนครบทุกบทแล้ว');
        $rows = $response->viewData('students')->getCollection()->keyBy('id');
        $this->assertSame(0.0, $rows[$notStarted->id]['progress_percent']);
        $this->assertSame(0, $rows[$notStarted->id]['xp']);
        $this->assertSame(1, $rows[$notStarted->id]['level']);
        $this->assertNull($rows[$notStarted->id]['last_activity_at']);
        $this->assertSame($lessons[1]->id, $rows[$notStarted->id]['current_lesson']->id);
        $this->assertFalse($rows[$notStarted->id]['current_lesson_started']);
        $this->assertSame(50.0, $rows[$next->id]['progress_percent']);
        $this->assertSame($lessons[0]->id, $rows[$next->id]['current_lesson']->id);
        $this->assertFalse($rows[$next->id]['current_lesson_started']);
        $this->assertSame(100.0, $rows[$finished->id]['progress_percent']);
        $this->assertNull($rows[$finished->id]['current_lesson']);
    }

    public function test_last_activity_uses_history_and_completion_and_ignores_future_dates(): void
    {
        $teacher = $this->approvedTeacher();
        $course = $this->teacherCourse($teacher);
        $lessons = $course->lessons;
        $historyOnly = $this->learner();
        $completionOnly = $this->learner();
        $future = $this->learner();
        foreach ([$historyOnly, $completionOnly, $future] as $student) {
            $this->enroll($course, $student);
        }
        $historyTime = now()->subHour();
        $completedTime = now()->subHours(2);
        $this->attempt($historyOnly, $lessons[0], $historyTime);
        $this->attempt($historyOnly, $lessons[0], now()->addDay());
        $this->progress($completionOnly, $lessons[0], ['last_visited_at' => now()->subWeek(), 'completed_at' => $completedTime]);
        $this->progress($future, $lessons[0], ['last_visited_at' => now()->addDay(), 'completed_at' => now()->addDay()]);
        $this->attempt($future, $lessons[0], now()->addDay());

        $response = $this->actingAs($teacher)->get(route('teacher.courses.students', $course))->assertOk();
        $rows = $response->viewData('students')->getCollection()->keyBy('id');
        $this->assertTrue($rows[$historyOnly->id]['last_activity_at']->equalTo($historyTime));
        $this->assertTrue($rows[$completionOnly->id]['last_activity_at']->equalTo($completedTime));
        $this->assertNull($rows[$future->id]['last_activity_at']);
        $this->assertSame(0.0, $rows[$future->id]['progress_percent']);
        $this->assertFalse($rows[$future->id]['current_lesson_started']);
    }

    public function test_course_without_learning_enrollments_has_an_empty_state(): void
    {
        $teacher = $this->approvedTeacher();
        $course = $this->teacherCourse($teacher);
        $this->enroll($course, $this->learner(), CourseEnrollment::PENDING);

        $response = $this->actingAs($teacher)->get(route('teacher.courses.students', $course))
            ->assertOk()->assertSee('ยังไม่มีนักเรียนที่เริ่มเรียนในคอร์สนี้');
        $this->assertSame(0, $response->viewData('students')->total());
    }

    public function test_course_without_lessons_still_shows_the_learners_global_level(): void
    {
        $teacher = $this->approvedTeacher();
        $course = $this->teacherCourse($teacher, state: fn ($factory) => $factory->draft());
        $course->lessons()->detach();
        $student = $this->learner();
        $this->enroll($course, $student);
        $otherCourse = $this->teacherCourse($this->approvedTeacher());
        $this->progress($student, $otherCourse->lessons->first(), ['completed_at' => now(), 'xp' => 1000]);

        $response = $this->actingAs($teacher)->get(route('teacher.courses.students', $course))
            ->assertOk()->assertSee('คอร์สนี้ยังไม่มีบทเรียน');
        $row = $response->viewData('students')->getCollection()->sole();
        $this->assertSame(0, $response->viewData('lessonsCount'));
        $this->assertNull($row['progress_percent']);
        $this->assertNull($row['last_activity_at']);
        $this->assertNull($row['current_lesson']);
        $this->assertSame(1000, $row['xp']);
        $this->assertSame(6, $row['level']);
    }

    public function test_pagination_has_a_stable_order_and_scopes_metrics_to_the_current_page(): void
    {
        $teacher = $this->approvedTeacher();
        $course = $this->teacherCourse($teacher);
        $lastStudent = null;
        for ($number = 1; $number <= 21; $number++) {
            $lastStudent = User::factory()->create(['name' => sprintf('Student %02d', $number)]);
            $this->enroll($course, $lastStudent);
        }
        $this->progress($lastStudent, $course->lessons->first(), ['completed_at' => now(), 'xp' => 200]);

        $firstPage = $this->actingAs($teacher)->get(route('teacher.courses.students', $course))
            ->assertOk()->assertSee('Student 01')->assertDontSee('Student 21');
        $this->assertSame(21, $firstPage->viewData('students')->total());
        $this->assertCount(20, $firstPage->viewData('students'));
        $secondPage = $this->get(route('teacher.courses.students', $course, false).'?page=2')
            ->assertOk()->assertSee('Student 21')->assertDontSee('Student 01');
        $row = $secondPage->viewData('students')->getCollection()->sole();
        $this->assertSame($lastStudent->id, $row['id']);
        $this->assertSame(50.0, $row['progress_percent']);
        $this->assertSame(2, $row['level']);
        $this->get(route('teacher.courses.students', $course, false).'?page=99')
            ->assertOk()->assertSee('ไม่มีรายการนักเรียนในหน้านี้')->assertSee('กลับไปหน้าแรก');
    }

    public function test_names_and_lesson_titles_are_escaped_in_the_student_table(): void
    {
        $teacher = $this->approvedTeacher();
        $course = $this->teacherCourse($teacher);
        $student = User::factory()->create(['name' => '<script>alert("student")</script>']);
        $this->enroll($course, $student);
        $lesson = $course->lessons->first();
        $lesson->update(['title' => '<img src=x onerror=alert("lesson")>']);

        $this->actingAs($teacher)->get(route('teacher.courses.students', $course))
            ->assertOk()->assertSee($student->name)->assertDontSee($student->name, false)
            ->assertSee($lesson->title)->assertDontSee($lesson->title, false);
    }

    public function test_viewing_the_student_list_does_not_change_rewards_or_enrollment(): void
    {
        $teacher = $this->approvedTeacher();
        $course = $this->teacherCourse($teacher);
        $student = $this->learner();
        $enrollment = $this->enroll($course, $student);
        $progress = $this->progress($student, $course->lessons->first(), ['completed_at' => now()->subDay(), 'xp' => 230, 'stars' => 3]);
        $beforeProgress = $progress->refresh()->getRawOriginal();
        $beforeEnrollment = $enrollment->refresh()->getRawOriginal();

        $this->actingAs($teacher)->get(route('teacher.courses.students', $course))->assertOk();
        $this->assertSame($beforeProgress, $progress->fresh()->getRawOriginal());
        $this->assertSame($beforeEnrollment, $enrollment->fresh()->getRawOriginal());
        $this->assertArrayNotHasKey('total_xp', $student->fresh()->getAttributes());
    }

    private function enroll(TeacherCourse $course, User $student, string $status = CourseEnrollment::ACTIVE): CourseEnrollment
    {
        return CourseEnrollment::forceCreate([
            'teacher_course_id' => $course->id, 'user_id' => $student->id,
            'status' => $status, 'payment_status' => CourseEnrollment::PAYMENT_NOT_REQUIRED,
            'requested_at' => now()->subMonth(),
        ]);
    }

    private function progress(User $student, Lesson $lesson, array $attributes): LessonProgress
    {
        return LessonProgress::create($attributes + ['user_id' => $student->id, 'lesson_id' => $lesson->id]);
    }

    private function attempt(User $student, Lesson $lesson, Carbon $completedAt): LearningAttempt
    {
        return LearningAttempt::create([
            'user_id' => $student->id, 'lesson_id' => $lesson->id,
            'run_key' => Str::random(64), 'lesson_title' => $lesson->title,
            'vocabulary_count' => 1, 'question_count' => 0,
            'correct_count' => 0, 'wrong_count' => 0, 'wrong_attempts' => 0,
            'score_percent' => null, 'elapsed_seconds' => 60,
            'started_at' => $completedAt->copy()->subMinute(),
            'completed_at' => $completedAt, 'results' => [],
        ]);
    }
}
