<?php

namespace App\Services\Progress;

use App\Models\LearningAttempt;

class LearnerActivityService
{
    public function completed(LearningAttempt $attempt): void
    {
        $user = $attempt->user;
        if (! $user || ! $user->isLearner()) {
            return;
        }

        app(StreakService::class)->record($user, $attempt->completed_at);
        $quests = app(DailyQuestService::class);
        if ($attempt->lesson_id !== null) {
            $quests->recordLesson($user, $attempt->lesson_id, $attempt->completed_at);
        }
        foreach ($attempt->results as $result) {
            if (($result['first_correct'] ?? false) === true && isset($result['question_id'])) {
                $quests->recordCorrectAnswer($user, (int) $result['question_id'], $attempt->completed_at);
            }
        }

        app(LearnerPromotionService::class)->promote($user);
    }
}
