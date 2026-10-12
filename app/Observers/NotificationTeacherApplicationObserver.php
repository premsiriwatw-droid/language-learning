<?php

namespace App\Observers;

use App\Enums\NotificationTopic;
use App\Events\Notifications\UserNotificationRequested;
use App\Models\TeacherApplication;
use Illuminate\Support\Str;

/** Observes persisted status only; does not decide or change verification rules. */
class NotificationTeacherApplicationObserver
{
    public function updated(TeacherApplication $application): void
    {
        if (! $application->wasChanged('status')) {
            return;
        }
        $label = match ($application->status) {
            TeacherApplication::APPROVED => 'ใบสมัครผู้สอนได้รับการอนุมัติแล้ว',
            TeacherApplication::REJECTED => 'ใบสมัครผู้สอนถูกปฏิเสธ',
            TeacherApplication::NEEDS_MORE_INFO => 'ใบสมัครผู้สอนต้องการข้อมูลเพิ่มเติม',
            TeacherApplication::PENDING => 'ส่งใบสมัครผู้สอนเพื่อพิจารณาแล้ว',
            default => null,
        };
        if ($label !== null) {
            UserNotificationRequested::dispatch(
                $application->user_id, NotificationTopic::TeacherApplicationStatus, (string) Str::uuid(),
                $label, 'ตรวจสอบรายละเอียดและผลพิจารณาในหน้าใบสมัคร', '/teacher/application',
            );
        }
    }
}
