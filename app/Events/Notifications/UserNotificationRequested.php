<?php

namespace App\Events\Notifications;

use App\Enums\NotificationTopic;
use Illuminate\Contracts\Events\ShouldDispatchAfterCommit;
use Illuminate\Foundation\Events\Dispatchable;
use InvalidArgumentException;

/** Backend-only contract. Producers must authorize the recipient and use a stable event key. */
class UserNotificationRequested implements ShouldDispatchAfterCommit
{
    use Dispatchable;

    public function __construct(
        public readonly int $userId,
        public readonly NotificationTopic $topic,
        public readonly string $eventKey,
        public readonly string $title,
        public readonly string $body,
        public readonly string $path = '/profile',
    ) {
        if ($userId < 1 || trim($eventKey) === '' || strlen($eventKey) > 200
            || trim($title) === '' || mb_strlen($title) > 160 || mb_strlen($body) > 2000
            || ! str_starts_with($path, '/') || str_starts_with($path, '//')
            || preg_match('/[\\\\\x00-\x20]/', $path) || strlen($path) > 1000) {
            throw new InvalidArgumentException('Invalid notification envelope or local destination.');
        }
    }
}
