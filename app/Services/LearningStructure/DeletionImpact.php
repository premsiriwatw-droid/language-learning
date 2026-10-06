<?php

namespace App\Services\LearningStructure;

use App\Models\Answer;
use App\Models\Course;
use App\Models\Exercise;
use App\Models\Language;
use App\Models\Lesson;
use App\Models\LessonProgress;
use App\Models\Question;
use App\Models\Unit;
use App\Models\Vocabulary;

/**
 * นับว่าการลบ Language/Course/Unit/Lesson หนึ่งรายการจะลบข้อมูลลูก
 * อะไรตามไปด้วยบ้าง (FK เป็น cascade ทั้งสาย) เพื่อแสดงให้ Admin เห็น
 * ก่อนยืนยันการลบ
 *
 * คลาสนี้อ่านอย่างเดียว ไม่แก้ไขข้อมูลของ Content / Quiz / Progress
 */
class DeletionImpact
{
    /**
     * @return array<string, int>  label => count (เฉพาะรายการที่ > 0)
     */
    public static function for(Language|Course|Unit|Lesson $model): array
    {
        $counts = match (true) {
            $model instanceof Language => [
                'คอร์ส' => $model->courses()->count(),
                'Unit' => Unit::whereIn('course_id', $model->courses()->select('id'))->count(),
            ],
            $model instanceof Course => [
                'Unit' => $model->units()->count(),
            ],
            default => [],
        };

        $lessonIds = self::lessonIdsUnder($model);

        $exerciseIds = Exercise::whereIn('lesson_id', $lessonIds)->pluck('id');
        $questionIds = Question::whereIn('exercise_id', $exerciseIds)->pluck('id');

        $counts += [
            'Lesson' => $model instanceof Lesson ? 0 : count($lessonIds),
            'คำศัพท์' => Vocabulary::whereIn('lesson_id', $lessonIds)->count(),
            'แบบฝึกหัด' => $exerciseIds->count(),
            'คำถาม' => $questionIds->count(),
            'คำตอบ' => Answer::whereIn('question_id', $questionIds)->count(),
            'ความคืบหน้าของผู้เรียน' => LessonProgress::whereIn('lesson_id', $lessonIds)->count(),
        ];

        return array_filter($counts, fn (int $count) => $count > 0);
    }

    /**
     * @return array<int, int>
     */
    private static function lessonIdsUnder(Language|Course|Unit|Lesson $model): array
    {
        $query = match (true) {
            $model instanceof Lesson => Lesson::whereKey($model->getKey()),
            $model instanceof Unit => Lesson::where('unit_id', $model->getKey()),
            $model instanceof Course => Lesson::whereIn('unit_id', Unit::where('course_id', $model->getKey())->select('id')),
            $model instanceof Language => Lesson::whereIn(
                'unit_id',
                Unit::whereIn('course_id', Course::where('language_id', $model->getKey())->select('id'))->select('id')
            ),
        };

        return $query->pluck('id')->all();
    }
}
