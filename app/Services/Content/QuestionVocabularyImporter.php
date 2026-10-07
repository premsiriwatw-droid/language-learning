<?php

namespace App\Services\Content;

use App\Models\Question;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class QuestionVocabularyImporter
{
    public function apply(Question $question, array $item): bool
    {
        $mode = $item['vocabulary_mode'] ?? null;
        $words = $item['required_vocabulary_words'] ?? null;

        if (!in_array($mode, ['after_vocabulary', 'lesson_end'], true)) {
            throw new RuntimeException(
                "คำถาม [{$question->question}] ต้องระบุ vocabulary_mode ใน content"
            );
        }

        if (!is_array($words) || !array_is_list($words)) {
            throw new RuntimeException(
                "คำถาม [{$question->question}] ต้องระบุ required_vocabulary_words เป็นรายการ"
            );
        }

        foreach ($words as $word) {
            if (!is_string($word) || trim($word) === '') {
                throw new RuntimeException('คำศัพท์ที่ต้องเรียนก่อนต้องเป็นข้อความที่ไม่ว่าง');
            }
        }

        if (count(array_unique($words)) !== count($words)) {
            throw new RuntimeException('คำศัพท์ที่ต้องเรียนก่อนต้องไม่ซ้ำกัน');
        }

        if (
            ($mode === 'after_vocabulary' && count($words) === 0)
            || ($mode === 'lesson_end' && count($words) > 0)
        ) {
            throw new RuntimeException(
                'after_vocabulary ต้องเลือกศัพท์ ส่วน lesson_end ต้องใช้รายการศัพท์ว่าง'
            );
        }

        $lesson = $question->exercise?->lesson;

        if (!$lesson) {
            throw new RuntimeException('คำถามต้องอยู่ในบทเรียนก่อนผูกคำศัพท์');
        }

        $ids = [];

        foreach ($words as $word) {
            $matches = $lesson->vocabularies()->where('word', $word)->get();

            if ($matches->count() !== 1) {
                throw new RuntimeException(
                    "บท [{$lesson->title}] ต้องมีคำศัพท์ [{$word}] เพียงหนึ่งรายการ"
                );
            }

            $ids[] = $matches->sole()->id;
        }

        return DB::transaction(function () use ($question, $mode, $ids) {
            $stored = Question::query()->lockForUpdate()->findOrFail($question->id);

            // ไม่ทับ mapping ที่จัดไว้แล้ว รวมถึงการแก้ผ่าน Admin
            if (in_array($stored->vocabulary_mode, ['after_vocabulary', 'lesson_end'], true)) {
                return false;
            }

            // รักษาความสัมพันธ์เดิมจากระบบก่อนมี vocabulary_mode
            $existing = $stored->vocabularies()->get();

            if ($existing->isNotEmpty()) {
                foreach ($existing as $vocabulary) {
                    if ((int) $vocabulary->lesson_id !== (int) $stored->exercise->lesson_id) {
                        throw new RuntimeException('พบ mapping เดิมที่ผูกคำศัพท์ข้ามบท');
                    }
                }

                $stored->update(['vocabulary_mode' => 'after_vocabulary']);

                return true;
            }

            $stored->vocabularies()->sync($ids);
            $stored->update(['vocabulary_mode' => $mode]);

            return true;
        });
    }
}
