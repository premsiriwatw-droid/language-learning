<?php

namespace App\Providers;

use App\Events\Notifications\UserNotificationRequested;
use App\Events\Progress\PlayerLeveledUp;
use App\Events\TeacherCourses\EnrollmentStatusChanged;
use App\Models\DailyQuest;
use App\Models\TeacherApplication;
use App\Observers\NotificationQuestObserver;
use App\Observers\NotificationTeacherApplicationObserver;
use App\Services\Notifications\EnrollmentNotificationListener;
use App\Services\Notifications\LevelNotificationListener;
use App\Services\Notifications\NotificationEventListener;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;

class ShopNotificationServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        Event::listen(UserNotificationRequested::class, NotificationEventListener::class);
        Event::listen(PlayerLeveledUp::class, LevelNotificationListener::class);
        Event::listen(EnrollmentStatusChanged::class, EnrollmentNotificationListener::class);
        DailyQuest::observe(NotificationQuestObserver::class);
        TeacherApplication::observe(NotificationTeacherApplicationObserver::class);
    }
}
