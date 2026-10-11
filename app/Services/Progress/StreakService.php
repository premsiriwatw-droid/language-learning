<?php

namespace App\Services\Progress;

use App\Models\User;
use DateTimeInterface;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class StreakService
{
    public function record(User $user, DateTimeInterface|string $completedAt): User
    {
        if (! $user->isLearner()) {
            return $user;
        }

        $date = Carbon::parse($completedAt, config('app.timezone'))
            ->setTimezone(config('app.timezone'))->startOfDay();

        if ($date->greaterThan(Carbon::today(config('app.timezone')))) {
            throw new InvalidArgumentException('An active day cannot be in the future.');
        }

        return DB::transaction(function () use ($user, $date): User {
            $locked = User::whereKey($user->id)->lockForUpdate()->firstOrFail();
            $last = $locked->getRawOriginal('last_active_date')
                ? Carbon::parse($locked->getRawOriginal('last_active_date'), config('app.timezone'))->startOfDay()
                : null;

            // Ignore duplicate or delayed events rather than move activity backwards.
            if ($last && $date->lessThanOrEqualTo($last)) {
                return $locked;
            }

            $streak = $last && $last->copy()->addDay()->equalTo($date)
                ? (int) $locked->current_streak + 1
                : 1;

            $locked->forceFill([
                'current_streak' => $streak,
                'longest_streak' => max((int) $locked->longest_streak, $streak),
                'last_active_date' => $date->toDateString(),
            ])->save();

            return $locked;
        });
    }
}
