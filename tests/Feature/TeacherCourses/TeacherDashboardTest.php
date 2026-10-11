<?php

namespace Tests\Feature\TeacherCourses;

use App\Models\CourseEnrollment;
use App\Models\LearningAttempt;
use App\Models\Lesson;
use App\Models\LessonProgress;
use App\Models\TeacherApplication;
use App\Models\TeacherCourse;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Tests\Support\CreatesTeacherCourses;
use Tests\TestCase;

class TeacherDashboardTest extends TestCase
{
    use CreatesTeacherCourses;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->travelTo(Carbon::parse('2026-10-14 12:00:00'));
    }

    public function test_dashboard_requires_an_approved_teacher(): void
    {
        $course = $this->teacherCourse($this->approvedTeacher());
        $url = route('teacher.courses.dashboard', $course);
        $this->get($url)->assertRedirect(route('login'));

        foreach ([$this->learner(), $this->pendingTeacher(), User::factory()->create(['role' => 'admin']), User::factory()->create(['role' => 'teacher'])] as $user) {
            $this->actingAs($user)->get($url)->assertForbidden();
        }

        foreach ([TeacherApplication::REJECTED, TeacherApplication::NEEDS_MORE_INFO] as $status) {
            $teacher = $this->approvedTeacher();
            $teacher->teacherApplication->forceFill(['status' => $status])->save();
            $this->actingAs($teacher)->get($url)->assertForbidden();
        }
    }

    public function test_only_the_course_owner_can_view_the_dashboard(): void
    {
        $teacher = $this->approvedTeacher();
        $course = $this->teacherCourse($teacher, attributes: ['title' => 'Private course']);
        $otherTeacher = $this->approvedTeacher();

        $this->actingAs($otherTeacher)->get(route('teacher.courses.dashboard', $course))
            ->assertForbidden()->assertDontSee('Private course');
        $this->actingAs($teacher)->get(route('teacher.courses.dashboard', $course))->assertOk();
        $this->get('/teacher/courses/999999/dashboard')->assertNotFound();

        $teacher->teacherApplication->forceFill(['status' => TeacherApplication::REJECTED])->save();
        $this->actingAs($teacher->fresh())->get(route('teacher.courses.dashboard', $course))->assertForbidden();
    }

    public function test_course_management_links_to_its_own_dashboards(): void
    {
        $teacher = $this->approvedTeacher();
        $course = $this->teacherCourse($teacher);
        $otherCourse = $this->teacherCourse($this->approvedTeacher());

        $this->actingAs($teacher)->get(route('teacher.courses.index'))
            ->assertOk()
            ->assertSee(route('teacher.courses.dashboard', $course), false)
            ->assertDontSee(route('teacher.courses.dashboard', $otherCourse), false);
        $this->get(route('teacher.courses.dashboard', $course))
            ->assertSee(route('teacher.courses.index'), false)
            ->assertSee('ภาพรวมการเรียน')
            ->assertSee('คะแนนเฉลี่ยจากการเรียนครั้งแรก');
    }

    public function test_metrics_use_learning_enrollments_and_only_the_course_curriculum(): void
    {
        $teacher = $this->approvedTeacher();
        [$language, $lessons] = $this->languageWithLessons();
        $course = $this->teacherCourse($teacher, $language);
        $first = $this->learner();
        $second = $this->learner();
        $notStarted = $this->learner();
        $this->enroll($course, $first);
        $this->enroll($course, $second, CourseEnrollment::COMPLETED);
        $this->enroll($course, $notStarted);

        $old = now()->subWeeks(2);
        foreach ($lessons as $lesson) {
            $this->progress($first, $lesson, ['completed_at' => $old, 'last_visited_at' => now()->subDay()]);
        }
        $this->progress($second, $lessons[0], ['completed_at' => $old, 'last_visited_at' => $old]);
        $this->attempt($first, $lessons[0], 50, $old);
        $this->attempt($first, $lessons[0], 100, now());
        $this->attempt($first, $lessons[1], 100, $old);
        $this->attempt($second, $lessons[0], 80, now()->subDay());

        foreach ([CourseEnrollment::PENDING, CourseEnrollment::APPROVED, CourseEnrollment::REJECTED] as $status) {
            $excluded = $this->learner();
            $this->enroll($course, $excluded, $status);
            foreach ($lessons as $lesson) {
                $this->progress($excluded, $lesson, ['completed_at' => now(), 'last_visited_at' => now()]);
                $this->attempt($excluded, $lesson, 100, now());
            }
        }

        $otherCourse = $this->teacherCourse($this->approvedTeacher());
        $otherLesson = $otherCourse->lessons->first();
        $this->enroll($otherCourse, $first);
        $this->progress($first, $otherLesson, ['completed_at' => now(), 'last_visited_at' => now()]);
        $this->attempt($first, $otherLesson, 100, now());
        $this->progress($notStarted, $otherLesson, ['completed_at' => now(), 'last_visited_at' => now()]);
        $this->attempt($notStarted, $otherLesson, 100, now());

        $outsider = $this->learner();
        $this->enroll($otherCourse, $outsider);
        $this->progress($outsider, $lessons[0], ['completed_at' => now(), 'last_visited_at' => now()]);
        $this->attempt($outsider, $lessons[0], 100, now());

        $this->actingAs($teacher)->get(route('teacher.courses.dashboard', $course))
            ->assertOk()->assertViewHas('summary', function (array $summary): bool {
                $this->assertSame(3, $summary['students_count']);
                $this->assertSame(2, $summary['active_this_week']);
                $this->assertSame(2, $summary['lessons_count']);
                $this->assertSame(50.0, $summary['average_progress']);
                $this->assertSame(76.7, $summary['average_first_attempt_score']);
                $this->assertSame(3, $summary['scored_first_attempts']);

                return true;
            });
    }

    public function test_weekly_activity_counts_each_learner_once_from_monday_to_now(): void
    {
        $teacher = $this->approvedTeacher();
        $course = $this->teacherCourse($teacher);
        $lessons = $course->lessons;
        $monday = Carbon::parse('2026-10-12 00:00:00');
        $visitor = $this->learner();
        $previousWeek = $this->learner();
        $historyOnly = $this->learner();
        $future = $this->learner();
        $completionOnly = $this->learner();
        foreach ([$visitor, $previousWeek, $historyOnly, $future, $completionOnly] as $student) {
            $this->enroll($course, $student);
        }

        foreach ($lessons as $lesson) {
            $this->progress($visitor, $lesson, ['last_visited_at' => $monday]);
        }
        $this->attempt($visitor, $lessons[0], 80, now());
        $this->progress($previousWeek, $lessons[0], ['last_visited_at' => $monday->copy()->subSecond()]);
        $this->attempt($previousWeek, $lessons[0], 80, $monday->copy()->subSecond());
        $this->attempt($historyOnly, $lessons[0], 70, now()->subDay());
        $this->progress($future, $lessons[0], ['last_visited_at' => now()->addDay()]);
        $this->attempt($future, $lessons[0], 90, now()->addDay());
        $this->progress($completionOnly, $lessons[0], ['completed_at' => now()->subDay()]);

        $this->actingAs($teacher)->get(route('teacher.courses.dashboard', $course))
            ->assertOk()->assertViewHas('summary', function (array $summary) use ($monday): bool {
                $this->assertSame(3, $summary['active_this_week']);
                $this->assertTrue($summary['week_started_at']->equalTo($monday));
                $this->assertSame(3, $summary['scored_first_attempts']);

                return true;
            });
    }

    public function test_first_attempt_uses_completion_time_and_id_to_break_ties(): void
    {
        $teacher = $this->approvedTeacher();
        $course = $this->teacherCourse($teacher);
        $student = $this->learner();
        $this->enroll($course, $student);
        $lessons = $course->lessons;

        $this->attempt($student, $lessons[0], 100, now()->subDay());
        $this->attempt($student, $lessons[0], 20, now()->subWeeks(2));
        $this->attempt($student, $lessons[1], 40, now());
        $this->attempt($student, $lessons[1], 100, now());

        $this->actingAs($teacher)->get(route('teacher.courses.dashboard', $course))
            ->assertOk()->assertViewHas('summary', function (array $summary): bool {
                $this->assertSame(30.0, $summary['average_first_attempt_score']);
                $this->assertSame(2, $summary['scored_first_attempts']);

                return true;
            });
    }

    public function test_an_unscored_first_round_is_not_replaced_by_a_scored_replay(): void
    {
        $teacher = $this->approvedTeacher();
        $course = $this->teacherCourse($teacher);
        $student = $this->learner();
        $this->enroll($course, $student);
        $lessons = $course->lessons;
        $old = now()->subDay();

        $this->attempt($student, $lessons[0], null, $old, [
            'question_count' => 0, 'correct_count' => 0, 'wrong_count' => 0, 'wrong_attempts' => 0,
        ]);
        $this->attempt($student, $lessons[0], 100, now());
        $this->attempt($student, $lessons[1], null, $old);
        $this->attempt($student, $lessons[1], 100, now());

        $this->actingAs($teacher)->get(route('teacher.courses.dashboard', $course))
            ->assertOk()->assertSee('ยังไม่มีคะแนนจากรอบแรกที่ใช้คำนวณได้')
            ->assertViewHas('summary', function (array $summary): bool {
                $this->assertNull($summary['average_first_attempt_score']);
                $this->assertSame(0, $summary['scored_first_attempts']);
                $this->assertSame(0.0, $summary['average_progress']);

                return true;
            });
    }

    public function test_a_zero_score_is_displayed_as_a_real_score(): void
    {
        $teacher = $this->approvedTeacher();
        $course = $this->teacherCourse($teacher);
        $student = $this->learner();
        $this->enroll($course, $student);
        $this->attempt($student, $course->lessons->first(), 0, now());

        $this->actingAs($teacher)->get(route('teacher.courses.dashboard', $course))
            ->assertOk()->assertDontSee('ยังไม่มีคะแนนจากรอบแรกที่ใช้คำนวณได้')
            ->assertViewHas('summary', function (array $summary): bool {
                $this->assertSame(0.0, $summary['average_first_attempt_score']);
                $this->assertSame(1, $summary['scored_first_attempts']);

                return true;
            });
    }

    public function test_a_course_without_learning_enrollments_has_an_empty_state(): void
    {
        $teacher = $this->approvedTeacher();
        $course = $this->teacherCourse($teacher);
        $pending = $this->learner();
        $this->enroll($course, $pending, CourseEnrollment::PENDING);
        $this->progress($pending, $course->lessons->first(), ['completed_at' => now()]);

        $this->actingAs($teacher)->get(route('teacher.courses.dashboard', $course))
            ->assertOk()->assertSee('ยังไม่มีนักเรียนที่เริ่มเรียนในคอร์สนี้')
            ->assertViewHas('summary', function (array $summary): bool {
                $this->assertSame(0, $summary['students_count']);
                $this->assertSame(0, $summary['active_this_week']);
                $this->assertNull($summary['average_progress']);
                $this->assertNull($summary['average_first_attempt_score']);

                return true;
            });
    }

    public function test_a_draft_without_lessons_does_not_calculate_misleading_percentages(): void
    {
        $teacher = $this->approvedTeacher();
        $course = $this->teacherCourse($teacher, state: fn ($factory) => $factory->draft());
        $course->lessons()->detach();
        $this->enroll($course, $this->learner());

        $this->actingAs($teacher)->get(route('teacher.courses.dashboard', $course))
            ->assertOk()->assertSee('คอร์สนี้ยังไม่มีบทเรียน')
            ->assertViewHas('summary', function (array $summary): bool {
                $this->assertSame(1, $summary['students_count']);
                $this->assertSame(0, $summary['lessons_count']);
                $this->assertSame(0, $summary['active_this_week']);
                $this->assertNull($summary['average_progress']);
                $this->assertNull($summary['average_first_attempt_score']);

                return true;
            });
    }

    private function enroll(TeacherCourse $course, User $student, string $status = CourseEnrollment::ACTIVE): CourseEnrollment
    {
        return CourseEnrollment::forceCreate([
            'teacher_course_id' => $course->id,
            'user_id' => $student->id,
            'status' => $status,
            'payment_status' => CourseEnrollment::PAYMENT_NOT_REQUIRED,
            'requested_at' => now()->subMonth(),
        ]);
    }

    private function progress(User $student, Lesson $lesson, array $attributes): LessonProgress
    {
        return LessonProgress::create($attributes + [
            'user_id' => $student->id,
            'lesson_id' => $lesson->id,
        ]);
    }

    private function attempt(User $student, Lesson $lesson, ?float $score, Carbon $completedAt, array $attributes = []): LearningAttempt
    {
        $correct = (int) round(($score ?? 0) / 10);

        return LearningAttempt::create($attributes + [
            'user_id' => $student->id,
            'lesson_id' => $lesson->id,
            'run_key' => Str::random(64),
            'lesson_title' => $lesson->title,
            'vocabulary_count' => 0,
            'question_count' => 10,
            'correct_count' => $correct,
            'wrong_count' => 10 - $correct,
            'wrong_attempts' => 10 - $correct,
            'score_percent' => $score,
            'elapsed_seconds' => 60,
            'started_at' => $completedAt->copy()->subMinute(),
            'completed_at' => $completedAt,
            'results' => [],
        ]);
    }
}
