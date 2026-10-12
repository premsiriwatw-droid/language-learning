<?php

namespace App\Services\TeacherCourses;

use App\Enums\NotificationTopic;
use App\Events\Notifications\UserNotificationRequested;
use App\Models\TeacherCourse;
use App\Models\User;
use Closure;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

/**
 * Course life cycle. Every status change of a teacher course goes through here.
 *
 *   draft ──submit──▶ pending_review ──approve──▶ published ──close──▶ closed
 *     ▲                  │      │                    │                  │
 *     └──withdraw/reject─┘      └─reject (has learners)──▶ closed       │
 *                                                    ▲                  │
 *                                                    └──reopen (still approved)
 *
 * Editing a published course sends it back to pending_review; learners who already
 * joined keep studying, but the course leaves the marketplace until an admin approves it again.
 */
class CourseReviewService
{
    public function submit(TeacherCourse $course): TeacherCourse
    {
        return $this->locked($course, function (TeacherCourse $course) {
            if (! in_array($course->status, [TeacherCourse::DRAFT, TeacherCourse::CLOSED], true)) {
                $this->fail('ส่งตรวจได้เฉพาะคอร์สฉบับร่างหรือคอร์สที่ปิดอยู่');
            }
            if ($course->status === TeacherCourse::CLOSED && $course->isApproved()) {
                $this->fail('คอร์สนี้ผ่านการตรวจแล้ว กด "เปิดคอร์สอีกครั้ง" ได้เลย');
            }
            $this->ensureReadyToPublish($course);

            $course->forceFill([
                'status' => TeacherCourse::PENDING_REVIEW,
                'submitted_at' => now(),
                'approved_at' => null,
            ])->save();

            $this->notifyAdmins($course);

            return $course;
        });
    }

    /** Teacher cancels the review request. */
    public function withdraw(TeacherCourse $course): TeacherCourse
    {
        return $this->locked($course, function (TeacherCourse $course) {
            if (! $course->isPendingReview()) {
                $this->fail('คอร์สนี้ไม่ได้อยู่ระหว่างรอตรวจ');
            }

            $course->forceFill([
                'status' => $this->statusOffTheMarket($course),
                'submitted_at' => null,
            ])->save();

            return $course;
        });
    }

    public function close(TeacherCourse $course): TeacherCourse
    {
        return $this->locked($course, function (TeacherCourse $course) {
            if (! $course->isPublished()) {
                $this->fail('ปิดได้เฉพาะคอร์สที่เปิดรับสมัครอยู่');
            }

            $course->forceFill(['status' => TeacherCourse::CLOSED])->save();

            return $course;
        });
    }

    /** A closed course that is still approved (not edited since) goes straight back to the marketplace. */
    public function reopen(TeacherCourse $course): TeacherCourse
    {
        return $this->locked($course, function (TeacherCourse $course) {
            if ($course->status !== TeacherCourse::CLOSED) {
                $this->fail('เปิดอีกครั้งได้เฉพาะคอร์สที่ปิดอยู่');
            }
            if (! $course->isApproved()) {
                $this->fail('คอร์สถูกแก้ไขหลังผ่านการตรวจ ต้องส่งให้แอดมินตรวจก่อน');
            }
            $this->ensureReadyToPublish($course);

            $course->forceFill(['status' => TeacherCourse::PUBLISHED])->save();

            return $course;
        });
    }

    public function approve(TeacherCourse $course, User $admin, ?string $note = null): TeacherCourse
    {
        return $this->locked($course, function (TeacherCourse $course) use ($admin, $note) {
            if (! $course->isPendingReview()) {
                $this->fail('คอร์สนี้ไม่ได้อยู่ระหว่างรอตรวจ (อาจถูกถอนหรือตรวจไปแล้ว)', 'review');
            }
            $this->ensureReadyToPublish($course, 'review');

            $course->forceFill([
                'status' => TeacherCourse::PUBLISHED,
                'approved_at' => now(),
                'published_at' => $course->published_at ?? now(),
                'reviewed_at' => now(),
                'reviewed_by' => $admin->id,
                'review_note' => $this->clean($note),
            ])->save();

            $this->notifyTeacher($course, 'approved', 'คอร์สของคุณผ่านการตรวจแล้ว', $course->title.' เผยแพร่ในหน้าคอร์สจากผู้สอนแล้ว');

            return $course;
        });
    }

    public function reject(TeacherCourse $course, User $admin, string $note): TeacherCourse
    {
        $note = $this->clean($note);
        if ($note === null) {
            $this->fail('กรุณาระบุเหตุผลที่ไม่อนุมัติ เพื่อให้ผู้สอนแก้ไขได้', 'review_note');
        }

        return $this->locked($course, function (TeacherCourse $course) use ($admin, $note) {
            if (! $course->isPendingReview()) {
                $this->fail('คอร์สนี้ไม่ได้อยู่ระหว่างรอตรวจ (อาจถูกถอนหรือตรวจไปแล้ว)', 'review');
            }

            $course->forceFill([
                'status' => $this->statusOffTheMarket($course),
                'approved_at' => null,
                'reviewed_at' => now(),
                'reviewed_by' => $admin->id,
                'review_note' => $note,
            ])->save();

            $this->notifyTeacher($course, 'rejected', 'คอร์สของคุณยังไม่ผ่านการตรวจ', $course->title.' · เหตุผล: '.$note);

            return $course;
        });
    }

    /**
     * Call after the teacher changed course content (info, lessons, materials).
     * Returns true when a published course was sent back for review.
     */
    public function contentChanged(TeacherCourse $course): bool
    {
        return $this->locked($course, function (TeacherCourse $course) {
            if ($course->isPublished()) {
                $course->forceFill([
                    'status' => TeacherCourse::PENDING_REVIEW,
                    'submitted_at' => now(),
                    'approved_at' => null,
                ])->save();
                $this->notifyAdmins($course);

                return true;
            }

            if ($course->status === TeacherCourse::CLOSED && $course->isApproved()) {
                $course->forceFill(['approved_at' => null])->save();
            }

            return false;
        });
    }

    public function ensureReadyToPublish(TeacherCourse $course, string $key = 'status'): void
    {
        if ($course->language_id === null) {
            $this->fail('เลือกภาษาของคอร์สก่อน', $key);
        }
        if (! $course->hasAnyLesson()) {
            $this->fail('เพิ่มบทเรียนอย่างน้อย 1 บท (บทเรียนของคุณเอง หรือบทเรียนจากระบบ) ก่อนส่งตรวจ', $key);
        }
    }

    /** A course that leaves the market keeps learners who already joined, so it cannot become a draft. */
    private function statusOffTheMarket(TeacherCourse $course): string
    {
        return $course->hasActiveEnrollments() ? TeacherCourse::CLOSED : TeacherCourse::DRAFT;
    }

    /**
     * @template T
     *
     * @param  Closure(TeacherCourse): T  $change
     * @return T
     */
    private function locked(TeacherCourse $course, Closure $change): mixed
    {
        return DB::transaction(function () use ($course, $change) {
            $fresh = TeacherCourse::query()->lockForUpdate()->findOrFail($course->id);
            $result = $change($fresh);
            $course->setRawAttributes($fresh->getAttributes(), true);

            return $result;
        });
    }

    private function notifyAdmins(TeacherCourse $course): void
    {
        $eventKey = 'teacher-course:'.$course->id.':submitted:'.Str::uuid();

        User::query()->where('role', 'admin')->select('id')->chunkById(100, function ($admins) use ($course, $eventKey) {
            foreach ($admins as $admin) {
                UserNotificationRequested::dispatch(
                    $admin->id, NotificationTopic::TeacherApplicationStatus, $eventKey,
                    'มีคอร์สจากผู้สอนรอตรวจ', $course->title,
                    route('admin.teacher-courses.show', $course, false),
                );
            }
        });
    }

    private function notifyTeacher(TeacherCourse $course, string $outcome, string $title, string $body): void
    {
        UserNotificationRequested::dispatch(
            $course->teacher_id, NotificationTopic::TeacherApplicationStatus,
            'teacher-course:'.$course->id.':'.$outcome.':'.Str::uuid(),
            $title, mb_substr($body, 0, 2000),
            route('teacher.courses.edit', $course, false),
        );
    }

    private function clean(?string $note): ?string
    {
        $note = trim((string) $note);

        return $note === '' ? null : mb_substr($note, 0, 1000);
    }

    private function fail(string $message, string $key = 'status'): never
    {
        throw ValidationException::withMessages([$key => $message]);
    }
}
