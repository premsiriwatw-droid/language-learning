<?php

namespace App\Observers;

use App\Enums\NotificationTopic;
use App\Events\Notifications\UserNotificationRequested;
use App\Models\TeacherApplication;
use App\Models\User;
use Illuminate\Support\Str;

/** Notification bridge only; verification rules remain in their owning module. */
class NotificationTeacherApplicationObserver
{
    public function created(TeacherApplication $application): void
    {
        if ($application->status === TeacherApplication::PENDING) {
            $this->notifySubmission($application);
        }
    }

    public function updated(TeacherApplication $application): void
    {
        if (! $application->wasChanged('status')) {
            return;
        }

        if ($application->status === TeacherApplication::PENDING) {
            $this->notifySubmission($application);
            return;
        }

        $label = match ($application->status) {
            TeacherApplication::APPROVED => 'ใบสมัครผู้สอนได้รับการอนุมัติแล้ว',
            TeacherApplication::REJECTED => 'ใบสมัครผู้สอนถูกปฏิเสธ',
            TeacherApplication::NEEDS_MORE_INFO => 'ใบสมัครผู้สอนต้องการข้อมูลเพิ่มเติม',
            default => null,
        };

        if ($label !== null) {
            UserNotificationRequested::dispatch(
                $application->user_id, NotificationTopic::TeacherApplicationStatus, (string) Str::uuid(),
                $label, 'ตรวจสอบรายละเอียดและผลพิจารณาในหน้าใบสมัคร', '/teacher/application',
            );
        }
    }

    private function notifySubmission(TeacherApplication $application): void
    {
        // Each submission is distinct, including resubmissions within the same second.
        $eventKey = 'teacher-application:'.$application->id.':submitted:'.Str::uuid();

        UserNotificationRequested::dispatch(
            $application->user_id, NotificationTopic::TeacherApplicationStatus, $eventKey,
            'ส่งใบสมัครผู้สอนเพื่อพิจารณาแล้ว',
            'ตรวจสอบสถานะได้ในหน้าใบสมัคร', '/teacher/application',
        );

        User::where('role', 'admin')->select('id')->chunkById(100, function ($admins) use ($application, $eventKey) {
            foreach ($admins as $admin) {
                UserNotificationRequested::dispatch(
                    $admin->id, NotificationTopic::TeacherApplicationStatus, $eventKey,
                    'มีใบสมัครผู้สอนรอพิจารณา',
                    'มีผู้สมัครส่งใบสมัครหรือส่งข้อมูลเพิ่มเติม กรุณาตรวจสอบในหน้าพิจารณา',
                    '/admin/teacher-applications/'.$application->id,
                );
            }
        });
    }
}
