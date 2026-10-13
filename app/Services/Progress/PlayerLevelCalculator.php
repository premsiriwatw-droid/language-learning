<?php

namespace App\Services\Progress;

use InvalidArgumentException;

class PlayerLevelCalculator
{
    public function calculate(int $xp): array
    {
        if ($xp < 0) {
            throw new InvalidArgumentException('XP must be non-negative.');
        }
        $required = config('learning.levels.xp_per_level', 200);
        if (!is_int($required) || $required < 1) {
            throw new InvalidArgumentException('xp_per_level must be a positive integer.');
        }
        $currentXp = $xp % $required;
        return [
            'level' => intdiv($xp, $required) + 1,
            'total_xp' => $xp,
            'current_xp' => $currentXp,
            'required_xp' => $required,
            'remaining_xp' => $required - $currentXp,
            'percent' => (int) floor($currentXp / $required * 100),
        ];
    }
}
