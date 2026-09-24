<?php

namespace App\Services\Progress;

use App\Models\Lesson;
use App\Models\LessonProgress;
use App\Models\User;
use Illuminate\Support\Facades\Schema;
use InvalidArgumentException;

class LearningProgress
{
    public function available(): bool
    {
        return Schema::hasTable('lesson_progress');
    }

    public function visit(User $user, Lesson $lesson, int $step): void
    {
        if (! $this->available()) {
            return;
        }

        LessonProgress::updateOrCreate(
            ['user_id' => $user->id, 'lesson_id' => $lesson->id],
            ['last_step' => max(1, $step), 'last_visited_at' => now()],
        );
    }

    /**
     * Integration point for a trusted backend AFTER it verifies lesson completion.
     * Rewards come from the team's scoring policy, never from request input.
     * There is deliberately no public endpoint that grants rewards.
     */
    public function complete(User $user, Lesson $lesson, int $xp, int $stars): LessonProgress
    {
        if ($xp < 0 || $stars < 0) {
            throw new InvalidArgumentException('Rewards must be non-negative.');
        }

        $progress = LessonProgress::firstOrCreate([
            'user_id' => $user->id,
            'lesson_id' => $lesson->id,
        ]);

        // One conditional update prevents replayed completion from awarding twice.
        LessonProgress::whereKey($progress->id)->whereNull('completed_at')->update([
            'completed_at' => now(),
            'xp' => $xp,
            'stars' => $stars,
        ]);

        return $progress->refresh();
    }

    public function summary(User $user): array
    {
        $available = $this->available();
        $total = Lesson::count();
        $records = $available
            ? LessonProgress::where('user_id', $user->id)->get()
            : collect();
        $completed = $records->whereNotNull('completed_at');
        $latest = $available
            ? LessonProgress::with('lesson.unit.course')
                ->where('user_id', $user->id)
                ->whereNotNull('last_visited_at')
                ->orderByDesc('last_visited_at')->orderByDesc('id')->first()
            : null;

        return [
            'available' => $available,
            'total' => $total,
            'completed' => $completed->count(),
            'percentage' => $total > 0 ? (int) round($completed->count() / $total * 100) : 0,
            'xp' => (int) $completed->sum('xp'),
            'stars' => (int) $completed->sum('stars'),
            'latest' => $latest,
        ];
    }
}
