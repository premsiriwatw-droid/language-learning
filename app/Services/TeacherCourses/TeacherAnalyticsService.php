<?php

namespace App\Services\TeacherCourses;

use App\Models\LearningAttempt;
use App\Models\LessonProgress;
use App\Models\TeacherCourse;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Support\Carbon;

class TeacherAnalyticsService
{
    /**
     * Learning records belong to a learner and a system lesson, so shared lessons
     * include earlier study and cannot be attributed to a specific teacher course.
     *
     * @return array{
     *     students_count: int, active_this_week: int, lessons_count: int,
     *     average_progress: ?float, average_first_attempt_score: ?float,
     *     scored_first_attempts: int, week_started_at: Carbon, as_of: Carbon
     * }
     */
    public function summary(TeacherCourse $course): array
    {
        $now = now();
        $weekStart = $now->copy()->startOfWeek(CarbonInterface::MONDAY);
        $studentIds = $course->enrollments()->learning()->select('user_id')->getQuery();
        $studentCount = (clone $studentIds)->count();
        $lessonIds = $course->lessons()->pluck('lessons.id');

        $summary = [
            'students_count' => $studentCount,
            'active_this_week' => 0,
            'lessons_count' => $lessonIds->count(),
            'average_progress' => null,
            'average_first_attempt_score' => null,
            'scored_first_attempts' => 0,
            'week_started_at' => $weekStart,
            'as_of' => $now,
        ];

        if ($studentCount === 0 || $lessonIds->isEmpty()) {
            return $summary;
        }

        $progress = LessonProgress::query()
            ->whereIn('user_id', $studentIds)
            ->whereIn('lesson_id', $lessonIds);
        $attempts = LearningAttempt::query()
            ->whereIn('user_id', $studentIds)
            ->whereIn('lesson_id', $lessonIds)
            ->whereNotNull('completed_at')
            ->where('completed_at', '<=', $now);

        $completedLessons = (clone $progress)
            ->whereNotNull('completed_at')
            ->where('completed_at', '<=', $now)
            ->count();
        $activeProgress = (clone $progress)
            ->where(function (Builder $activity) use ($weekStart, $now): void {
                $activity->whereBetween('last_visited_at', [$weekStart, $now])
                    ->orWhereBetween('completed_at', [$weekStart, $now]);
            })
            ->select('user_id');
        $activeAttempts = (clone $attempts)
            ->whereBetween('completed_at', [$weekStart, $now])
            ->select('user_id');

        $summary['active_this_week'] = (clone $studentIds)
            ->where(function (Builder $students) use ($activeProgress, $activeAttempts): void {
                $students->whereIn('user_id', $activeProgress)
                    ->orWhereIn('user_id', $activeAttempts);
            })
            ->count();
        $summary['average_progress'] = round($completedLessons * 100 / ($studentCount * $lessonIds->count()), 1);

        $firstAttempts = (clone $attempts)
            ->whereNotExists(function (QueryBuilder $earlier): void {
                $earlier->selectRaw('1')->from('learning_attempts as earlier_attempts')
                    ->whereColumn('earlier_attempts.user_id', 'learning_attempts.user_id')
                    ->whereColumn('earlier_attempts.lesson_id', 'learning_attempts.lesson_id')
                    ->whereNotNull('earlier_attempts.completed_at')
                    ->where(function (QueryBuilder $order): void {
                        $order->whereColumn('earlier_attempts.completed_at', '<', 'learning_attempts.completed_at')
                            ->orWhere(function (QueryBuilder $tie): void {
                                $tie->whereColumn('earlier_attempts.completed_at', 'learning_attempts.completed_at')
                                    ->whereColumn('earlier_attempts.id', '<', 'learning_attempts.id');
                            });
                    });
            })
            ->where('question_count', '>', 0)
            ->whereNotNull('score_percent');

        $scores = $firstAttempts->toBase()
            ->selectRaw('COUNT(*) as scored_attempts, AVG(score_percent) as average_score')
            ->first();

        $summary['scored_first_attempts'] = (int) $scores->scored_attempts;
        $summary['average_first_attempt_score'] = $scores->average_score === null
            ? null
            : round((float) $scores->average_score, 1);

        return $summary;
    }
}
