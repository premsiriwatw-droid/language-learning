<?php

namespace App\Services\Progress;

use App\Models\LessonProgress;
use App\Models\StarReward;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\Schema;
use InvalidArgumentException;

class StarRewardService
{
    /** Bonus rewards only; first-completion stars remain in lesson_progress. */
    public function add(User $user, string $sourceKey, int $stars): StarReward
    {
        if (! $user->isLearner()) {
            throw new AuthorizationException('Only learners receive progression rewards.');
        }

        if ($stars < 1 || trim($sourceKey) === '' || strlen($sourceKey) > 191) {
            throw new InvalidArgumentException('A positive star reward and valid source key are required.');
        }

        return StarReward::firstOrCreate(
            ['user_id' => $user->id, 'source_key' => $sourceKey],
            ['stars' => $stars],
        );
    }

    public function totalBonus(User $user): int
    {
        if (! Schema::hasTable('star_rewards')) {
            return 0;
        }

        return (int) StarReward::where('user_id', $user->id)->sum('stars');
    }

    /** Called only after a genuinely first lesson completion. */
    public function milestone(User $user): ?StarReward
    {
        if (! $user->isLearner()) {
            return null;
        }

        $interval = config('progression.stars.milestone_lessons', 5);
        $stars = config('progression.stars.milestone_reward', 10);

        if (! is_int($interval) || $interval < 1 || ! is_int($stars) || $stars < 1) {
            throw new InvalidArgumentException('Milestone settings must be positive integers.');
        }

        $completed = LessonProgress::where('user_id', $user->id)
            ->whereNotNull('completed_at')->lockForUpdate()->get(['id'])->count();

        if ($completed === 0 || $completed % $interval !== 0) {
            return null;
        }

        return $this->add($user, 'milestone:'.$completed, $stars);
    }
}
