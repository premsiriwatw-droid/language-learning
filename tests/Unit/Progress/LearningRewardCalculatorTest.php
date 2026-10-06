<?php

namespace Tests\Unit\Progress;

use App\Services\Progress\LearningRewardCalculator;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class LearningRewardCalculatorTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config()->set('learning.rewards', [
            'completion_xp' => 20,
            'xp_per_first_correct' => 10,
            'three_star_percent' => 90,
            'two_star_percent' => 70,
            'vocabulary_only_stars' => 1,
        ]);
    }

    #[DataProvider('rewardCases')]
    public function test_rewards_follow_the_scoring_policy(
        int $questionCount,
        int $firstCorrectCount,
        int $expectedXp,
        int $expectedStars
    ): void {
        $calculator = new LearningRewardCalculator();

        $this->assertSame(
            [
                'xp' => $expectedXp,
                'stars' => $expectedStars,
            ],
            $calculator->calculate(
                $questionCount,
                $firstCorrectCount
            )
        );
    }

    public static function rewardCases(): array
    {
        return [
            '100 percent' => [10, 10, 120, 3],
            '90 percent' => [10, 9, 110, 3],
            '80 percent' => [10, 8, 100, 2],
            '70 percent' => [10, 7, 90, 2],
            '60 percent' => [10, 6, 80, 1],
            'all first answers wrong' => [10, 0, 20, 1],
            'vocabulary only' => [0, 0, 20, 1],
        ];
    }

    #[DataProvider('invalidCounts')]
    public function test_invalid_counts_are_rejected(
        int $questionCount,
        int $firstCorrectCount
    ): void {
        $this->expectException(InvalidArgumentException::class);

        (new LearningRewardCalculator())->calculate(
            $questionCount,
            $firstCorrectCount
        );
    }

    public static function invalidCounts(): array
    {
        return [
            'negative question count' => [-1, 0],
            'negative correct count' => [10, -1],
            'correct count exceeds questions' => [10, 11],
        ];
    }

    public function test_xp_values_can_be_changed_in_configuration(): void
    {
        config()->set('learning.rewards.completion_xp', 30);
        config()->set('learning.rewards.xp_per_first_correct', 5);

        $this->assertSame(
            [
                'xp' => 70,
                'stars' => 2,
            ],
            (new LearningRewardCalculator())->calculate(10, 8)
        );
    }
}