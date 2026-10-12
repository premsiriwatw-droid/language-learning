<?php

namespace App\Notifications;

use App\Events\Notifications\UserNotificationRequested;
use App\Models\User;
use Illuminate\Notifications\Notification;
use Ramsey\Uuid\Uuid;

class HubNotification extends Notification
{
    public function __construct(public readonly UserNotificationRequested $event)
    {
        $this->id = Uuid::uuid5(Uuid::NAMESPACE_URL,
            'langlearn:'.$event->userId.':'.$event->topic->value.':'.$event->eventKey)->toString();
    }

    public function via(User $notifiable): array
    {
        return [IdempotentDatabaseChannel::class];
    }

    public function toDatabase(User $notifiable): array
    {
        return [
            'topic' => $this->event->topic->value, 'title' => $this->event->title,
            'body' => $this->event->body, 'path' => $this->event->path,
        ];
    }
}
