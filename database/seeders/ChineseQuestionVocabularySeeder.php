<?php

namespace Database\Seeders;

use App\Models\Lesson;
use App\Models\Question;
use App\Services\Content\QuestionVocabularyImporter;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class ChineseQuestionVocabularySeeder extends Seeder
{
    public function run(): void
    {
        $content = require __DIR__.'/data/chinese.php';
        $importer = app(QuestionVocabularyImporter::class);

        $updated = 0;
        $checked = 0;

        DB::transaction(function () use (
            $content,
            $importer,
            &$updated,
            &$checked
        ) {
            foreach ($content as $title => $lessonContent) {
                $lessons = Lesson::where('title', $title)
                    ->whereHas(
                        'unit.course.language',
                        fn ($query) => $query->where('name', 'Chinese')
                    )
                    ->get();

                if ($lessons->count() !== 1) {
                    throw new RuntimeException(
                        "ต้องมีบทภาษาจีน [{$title}] "
                        . 'เพียงหนึ่งบทก่อน backfill'
                    );
                }

                $lesson = $lessons->sole();

                foreach ($lessonContent['exercises'] as $type => $items) {
                    $exercises = $lesson->exercises()
                        ->where('type', $type)
                        ->where('title', 'Chinese MVP: '.$type)
                        ->get();

                    if ($exercises->count() !== 1) {
                        throw new RuntimeException(
                            'ไม่พบแบบฝึกหัดที่ตรงเพียงหนึ่งรายการ: '
                            . "[{$title}] [{$type}]"
                        );
                    }

                    if (isset($items['question'])) {
                        $items = [$items];
                    }

                    foreach ($items as $item) {
                        $questions = $exercises->sole()
                            ->questions()
                            ->where('question', $item['question'])
                            ->get();

                        if ($questions->count() !== 1) {
                            throw new RuntimeException(
                                'ไม่พบคำถามที่ตรงเพียงหนึ่งข้อ: '
                                . "[{$title}] [{$item['question']}]"
                            );
                        }

                        if ($importer->apply($questions->sole(), $item)) {
                            $updated++;
                        }

                        $checked++;
                    }
                }
            }
        });

        $pending = Question::query()
            ->where('vocabulary_mode', 'pending')
            ->whereHas(
                'exercise.lesson.unit.course.language',
                fn ($query) => $query->where('name', 'Chinese')
            )
            ->count();

        $this->command?->info(
            "ตรวจคำถามจาก content {$checked} ข้อ "
            . "อัปเดต mapping {$updated} ข้อ"
        );

        if ($pending > 0) {
            $this->command?->warn(
                "ยังมีคำถามภาษาจีน {$pending} ข้อที่เป็น pending "
                . 'กรุณาจัดคำศัพท์ผ่าน Admin '
                . 'หรือเพิ่ม metadata ใน content'
            );
        }
    }
}