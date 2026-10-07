<?php

namespace App\Services\Progress;

use App\Models\LessonProgress;
use App\Models\Unit;
use App\Models\User;

class UnitAccess
{
    public function __construct(private LearningProgress $progress)
    {
    }

    public function forCourse(int $courseId, ?User $user): array
    {
        $units = Unit::where('course_id', $courseId)
            ->orderBy('position')->orderBy('id')
            ->with(['lessons' => function ($query) {
                $query->orderBy('position')->orderBy('id')->withExists([
                    'vocabularies',
                    'exercises as playable_questions_exist' => function ($query) {
                        $query->whereIn('type', [
                            'multiple_choice', 'fill_blank', 'listening', 'image_choice',
                        ])->whereHas('questions');
                    },
                ]);
            }])->get();

        $completedIds = [];

        if ($user && $this->progress->available()) {
            $completedIds = LessonProgress::where('user_id', $user->id)
                ->whereIn('lesson_id', $units->flatMap(fn ($unit) => $unit->lessons)->pluck('id'))
                ->whereNotNull('completed_at')
                ->pluck('lesson_id')->mapWithKeys(fn ($id) => [(int) $id => true])->all();
        }

        $states = [];
        $allPreviousCompleted = true;

        foreach ($units as $unit) {
            $playable = $unit->lessons->filter(fn ($lesson) =>
                (bool) $lesson->vocabularies_exists || (bool) $lesson->playable_questions_exist
            );

            $total = $playable->count();
            $completed = $playable->filter(fn ($lesson) => isset($completedIds[$lesson->id]))->count();
            $available = $total > 0 && $allPreviousCompleted;

            $states[$unit->id] = [
                'available' => $available,
                'completed' => $total > 0 && $completed === $total,
                'completed_count' => $completed,
                'lesson_count' => $total,
                'reason' => $total === 0
                    ? 'Unit นี้ยังไม่มีเนื้อหาสำหรับเรียน'
                    : ($available ? null : 'เรียนบทที่มีเนื้อหาใน Unit ก่อนหน้าให้ครบก่อน'),
            ];

            // Unit ว่างไม่ขวางการปลดล็อก แต่ Unit ที่มีเนื้อหาต้องจบครบ
            if ($total > 0) {
                $allPreviousCompleted = $allPreviousCompleted && $completed === $total;
            }
        }

        return $states;
    }

    public function canOpen(?User $user, Unit $unit): bool
    {
        return $this->forCourse((int) $unit->course_id, $user)[$unit->id]['available'] ?? false;
    }
}
