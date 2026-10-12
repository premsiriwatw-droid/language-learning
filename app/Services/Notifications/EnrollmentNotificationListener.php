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
        UserNotificationRequested::dispatch(
            $event->enrollment->user_id, NotificationTopic::EnrollmentStatus, $event->notificationEventId,
            $labels[$event->toStatus], $course->title,
            '/teacher-courses/'.$course->id,
        );
    }
}
