<?php

namespace App\Services\Progress;

use App\Models\Lesson;
use App\Models\LessonProgress;
use App\Models\Unit;
use App\Models\User;

class LessonAccess
{
    public function __construct(
        private LearningProgress $progress
    ) {
    }

    public function forCourse(int $courseId, ?User $user): array
    {
        $units = Unit::query()
            ->where('course_id', $courseId)
            ->orderBy('position')
            ->orderBy('id')
            ->with([
                'lessons' => function ($query) {
                    $query->orderBy('position')->orderBy('id')
                        ->withExists([
                            'vocabularies',
                            'exercises as playable_questions_exist' => function ($query) {
                                $query->whereIn('type', [
                                    'multiple_choice',
                                    'fill_blank',
                                    'listening',
                                    'image_choice',
                                ])->whereHas('questions');
                            },
                        ]);
                },
            ])
            ->get();

        $lessons = $units->flatMap(
            fn (Unit $unit) => $unit->lessons
        );

        $completedIds = [];

        if ($user && $this->progress->available()) {
            $completedIds = LessonProgress::query()
                ->where('user_id', $user->id)
                ->whereIn('lesson_id', $lessons->pluck('id'))
                ->whereNotNull('completed_at')
                ->pluck('lesson_id')
                ->mapWithKeys(fn ($id) => [(int) $id => true])
                ->all();
        }

        $unitStates = app(UnitAccess::class)->forCourse($courseId, $user);
        $states = [];
        $previousPlayableLesson = null;

        foreach ($lessons as $lesson) {
            $hasContent = (bool) $lesson->vocabularies_exists
                || (bool) $lesson->playable_questions_exist;

            $completed = isset($completedIds[$lesson->id]);

            if (!$hasContent) {
                $states[$lesson->id] = [
                    'available' => false,
                    'completed' => $completed,
                    'reason' => 'บทนี้ยังไม่มีเนื้อหาสำหรับเรียน',
                ];

                continue;
            }

            $available = ($unitStates[$lesson->unit_id]['available'] ?? false) && ($completed
                || $previousPlayableLesson === null
                || isset($completedIds[$previousPlayableLesson->id]));

            $reason = null;

            if (!($unitStates[$lesson->unit_id]['available'] ?? false)) {
                $reason = $unitStates[$lesson->unit_id]['reason'] ?? 'Unit นี้ยังไม่เปิดให้เรียน';
            } elseif (!$available) {
                $reason = $user
                    ? 'เรียนบท "' . $previousPlayableLesson->title . '" ให้จบก่อน'
                    : 'เข้าสู่ระบบและเรียนบทก่อนหน้าให้จบก่อน';
            }

            $states[$lesson->id] = [
                'available' => $available,
                'completed' => $completed,
                'reason' => $reason,
            ];

            $previousPlayableLesson = $lesson;
        }

        return $states;
    }

    public function canLearn(?User $user, Lesson $lesson): bool
    {
        $courseId = $lesson->unit?->course_id;

        if ($courseId === null) {
            return false;
        }

        $states = $this->forCourse((int) $courseId, $user);

        return $states[$lesson->id]['available'] ?? false;
    }
}   