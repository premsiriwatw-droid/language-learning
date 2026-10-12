<?php

namespace App\Notifications;

use App\Models\User;
use Illuminate\Notifications\DatabaseNotification;

/** Laravel's notification store with a deterministic primary key for retried events. */
class IdempotentDatabaseChannel
{
    public function send(User $notifiable, HubNotification $notification): DatabaseNotification
    {
        return $notifiable->notifications()->firstOrCreate(['id' => $notification->id], [
            'type' => HubNotification::class,
            'data' => $notification->toDatabase($notifiable),
            'read_at' => null,
        ]);
    }
}
