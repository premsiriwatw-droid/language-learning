<?php

namespace App\Services\Notifications;

use App\Events\Notifications\UserNotificationRequested;
use App\Models\User;
use App\Notifications\HubNotification;

class NotificationEventListener
{
    public function handle(UserNotificationRequested $event): void
    {
        User::find($event->userId)?->notify(new HubNotification($event));
    }
}
