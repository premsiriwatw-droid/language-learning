<?php

namespace Tests\Feature\TeacherCourses;

use App\Models\CourseEnrollment;
use App\Models\LearningAttempt;
use App\Models\Lesson;
use App\Models\TeacherCourse;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Tests\Support\CreatesTeacherCourses;
use Tests\TestCase;

class TeacherWeakTopicsTest extends TestCase
{
    use CreatesTeacherCourses;
    use RefreshDatabase;

    public function test_only_the_course_owner_can_view_the_report(): void
    {
        $teacher = $this->approvedTeacher();
        $course = $this->teacherCourse($teacher);
        $url = route('teacher.courses.weak-topics', $course);

        $this->get($url)->assertRedirect(route('login'));

        $this->actingAs($this->approvedTeacher())
            ->get($url)
            ->assertForbidden();

        $this->actingAs($this->learner())
            ->get($url)
            ->assertForbidden();

        $this->actingAs($teacher)
            ->get($url)
            ->assertOk();
    }

    public function test_report_scopes_data_and_uses_first_round_scores(): void
    {
        $teacher = $this->approvedTeacher();
        $course = $this->teacherCourse($teacher);
        $lesson = $course->lessons->first();

        $student = $this->learner();
        $finished = $this->learner();

        $this->enroll($course, $student);
        $this->enroll($course, $finished, CourseEnrollment::COMPLETED);

        // คนแรกได้ 0 แล้วทบทวนได้ 100; ต้องใช้คะแนนรอบแรกคือ 0
        $this->record($student, $lesson, now()->subHours(2), 0, 2);
        $this->record($student, $lesson, now()->subHour(), 100, 0);

        // คนที่สองได้ 100; ค่าเฉลี่ยรอบแรกจึงเท่ากับ 50
        $this->record($finished, $lesson, now()->subHour(), 100, 0);

        // คนที่ไม่ได้อยู่ในคอร์ส ต้องไม่นำมาคำนวณ
        $this->record($this->learner(), $lesson, now()->subHour(), 0, 99);

        foreach ([
            CourseEnrollment::PENDING,
            CourseEnrollment::APPROVED,
            CourseEnrollment::REJECTED,
        ] as $status) {
            $excluded = $this->learner();
            $this->enroll($course, $excluded, $status);
            $this->record($excluded, $lesson, now()->subHour(), 0, 99);
        }

        // บทเรียนนอกคอร์สและข้อมูลในอนาคต ต้องไม่นำมาคำนวณ
        $otherCourse = $this->teacherCourse($this->approvedTeacher());

        $this->record(
            $student,
            $otherCourse->lessons->first(),
            now()->subHour(),
            0,
            99
        );

        $this->record($student, $lesson, now()->addHour(), 0, 99);

        $response = $this->actingAs($teacher)
            ->get(route('teacher.courses.weak-topics', $course))
            ->assertOk();

        $report = $response->viewData('report');

        $this->assertSame(
            2,
            $report['wrong_questions']->sole()['wrong_attempts']
        );

        $this->assertSame(
            2,
            $report['related_words']->sole()['wrong_attempts']
        );

        $this->assertSame(
            50.0,
            $report['low_lessons']->sole()['average_score']
        );

        $this->assertSame(
            2,
            $report['low_lessons']->sole()['scored_count']
        );
    }

    public function test_course_without_history_has_empty_rankings(): void
    {
        $teacher = $this->approvedTeacher();
        $course = $this->teacherCourse($teacher);

        $response = $this->actingAs($teacher)
            ->get(route('teacher.courses.weak-topics', $course))
            ->assertOk();

        $report = $response->viewData('report');

        $this->assertCount(0, $report['wrong_questions']);
        $this->assertCount(0, $report['related_words']);
        $this->assertCount(0, $report['low_lessons']);
    }

    private function enroll(
        TeacherCourse $course,
        User $student,
        string $status = CourseEnrollment::ACTIVE
    ): void {
        CourseEnrollment::forceCreate([
            'teacher_course_id' => $course->id,
            'user_id' => $student->id,
            'status' => $status,
            'payment_status' => CourseEnrollment::PAYMENT_NOT_REQUIRED,
            'requested_at' => now()->subDay(),
        ]);
    }

    private function record(
        User $student,
        Lesson $lesson,
        Carbon $completedAt,
        float $score,
        int $wrong
    ): void {
        LearningAttempt::create([
            'user_id' => $student->id,
            'lesson_id' => $lesson->id,
            'run_key' => Str::random(64),
            'lesson_title' => $lesson->title,
            'vocabulary_count' => 1,
            'question_count' => 1,
            'correct_count' => $wrong === 0 ? 1 : 0,
            'wrong_count' => $wrong > 0 ? 1 : 0,
            'wrong_attempts' => $wrong,
            'score_percent' => $score,
            'elapsed_seconds' => 60,
            'started_at' => $completedAt->copy()->subMinute(),
            'completed_at' => $completedAt,
            'results' => [[
                'question_id' => 123,
                'question' => 'Greeting question',
                'first_correct' => $wrong === 0,
                'wrong_attempts' => $wrong,
                'vocabulary' => [[
                    'id' => 456,
                    'word' => 'hello',
                    'meaning' => 'สวัสดี',
                ]],
            ]],
        ]);
    }
}