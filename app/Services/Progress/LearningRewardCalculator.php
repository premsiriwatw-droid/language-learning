<?php

namespace App\Services\Progress;

use InvalidArgumentException;

class LearningRewardCalculator
{
    public function calculate(
        int $questionCount,
        int $firstCorrectCount
    ): array {
        if (
            $questionCount < 0
            || $firstCorrectCount < 0
            || $firstCorrectCount > $questionCount
        ) {
            throw new InvalidArgumentException(
                'จำนวนคำถามหรือจำนวนคำตอบถูกไม่ถูกต้อง'
            );
        }

        $completionXp = (int) config(
            'learning.rewards.completion_xp',
            20
        );

        $xpPerCorrect = (int) config(
            'learning.rewards.xp_per_first_correct',
            10
        );

        $threeStarPercent = (int) config(
            'learning.rewards.three_star_percent',
            90
        );

        $twoStarPercent = (int) config(
            'learning.rewards.two_star_percent',
            70
        );

        $vocabularyStars = (int) config(
            'learning.rewards.vocabulary_only_stars',
            1
        );

        if (
            $completionXp < 0
            || $xpPerCorrect < 0
            || $threeStarPercent < 0
            || $threeStarPercent > 100
            || $twoStarPercent < 0
            || $twoStarPercent > $threeStarPercent
            || $vocabularyStars < 0
            || $vocabularyStars > 3
        ) {
            throw new InvalidArgumentException(
                'การตั้งค่ารางวัลไม่ถูกต้อง'
            );
        }

        if ($questionCount === 0) {
            $stars = $vocabularyStars;
        } else {
            // เปรียบเทียบจากจำนวนข้อโดยตรง ไม่ใช้คะแนนที่ปัดเศษ
            $stars = match (true) {
                $firstCorrectCount * 100
                    >= $questionCount * $threeStarPercent => 3,

                $firstCorrectCount * 100
                    >= $questionCount * $twoStarPercent => 2,

                default => 1,
            };
        }

        return [
            'xp' => $completionXp + ($firstCorrectCount * $xpPerCorrect),
            'stars' => $stars,
        ];
    }
}