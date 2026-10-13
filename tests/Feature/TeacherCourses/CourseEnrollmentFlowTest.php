<?php

namespace Tests\Feature\TeacherCourses;

use App\Events\TeacherCourses\EnrollmentStatusChanged;
use App\Models\CourseEnrollment;
use App\Models\Vocabulary;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Tests\Support\CreatesTeacherCourses;
use Tests\TestCase;

class CourseEnrollmentFlowTest extends TestCase
{
    use CreatesTeacherCourses;
    use RefreshDatabase;

    private function enrollment(int $courseId, int $userId): ?CourseEnrollment
    {
        return CourseEnrollment::where('teacher_course_id', $courseId)->where('user_id', $userId)->first();
    }

    public function test_free_instant_course_activates_immediately_and_fires_event(): void
    {
        Event::fake([EnrollmentStatusChanged::class]);
        $course = $this->teacherCourse($this->approvedTeacher());
        $learner = $this->learner();

        $this->actingAs($learner)->post(route('teacher-courses.enroll', $course))
            ->assertRedirect(route('teacher-courses.show', $course))
            ->assertSessionHas('success');

        $enrollment = $this->enrollment($course->id, $learner->id);
        $this->assertSame(CourseEnrollment::ACTIVE, $enrollment->status);
        $this->assertSame(CourseEnrollment::PAYMENT_NOT_REQUIRED, $enrollment->payment_status);
        $this->assertNotNull($enrollment->activated_at);

        Event::assertDispatched(EnrollmentStatusChanged::class, fn ($e) => $e->fromStatus === null && $e->toStatus === CourseEnrollment::ACTIVE);
    }

    public function test_approval_required_course_waits_for_the_owner(): void
    {
        $teacher = $this->approvedTeacher();
        $course = $this->teacherCourse($teacher, null, [], fn ($f) => $f->approvalRequired());
        $learner = $this->learner();

        $this->actingAs($learner)->post(route('teacher-courses.enroll', $course));
        $enrollment = $this->enrollment($course->id, $learner->id);
        $this->assertSame(CourseEnrollment::PENDING, $enrollment->status);

        // Another teacher and the learner cannot approve it.
        $this->actingAs($this->approvedTeacher())
            ->patch(route('teacher.courses.enrollments.update', $enrollment), ['action' => 'approve'])
            ->assertForbidden();
        $this->actingAs($learner)
            ->patch(route('teacher.courses.enrollments.update', $enrollment), ['action' => 'approve'])
            ->assertForbidden();
        $this->assertSame(CourseEnrollment::PENDING, $enrollment->fresh()->status);

        $this->actingAs($teacher)->get(route('teacher.courses.enrollments', $course))
            ->assertOk()->assertSee($learner->name)->assertSee('อนุมัติ');

        $this->actingAs($teacher)
            ->patch(route('teacher.courses.enrollments.update', $enrollment), ['action' => 'approve', 'note' => 'ยินดีต้อนรับ'])
            ->assertSessionHasNoErrors();

        $enrollment->refresh();
        $this->assertSame(CourseEnrollment::ACTIVE, $enrollment->status);
        $this->assertSame('ยินดีต้อนรับ', $enrollment->teacher_note);
    }

    public function test_rejected_learner_sees_reason_and_may_request_again(): void
    {
        $teacher = $this->approvedTeacher();
        $course = $this->teacherCourse($teacher, null, [], fn ($f) => $f->approvalRequired());
        $learner = $this->learner();

        $this->actingAs($learner)->post(route('teacher-courses.enroll', $course));
        $enrollment = $this->enrollment($course->id, $learner->id);

        $this->actingAs($teacher)->patch(route('teacher.courses.enrollments.update', $enrollment), ['action' => 'reject', 'note' => 'คอร์สเต็ม']);
        $this->assertSame(CourseEnrollment::REJECTED, $enrollment->fresh()->status);

        $this->actingAs($learner)->get(route('teacher-courses.show', $course))
            ->assertSee('คอร์สเต็ม')->assertSee('ขอเข้าเรียน');

        $this->actingAs($learner)->post(route('teacher-courses.enroll', $course))->assertSessionHasNoErrors();
        $this->assertSame(CourseEnrollment::PENDING, $enrollment->fresh()->status);
        $this->assertSame(1, CourseEnrollment::count());
    }

    public function test_duplicate_requests_are_blocked(): void
    {
        $course = $this->teacherCourse($this->approvedTeacher(), null, [], fn ($f) => $f->approvalRequired());
        $learner = $this->learner();

        $this->actingAs($learner)->post(route('teacher-courses.enroll', $course))->assertSessionHasNoErrors();
        $this->actingAs($learner)->post(route('teacher-courses.enroll', $course))->assertSessionHasErrors('enrollment');

        $this->assertSame(1, CourseEnrollment::count());
    }

    public function test_paid_instant_course_needs_simulated_payment_confirmed_by_teacher(): void
    {
        Event::fake([EnrollmentStatusChanged::class]);
        $teacher = $this->approvedTeacher();
        $course = $this->teacherCourse($teacher, null, [], fn ($f) => $f->paid(350));
        $learner = $this->learner();

        $this->actingAs($learner)->post(route('teacher-courses.enroll', $course));
        $enrollment = $this->enrollment($course->id, $learner->id);
        $this->assertSame(CourseEnrollment::APPROVED, $enrollment->status);
        $this->assertSame(CourseEnrollment::PAYMENT_UNPAID, $enrollment->payment_status);
        $this->assertSame('350.00', $enrollment->price_at_request);

        $this->actingAs($learner)->post(route('teacher-courses.enroll.pay', $course))->assertSessionHasNoErrors();
        $this->assertSame(CourseEnrollment::PAYMENT_SUBMITTED, $enrollment->fresh()->payment_status);
        $this->assertSame(CourseEnrollment::APPROVED, $enrollment->fresh()->status);

        // Paying twice is not possible.
        $this->actingAs($learner)->post(route('teacher-courses.enroll.pay', $course))->assertSessionHasErrors('enrollment');

        $this->actingAs($teacher)
            ->patch(route('teacher.courses.enrollments.update', $enrollment), ['action' => 'confirm_payment'])
            ->assertSessionHasNoErrors();

        $enrollment->refresh();
        $this->assertSame(CourseEnrollment::ACTIVE, $enrollment->status);
        $this->assertSame(CourseEnrollment::PAYMENT_CONFIRMED, $enrollment->payment_status);
        Event::assertDispatched(EnrollmentStatusChanged::class, fn ($e) => $e->fromStatus === CourseEnrollment::APPROVED && $e->toStatus === CourseEnrollment::ACTIVE);
    }

    public function test_paid_course_with_approval_goes_pending_then_approved_then_active(): void
    {
        $teacher = $this->approvedTeacher();
        $course = $this->teacherCourse($teacher, null, [], fn ($f) => $f->paid(100)->approvalRequired());
        $learner = $this->learner();

        $this->actingAs($learner)->post(route('teacher-courses.enroll', $course));
        $enrollment = $this->enrollment($course->id, $learner->id);
        $this->assertSame(CourseEnrollment::PENDING, $enrollment->status);

        // Payment cannot be confirmed before the request is approved.
        $this->actingAs($teacher)->patch(route('teacher.courses.enrollments.update', $enrollment), ['action' => 'confirm_payment'])
            ->assertSessionHasErrors('enrollment');

        $this->actingAs($teacher)->patch(route('teacher.courses.enrollments.update', $enrollment), ['action' => 'approve']);
        $this->assertSame(CourseEnrollment::APPROVED, $enrollment->fresh()->status);

        $this->actingAs($teacher)->patch(route('teacher.courses.enrollments.update', $enrollment), ['action' => 'confirm_payment']);
        $this->assertSame(CourseEnrollment::ACTIVE, $enrollment->fresh()->status);
    }

    public function test_cannot_join_draft_or_closed_course_or_own_course(): void
    {
        $teacher = $this->approvedTeacher();
        $draft = $this->teacherCourse($teacher, null, [], fn ($f) => $f->draft());
        $closed = $this->teacherCourse($teacher, null, [], fn ($f) => $f->closed());
        $open = $this->teacherCourse($teacher);
        $learner = $this->learner();

        $this->actingAs($learner)->post(route('teacher-courses.enroll', $draft))->assertForbidden();
        $this->actingAs($learner)->post(route('teacher-courses.enroll', $closed))->assertForbidden();
        $this->actingAs($teacher)->post(route('teacher-courses.enroll', $open))->assertSessionHasErrors('enrollment');

        $this->assertSame(0, CourseEnrollment::count());
    }

    public function test_closing_a_course_keeps_existing_learners(): void
    {
        $teacher = $this->approvedTeacher();
        $course = $this->teacherCourse($teacher, null, ['title' => 'Closing soon']);
        $learner = $this->learner();
        $this->actingAs($learner)->post(route('teacher-courses.enroll', $course));

        $this->actingAs($teacher)->patch(route('teacher.courses.status', $course), ['action' => 'close'])->assertSessionHasNoErrors();

        $this->actingAs($learner)->get(route('teacher-courses.show', $course))
            ->assertOk()->assertSee('กำลังเรียน');
        $this->actingAs($learner)->get(route('teacher-courses.mine'))
            ->assertOk()->assertSee('Closing soon');
        $this->actingAs($this->learner())->get(route('teacher-courses.show', $course))->assertForbidden();
    }

    public function test_learner_cancels_pending_request_but_not_active_enrollment(): void
    {
        $pendingCourse = $this->teacherCourse($this->approvedTeacher(), null, [], fn ($f) => $f->approvalRequired());
        $activeCourse = $this->teacherCourse($this->approvedTeacher());
        $learner = $this->learner();

        $this->actingAs($learner)->post(route('teacher-courses.enroll', $pendingCourse));
        $this->actingAs($learner)->post(route('teacher-courses.enroll', $activeCourse));

        $this->actingAs($learner)->delete(route('teacher-courses.enroll.cancel', $pendingCourse))->assertSessionHasNoErrors();
        $this->assertNull($this->enrollment($pendingCourse->id, $learner->id));

        $this->actingAs($learner)->delete(route('teacher-courses.enroll.cancel', $activeCourse))->assertSessionHasErrors('enrollment');
        $this->assertNotNull($this->enrollment($activeCourse->id, $learner->id));

        // Nothing to cancel for someone who never asked.
        $this->actingAs($this->learner())->delete(route('teacher-courses.enroll.cancel', $activeCourse))->assertNotFound();
    }

    public function test_completion_fires_event_once(): void
    {
        Event::fake([EnrollmentStatusChanged::class]);
        $teacher = $this->approvedTeacher();
        $course = $this->teacherCourse($teacher);
        $learner = $this->learner();
        $this->actingAs($learner)->post(route('teacher-courses.enroll', $course));
        $enrollment = $this->enrollment($course->id, $learner->id);

        $this->actingAs($teacher)->patch(route('teacher.courses.enrollments.update', $enrollment), ['action' => 'complete'])
            ->assertSessionHasNoErrors();
        $this->actingAs($teacher)->patch(route('teacher.courses.enrollments.update', $enrollment), ['action' => 'complete'])
            ->assertSessionHasErrors('enrollment');

        $this->assertSame(CourseEnrollment::COMPLETED, $enrollment->fresh()->status);
        Event::assertDispatchedTimes(EnrollmentStatusChanged::class, 2); // joined + completed
        Event::assertDispatched(EnrollmentStatusChanged::class, fn ($e) => $e->toStatus === CourseEnrollment::COMPLETED);
    }

    public function test_enrollment_does_not_award_xp_or_stars(): void
    {
        $course = $this->teacherCourse($this->approvedTeacher());
        $learner = $this->learner();

        $this->actingAs($learner)->post(route('teacher-courses.enroll', $course));

        $this->assertDatabaseCount('lesson_progress', 0);
    }

    public function test_enrolled_learner_sees_curriculum_with_learn_link(): void
    {
        $course = $this->teacherCourse($this->approvedTeacher());
        $first = $course->lessons->first();
        Vocabulary::factory()->create(['lesson_id' => $first->id]);
        $learner = $this->learner();

        $this->actingAs($learner)->get(route('teacher-courses.show', $course))
            ->assertSee($first->title)
            ->assertDontSee(route('lessons.learn', $first), false);

        $this->actingAs($learner)->post(route('teacher-courses.enroll', $course));

        $this->actingAs($learner)->get(route('teacher-courses.show', $course))
            ->assertSee(route('lessons.learn', $first), false);
    }

    public function test_unknown_action_is_rejected(): void
    {
        $teacher = $this->approvedTeacher();
        $course = $this->teacherCourse($teacher);
        $learner = $this->learner();
        $this->actingAs($learner)->post(route('teacher-courses.enroll', $course));

        $this->actingAs($teacher)
            ->patch(route('teacher.courses.enrollments.update', $this->enrollment($course->id, $learner->id)), ['action' => 'delete_everything'])
            ->assertSessionHasErrors('action');
    }

    public function test_payment_uses_price_at_request_after_teacher_changes_price(): void
    {
        $teacher = $this->approvedTeacher();
        $course = $this->teacherCourse($teacher, null, [], fn ($f) => $f->paid(350));
        $learner = $this->learner();

        $this->actingAs($learner)->post(route('teacher-courses.enroll', $course))->assertSessionHasNoErrors();

        $course->update(['price' => 500]);

        $this->actingAs($learner)->get(route('teacher-courses.show', $course))
            ->assertOk()
            ->assertSee('ชำระเงิน (จำลอง) ฿350')
            ->assertDontSee('ชำระเงิน (จำลอง) ฿500')
            ->assertSee('ยอดของคุณ ฿350');

        $this->actingAs($teacher)->get(route('teacher.courses.enrollments', $course))
            ->assertOk()
            ->assertSee('(฿350)')
            ->assertDontSee('(฿500)');

        // A new learner pays the new price.
        $newcomer = $this->learner();
        $this->actingAs($newcomer)->post(route('teacher-courses.enroll', $course));
        $this->assertSame('500.00', $this->enrollment($course->id, $newcomer->id)->price_at_request);
        $this->assertSame('350.00', $this->enrollment($course->id, $learner->id)->price_at_request);
    }
}
