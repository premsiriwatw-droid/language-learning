<?php

namespace App\Services\Notifications;

use App\Enums\NotificationTopic;
use App\Events\Notifications\UserNotificationRequested;
use App\Events\Progress\PlayerLeveledUp;

class LevelNotificationListener
{
    public function handle(PlayerLeveledUp $event): void
    {
        UserNotificationRequested::dispatch(
            $event->userId, NotificationTopic::LevelUp, 'level:'.$event->level,
            'เลเวลเพิ่มแล้ว!', 'ตอนนี้คุณอยู่ Level '.$event->level, '/profile',
        );
    }
}
