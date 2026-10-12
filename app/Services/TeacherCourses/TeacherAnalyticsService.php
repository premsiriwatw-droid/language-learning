<?php

namespace App\Services\TeacherCourses;

use App\Models\LearningAttempt;
use App\Models\Lesson;
use App\Models\LessonProgress;
use App\Models\TeacherCourse;
use App\Models\User;
use App\Services\Progress\PlayerLevelCalculator;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Pagination\LengthAwarePaginator;
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

    /**
     * Course activity is scoped to its curriculum. Global XP reads completed
     * rewards, matching LearningProgress::summary; the shared calculator sets Level.
     *
     * @return array{students: LengthAwarePaginator, lessons_count: int, as_of: Carbon}
     */
    public function studentList(TeacherCourse $course): array
    {
        $now = now();
        $studentIds = $course->enrollments()->learning()->select('user_id')->getQuery();
        $students = User::query()->select(['users.id', 'users.name'])
            ->whereIn('users.id', $studentIds)
            ->addSelect(['total_xp' => LessonProgress::query()
                ->selectRaw('COALESCE(SUM(xp), 0)')
                ->whereColumn('user_id', 'users.id')
                ->whereNotNull('completed_at')])
            ->orderBy('users.name')->orderBy('users.id')
            ->paginate(20);
        $lessons = $course->lessons()->get(['lessons.id', 'lessons.title']);
        $lessonCount = $lessons->count();
        $list = ['students' => $students, 'lessons_count' => $lessonCount, 'as_of' => $now];

        if ($students->isEmpty()) {
            return $list;
        }

        $pageStudentIds = $students->getCollection()->modelKeys();
        $progressByUser = LessonProgress::query()
            ->whereIn('user_id', $pageStudentIds)->whereIn('lesson_id', $lessons->modelKeys())
            ->orderByDesc('last_visited_at')->orderByDesc('id')
            ->get(['id', 'user_id', 'lesson_id', 'last_visited_at', 'completed_at'])
            ->groupBy('user_id');
        $attemptActivity = LearningAttempt::query()
            ->whereIn('user_id', $pageStudentIds)->whereIn('lesson_id', $lessons->modelKeys())
            ->where('completed_at', '<=', $now)
            ->select('user_id')->selectRaw('MAX(completed_at) as last_activity_at')
            ->groupBy('user_id')->pluck('last_activity_at', 'user_id');
        $playerLevels = app(PlayerLevelCalculator::class);

        $students->through(function (User $student) use ($progressByUser, $attemptActivity, $lessons, $lessonCount, $now, $playerLevels): array {
            $records = $progressByUser->get($student->id, collect());
            $completedIds = $records->filter(fn (LessonProgress $record): bool => $record->completed_at !== null
                && $record->completed_at->lessThanOrEqualTo($now))->pluck('lesson_id');
            $inProgress = $records->first(fn (LessonProgress $record): bool => ! $completedIds->contains($record->lesson_id)
                && $record->last_visited_at !== null && $record->last_visited_at->lessThanOrEqualTo($now));
            $currentLesson = $inProgress
                ? $lessons->firstWhere('id', $inProgress->lesson_id)
                : $lessons->first(fn (Lesson $lesson): bool => ! $completedIds->contains($lesson->id));
            $lastActivity = $records
                ->flatMap(fn (LessonProgress $record): array => [$record->last_visited_at, $record->completed_at])
                ->filter(fn (?Carbon $date): bool => $date !== null && $date->lessThanOrEqualTo($now))
                ->sortByDesc(fn (Carbon $date): int => $date->getTimestamp())->first();
            $lastAttempt = $attemptActivity->get($student->id);

            if ($lastAttempt !== null) {
                $lastAttempt = Carbon::parse($lastAttempt, config('app.timezone'));
                if ($lastActivity === null || $lastAttempt->greaterThan($lastActivity)) {
                    $lastActivity = $lastAttempt;
                }
            }

            $xp = (int) $student->total_xp;

            return [
                'id' => $student->id,
                'name' => $student->name,
                'completed_lessons' => $completedIds->count(),
                'progress_percent' => $lessonCount === 0 ? null : round($completedIds->count() * 100 / $lessonCount, 1),
                'xp' => $xp,
                'level' => $playerLevels->calculate($xp)['level'],
                'last_activity_at' => $lastActivity,
                'current_lesson' => $currentLesson,
                'current_lesson_started' => $inProgress !== null,
            ];
        });

        return $list;
    }
}
