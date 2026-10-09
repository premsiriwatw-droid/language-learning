<?php

namespace Tests\Unit\Progress;

use App\Services\Progress\PlayerLevelCalculator;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class PlayerLevelCalculatorTest extends TestCase
{
    #[DataProvider('levelCases')]
    public function test_level_boundaries(int $xp, int $level, int $current, int $remaining): void
    {
        config()->set('learning.levels.xp_per_level', 200);
        $result = app(PlayerLevelCalculator::class)->calculate($xp);
        $this->assertSame($level, $result['level']);
        $this->assertSame($current, $result['current_xp']);
        $this->assertSame($remaining, $result['remaining_xp']);
        $this->assertSame($xp, $result['total_xp']);
    }

    public static function levelCases(): array
    {
        return [
            [0, 1, 0, 200], [199, 1, 199, 1], [200, 2, 0, 200],
            [399, 2, 199, 1], [400, 3, 0, 200], [730, 4, 130, 70],
        ];
    }

    public function test_threshold_is_configurable(): void
    {
        config()->set('learning.levels.xp_per_level', 100);
        $this->assertSame(4, app(PlayerLevelCalculator::class)->calculate(300)['level']);
    }

    public function test_negative_xp_is_rejected(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        app(PlayerLevelCalculator::class)->calculate(-1);
    }

    public function test_invalid_threshold_is_rejected(): void
    {
        config()->set('learning.levels.xp_per_level', 0);
        $this->expectException(\InvalidArgumentException::class);
        app(PlayerLevelCalculator::class)->calculate(10);
    }
}
