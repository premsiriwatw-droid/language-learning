<?php

namespace App\Services\TeacherCourses;

use App\Events\TeacherCourses\EnrollmentStatusChanged;
use App\Models\CourseEnrollment;
use App\Models\TeacherCourse;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * The only place that changes enrollment status / payment status.
 *
 * Request flow:
 *   free + instant            -> active
 *   free + approval_required  -> pending  -> (teacher approves) -> active
 *   paid + instant            -> approved (waiting payment)      -> (payment confirmed) -> active
 *   paid + approval_required  -> pending  -> approved            -> (payment confirmed) -> active
 *   active -> (teacher marks done) -> completed
 *
 * Payment is simulated only: the learner can press "simulate payment"
 * (unpaid -> submitted) and the teacher confirms it manually (-> confirmed).
 * No real money moves in this MVP.
 */
class EnrollmentService
{
    public function request(TeacherCourse $course, User $learner): CourseEnrollment
    {
        return DB::transaction(function () use ($course, $learner) {
            $course = TeacherCourse::query()->whereKey($course->id)->lockForUpdate()->firstOrFail();

            if ($course->isOwnedBy($learner)) {
                $this->fail('ผู้สอนขอเข้าเรียนคอร์สของตัวเองไม่ได้');
            }

            if (! $course->isPublished() || ! TeacherCourse::query()->whereKey($course->id)->fromApprovedTeachers()->exists()) {
                $this->fail('คอร์สนี้ยังไม่เปิดรับผู้เรียน');
            }

            $enrollment = CourseEnrollment::query()
                ->where('teacher_course_id', $course->id)
                ->where('user_id', $learner->id)
                ->lockForUpdate()
                ->first();

            // Re-requesting is only allowed after a rejection; everything else is a duplicate.
            if ($enrollment && $enrollment->status !== CourseEnrollment::REJECTED) {
                $this->fail('คุณส่งคำขอหรือเข้าเรียนคอร์สนี้อยู่แล้ว');
            }

            $from = $enrollment?->status;
            $enrollment ??= new CourseEnrollment();

            $instant = $course->enrollment_mode === TeacherCourse::INSTANT;
            $status = match (true) {
                $course->isFree() && $instant => CourseEnrollment::ACTIVE,
                ! $course->isFree() && $instant => CourseEnrollment::APPROVED,
                default => CourseEnrollment::PENDING,
            };

            $enrollment->forceFill([
                'teacher_course_id' => $course->id,
                'user_id' => $learner->id,
                'status' => $status,
                'payment_status' => $course->isFree()
                    ? CourseEnrollment::PAYMENT_NOT_REQUIRED
                    : CourseEnrollment::PAYMENT_UNPAID,
                'price_at_request' => $course->isFree() ? null : $course->price,
                'teacher_note' => null,
                'requested_at' => now(),
                'decided_at' => $instant ? now() : null,
                'payment_submitted_at' => null,
                'payment_confirmed_at' => null,
                'activated_at' => $status === CourseEnrollment::ACTIVE ? now() : null,
                'completed_at' => null,
            ])->save();

            EnrollmentStatusChanged::dispatch($enrollment, $from, $status);

            return $enrollment;
        });
    }

    public function cancel(CourseEnrollment $enrollment): void
    {
        DB::transaction(function () use ($enrollment) {
            $enrollment = $this->lock($enrollment);

            $cancellable = $enrollment->status === CourseEnrollment::PENDING
                || ($enrollment->status === CourseEnrollment::APPROVED
                    && $enrollment->payment_status !== CourseEnrollment::PAYMENT_CONFIRMED);

            if (! $cancellable) {
                $this->fail('ยกเลิกได้เฉพาะคำขอที่ยังไม่ได้เริ่มเรียน');
            }

            $enrollment->delete();
        });
    }

    public function approve(CourseEnrollment $enrollment, ?string $note = null): CourseEnrollment
    {
        return $this->transition($enrollment, [CourseEnrollment::PENDING], function (CourseEnrollment $e) use ($note) {
            $paid = $e->payment_status !== CourseEnrollment::PAYMENT_NOT_REQUIRED;
            $alreadyPaid = $e->payment_status === CourseEnrollment::PAYMENT_CONFIRMED;
            $active = ! $paid || $alreadyPaid;

            return [
                'status' => $active ? CourseEnrollment::ACTIVE : CourseEnrollment::APPROVED,
                'teacher_note' => $note,
                'decided_at' => now(),
                'activated_at' => $active ? now() : null,
            ];
        }, 'อนุมัติได้เฉพาะคำขอที่รออนุมัติ');
    }

    public function reject(CourseEnrollment $enrollment, ?string $note = null): CourseEnrollment
    {
        return $this->transition($enrollment, [CourseEnrollment::PENDING, CourseEnrollment::APPROVED], function (CourseEnrollment $e) use ($note) {
            if ($e->payment_status === CourseEnrollment::PAYMENT_CONFIRMED) {
                $this->fail('ยืนยันการชำระเงินแล้ว ปฏิเสธคำขอไม่ได้');
            }

            return ['status' => CourseEnrollment::REJECTED, 'teacher_note' => $note, 'decided_at' => now()];
        }, 'ปฏิเสธได้เฉพาะคำขอที่ยังไม่ได้เริ่มเรียน');
    }

    public function submitPayment(CourseEnrollment $enrollment): CourseEnrollment
    {
        return DB::transaction(function () use ($enrollment) {
            $enrollment = $this->lock($enrollment);

            if (
                ! in_array($enrollment->status, [CourseEnrollment::PENDING, CourseEnrollment::APPROVED], true)
                || $enrollment->payment_status !== CourseEnrollment::PAYMENT_UNPAID
            ) {
                $this->fail('ไม่มีรายการที่ต้องชำระเงินสำหรับคำขอนี้');
            }

            // Payment-only change: status stays the same, so no status event.
            $enrollment->forceFill([
                'payment_status' => CourseEnrollment::PAYMENT_SUBMITTED,
                'payment_submitted_at' => now(),
            ])->save();

            return $enrollment;
        });
    }

    public function confirmPayment(CourseEnrollment $enrollment): CourseEnrollment
    {
        return $this->transition($enrollment, [CourseEnrollment::APPROVED], function (CourseEnrollment $e) {
            if (! in_array($e->payment_status, [CourseEnrollment::PAYMENT_UNPAID, CourseEnrollment::PAYMENT_SUBMITTED], true)) {
                $this->fail('คำขอนี้ไม่ต้องยืนยันการชำระเงิน');
            }

            return [
                'status' => CourseEnrollment::ACTIVE,
                'payment_status' => CourseEnrollment::PAYMENT_CONFIRMED,
                'payment_confirmed_at' => now(),
                'activated_at' => now(),
            ];
        }, 'ยืนยันการชำระเงินได้หลังอนุมัติคำขอแล้วเท่านั้น');
    }

    public function complete(CourseEnrollment $enrollment): CourseEnrollment
    {
        return $this->transition($enrollment, [CourseEnrollment::ACTIVE], fn () => [
            'status' => CourseEnrollment::COMPLETED,
            'completed_at' => now(),
        ], 'บันทึกว่าเรียนจบได้เฉพาะผู้เรียนที่กำลังเรียน');
    }

    /**
     * @param  list<string>  $allowedFrom
     * @param  callable(CourseEnrollment): array<string, mixed>  $changes
     */
    private function transition(CourseEnrollment $enrollment, array $allowedFrom, callable $changes, string $error): CourseEnrollment
    {
        return DB::transaction(function () use ($enrollment, $allowedFrom, $changes, $error) {
            $enrollment = $this->lock($enrollment);

            if (! in_array($enrollment->status, $allowedFrom, true)) {
                $this->fail($error);
            }

            $from = $enrollment->status;
            $enrollment->forceFill($changes($enrollment))->save();

            if ($enrollment->status !== $from) {
                EnrollmentStatusChanged::dispatch($enrollment, $from, $enrollment->status);
            }

            return $enrollment;
        });
    }

    private function lock(CourseEnrollment $enrollment): CourseEnrollment
    {
        return CourseEnrollment::query()->whereKey($enrollment->id)->lockForUpdate()->firstOrFail();
    }

    private function fail(string $message): never
    {
        throw ValidationException::withMessages(['enrollment' => $message]);
    }
}
