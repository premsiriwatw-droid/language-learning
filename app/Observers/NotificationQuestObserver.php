<?php

namespace App\Observers;

use App\Enums\NotificationTopic;
use App\Events\Notifications\UserNotificationRequested;
use App\Models\DailyQuest;

class NotificationQuestObserver
{
    public function updated(DailyQuest $quest): void
    {
        if ($quest->wasChanged('completed_at') && $quest->completed_at !== null) {
            UserNotificationRequested::dispatch(
                $quest->user_id, NotificationTopic::QuestComplete, 'quest:'.$quest->id,
                'ทำภารกิจสำเร็จแล้ว', 'เปิดหน้าภารกิจเพื่อรับดาวรางวัลของคุณ', '/daily-quests',
            );
        }
    }
}
