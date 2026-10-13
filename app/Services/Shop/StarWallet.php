<?php

namespace App\Services\Shop;

use App\Models\LessonProgress;
use App\Models\Purchase;
use App\Models\User;
use App\Services\Progress\StarRewardService;
use Illuminate\Support\Facades\Schema;

/** Read adapter for existing rewards; purchases never rewrite XP or earned stars. */
class StarWallet
{
    /** @return array{earned: int, spent: int, balance: int} */
    public function summary(User $user): array
    {
        $lessonStars = Schema::hasTable('lesson_progress')
            ? (int) LessonProgress::where('user_id', $user->id)->whereNotNull('completed_at')->sum('stars')
            : 0;
        $earned = $lessonStars + app(StarRewardService::class)->totalBonus($user);
        $spent = (int) Purchase::where('user_id', $user->id)->sum('price_stars');

        return ['earned' => $earned, 'spent' => $spent, 'balance' => max(0, $earned - $spent)];
    }
}
