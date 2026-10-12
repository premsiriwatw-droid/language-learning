<?php

namespace App\Services\Progress;

use App\Models\DailyQuest;
use App\Models\DailyQuestEvent;
use App\Models\User;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use InvalidArgumentException;

class DailyQuestService
{
    public const TYPES = ['complete_lesson', 'correct_answers', 'review_mistakes'];

    public function __construct(private StarRewardService $rewards)
    {
    }

    public function today(User $user): Collection
    {
        abort_unless($user->isLearner(), 403);

        return $this->forDate($user, Carbon::today(config('app.timezone'))->toDateString());
    }

    public function recordLesson(User $user, int $lessonId, DateTimeInterface|string $completedAt): void
    {
        $this->record($user, 'complete_lesson', $lessonId, $completedAt);
    }

    public function recordCorrectAnswer(User $user, int $questionId, DateTimeInterface|string $completedAt): void
    {
        $this->record($user, 'correct_answers', $questionId, $completedAt);
    }

    public function recordReview(User $user, int $questionId, DateTimeInterface|string $completedAt): void
    {
        $this->record($user, 'review_mistakes', $questionId, $completedAt);
    }

    public function claim(User $user, int $questId): DailyQuest
    {
        abort_unless($user->isLearner(), 403);

        return DB::transaction(function () use ($user, $questId): DailyQuest {
            User::whereKey($user->id)->lockForUpdate()->firstOrFail();
            $quest = DailyQuest::where('user_id', $user->id)->whereKey($questId)
                ->lockForUpdate()->firstOrFail();

            $date = $quest->getRawOriginal('quest_date');
            if ($date !== Carbon::today(config('app.timezone'))->toDateString()) {
                throw ValidationException::withMessages(['quest' => 'รับรางวัลได้เฉพาะภารกิจของวันนี้']);
            }

            if ($quest->completed_at === null || $quest->progress < $quest->target) {
                throw ValidationException::withMessages(['quest' => 'ทำภารกิจให้ครบก่อนรับรางวัล']);
            }

            if ($quest->claimed_at !== null) {
                return $quest;
            }

            $this->rewards->add($user, 'daily-quest:'.$date.':'.$quest->type, $quest->reward_stars);
            $quest->update(['claimed_at' => now()]);

            return $quest->refresh();
        });
    }

    private function record(User $user, string $type, int $entityId, DateTimeInterface|string $completedAt): void
    {
        if (! $user->isLearner()) {
            return;
        }

        if ($entityId < 1) {
            throw new InvalidArgumentException('Quest activity requires an existing entity ID.');
        }

        $date = Carbon::parse($completedAt, config('app.timezone'))
            ->setTimezone(config('app.timezone'))->startOfDay();

        if ($date->greaterThan(Carbon::today(config('app.timezone')))) {
            throw new InvalidArgumentException('Quest activity cannot be in the future.');
        }

        DB::transaction(function () use ($user, $type, $entityId, $date): void {
            User::whereKey($user->id)->lockForUpdate()->firstOrFail();
            $quest = $this->forDate($user, $date->toDateString())->firstWhere('type', $type);

            if ($quest->completed_at !== null) {
                return;
            }

            $event = DailyQuestEvent::firstOrCreate([
                'user_id' => $user->id,
                'quest_date' => $date->toDateString(),
                'type' => $type,
                'event_key' => (string) $entityId,
            ]);

            if (! $event->wasRecentlyCreated) {
                return;
            }

            $progress = min($quest->target, $quest->progress + 1);
            $quest->update([
                'progress' => $progress,
                'completed_at' => $progress === $quest->target ? now() : null,
            ]);
        });
    }

    private function forDate(User $user, string $date): Collection
    {
        $defaults = [
            'complete_lesson' => ['target' => 1, 'reward_stars' => 2],
            'correct_answers' => ['target' => 5, 'reward_stars' => 3],
            'review_mistakes' => ['target' => 3, 'reward_stars' => 3],
        ];

        foreach (self::TYPES as $type) {
            $settings = config('progression.quests.'.$type, $defaults[$type]);
            $target = $settings['target'] ?? null;
            $stars = $settings['reward_stars'] ?? null;

            if (! is_int($target) || $target < 1 || ! is_int($stars) || $stars < 1) {
                throw new InvalidArgumentException('Quest targets and rewards must be positive integers.');
            }

            DailyQuest::firstOrCreate(
                ['user_id' => $user->id, 'quest_date' => $date, 'type' => $type],
                ['target' => $target, 'reward_stars' => $stars],
            );
        }

        return DailyQuest::where('user_id', $user->id)->whereDate('quest_date', $date)->orderBy('id')->get();
    }
}
