<?php

namespace App\Services\Progress;

use App\Enums\LearnerProficiency;
use App\Models\Course;
use App\Models\LearningAttempt;
use App\Models\Lesson;
use App\Models\LessonProgress;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class LearnerPromotionService
{
    public function promote(User $user): bool
    {
        $promoted = DB::transaction(function () use ($user): ?LearnerProficiency {
            $learner = User::query()->lockForUpdate()->find($user->id);

            if (! $learner || ! $learner->isLearner() || $learner->placement_required) {
                return null;
            }

            $next = match ($learner->learner_proficiency) {
                LearnerProficiency::Beginner => LearnerProficiency::Intermediate,
                LearnerProficiency::Intermediate => LearnerProficiency::Advanced,
                default => null,
            };

            if ($next === null) {
                return null;
            }

            $titles = config('progression.promotion.course_titles.'.$learner->learner_proficiency->value, []);
            if ($titles === []) {
                return null;
            }

            // Each course must qualify independently; unrelated languages cannot fill its gaps.
            foreach (Course::query()->whereIn('title', $titles)->get() as $course) {
                if ($this->courseQualifies($learner, $course)) {
                    $learner->learner_proficiency = $next;
                    $learner->save();

                    return $next;
                }
            }

            return null;
        });

        if ($promoted === null) {
            return false;
        }

        $user->learner_proficiency = $promoted;

        return true;
    }

    private function courseQualifies(User $learner, Course $course): bool
    {
        $lessonIds = Lesson::query()
            ->whereHas('unit', fn ($query) => $query->where('course_id', $course->id))
            ->pluck('id');

        if ($lessonIds->isEmpty()) {
            return false;
        }

        $completedIds = LessonProgress::query()
            ->where('user_id', $learner->id)
            ->whereIn('lesson_id', $lessonIds)
            ->whereNotNull('completed_at')
            ->pluck('lesson_id');

        $completionPercent = $completedIds->count() * 100 / $lessonIds->count();
        if ($completionPercent < (float) config('progression.promotion.completion_percent', 80)) {
            return false;
        }

        // Select the first completed round before filtering scores. A replay must not replace it.
        $firstScores = LearningAttempt::query()
            ->where('user_id', $learner->id)
            ->whereIn('lesson_id', $completedIds)
            ->whereNotNull('completed_at')
            ->orderBy('completed_at')
            ->orderBy('id')
            ->get(['id', 'lesson_id', 'question_count', 'score_percent'])
            ->unique('lesson_id')
            ->filter(fn (LearningAttempt $attempt) => $attempt->question_count > 0
                && $attempt->score_percent !== null)
            ->pluck('score_percent');

        $minimumScoredLessons = max(1, (int) config('progression.promotion.minimum_scored_lessons', 5));

        return $firstScores->count() >= $minimumScoredLessons
            && $firstScores->avg() >= (float) config('progression.promotion.first_attempt_score_percent', 75);
    }
}
