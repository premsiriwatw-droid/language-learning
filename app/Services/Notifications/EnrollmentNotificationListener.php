<?php

namespace App\Services\Notifications;

use App\Enums\NotificationTopic;
use App\Events\Notifications\UserNotificationRequested;
use App\Events\TeacherCourses\EnrollmentStatusChanged;
use App\Models\CourseEnrollment;

class EnrollmentNotificationListener
{
    public function handle(EnrollmentStatusChanged $event): void
    {
        $labels = [
            CourseEnrollment::PENDING => 'ส่งคำขอเข้าเรียนแล้ว',
            CourseEnrollment::APPROVED => 'อนุมัติคำขอแล้ว รอยืนยันการชำระเงิน',
            CourseEnrollment::REJECTED => 'คำขอเข้าเรียนถูกปฏิเสธ',
            CourseEnrollment::ACTIVE => 'เข้าเรียนคอร์สได้แล้ว',
            CourseEnrollment::COMPLETED => 'บันทึกว่าเรียนจบคอร์สแล้ว',
        ];
        if (! isset($labels[$event->toStatus])) {
            return;
        }
        $course = $event->enrollment->course;
        $rejected = $event->toStatus === CourseEnrollment::REJECTED;
        $body = $course->title;
        if ($rejected) {
            $reason = trim($event->enrollment->teacher_note ?? '');
            $body .= ' · เหตุผล: '.($reason !== '' ? $reason : 'ผู้สอนไม่ได้ระบุเหตุผล');
        }
        UserNotificationRequested::dispatch(
            $event->enrollment->user_id, NotificationTopic::EnrollmentStatus, $event->notificationEventId,
            $labels[$event->toStatus], $body,
            $rejected ? route('teacher-courses.mine', [], false) : '/teacher-courses/'.$course->id,
        );

        if ($event->toStatus === CourseEnrollment::PENDING
            && in_array($event->fromStatus, [null, CourseEnrollment::REJECTED], true)) {
            UserNotificationRequested::dispatch(
                $course->teacher_id, NotificationTopic::EnrollmentStatus, $event->notificationEventId,
                'มีคำขอเข้าเรียนใหม่', $event->enrollment->user->name.' ส่งคำขอเข้าเรียน '.$course->title,
                route('teacher.courses.enrollments', $course, false),
            );
        }
    }
}
