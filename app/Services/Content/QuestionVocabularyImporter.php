<?php

namespace App\Services\Content;

use App\Models\Question;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class QuestionVocabularyImporter
{
    public function apply(Question $question, array $item): bool
    {
        return DB::transaction(function () use ($question, $item) {
            $stored = Question::query()
                ->lockForUpdate()
                ->findOrFail($question->id);

            $lesson = $stored->exercise?->lesson;

            if (!$lesson) {
                throw new RuntimeException(
                    'คำถามต้องอยู่ในบทเรียนก่อนผูกคำศัพท์'
                );
            }

            $existing = $stored->vocabularies()->get();

            foreach ($existing as $vocabulary) {
                if ((int) $vocabulary->lesson_id !== (int) $lesson->id) {
                    throw new RuntimeException(
                        "คำถาม [{$stored->question}] ผูกคำศัพท์ข้ามบท"
                    );
                }
            }

            // รักษา mapping ที่จัดไว้แล้ว รวมถึงการแก้ผ่าน Admin
            if ($stored->vocabulary_mode === 'after_vocabulary') {
                if ($existing->isEmpty()) {
                    throw new RuntimeException(
                        "คำถาม [{$stored->question}] "
                        . 'กำหนดให้ออกหลังศัพท์ แต่ไม่มีศัพท์ที่ผูกไว้'
                    );
                }

                return false;
            }

            if ($stored->vocabulary_mode === 'lesson_end') {
                if ($existing->isNotEmpty()) {
                    throw new RuntimeException(
                        "คำถาม [{$stored->question}] "
                        . 'เป็นทบทวนท้ายบท แต่ยังมีศัพท์ที่ผูกไว้'
                    );
                }

                return false;
            }

            // รองรับ mapping เดิมก่อนมีคอลัมน์ vocabulary_mode
            if ($existing->isNotEmpty()) {
                $stored->update([
                    'vocabulary_mode' => 'after_vocabulary',
                ]);

                return true;
            }

            $hasMode = array_key_exists('vocabulary_mode', $item);
            $hasWords = array_key_exists(
                'required_vocabulary_words',
                $item
            );

            // Content ของเพื่อนยังไม่มี metadata:
            // นำเข้าได้ แต่ยังไม่ถือว่าจัด mapping เสร็จ
            if (!$hasMode && !$hasWords) {
                $resolved = app(QuestionVocabularyResolver::class)->resolve(
                    $item,
                    $lesson->vocabularies()->orderBy('id')->pluck('word')->all()
                );

                if ($resolved === []) {
                    // รายงานให้ตรวจ ไม่เดาคำศัพท์และไม่ประกาศเป็นท้ายบทเอง
                    if ($stored->vocabulary_mode !== 'pending') {
                        $stored->update(['vocabulary_mode' => 'pending']);
                    }

                    return false;
                }

                $item['vocabulary_mode'] = 'after_vocabulary';
                $item['required_vocabulary_words'] = $resolved;
                $hasMode = true;
                $hasWords = true;
            }

            // ถ้าระบุ metadata ต้องระบุให้ครบทั้งสองช่อง
            if (!$hasMode || !$hasWords) {
                throw new RuntimeException(
                    "คำถาม [{$stored->question}] ต้องระบุ "
                    . 'vocabulary_mode และ required_vocabulary_words '
                    . 'ให้ครบทั้งสองช่อง'
                );
            }

            $mode = $item['vocabulary_mode'];
            $words = $item['required_vocabulary_words'];

            if (!in_array(
                $mode,
                ['after_vocabulary', 'lesson_end'],
                true
            )) {
                throw new RuntimeException(
                    "คำถาม [{$stored->question}] "
                    . 'มี vocabulary_mode ไม่ถูกต้อง'
                );
            }

            if (!is_array($words) || !array_is_list($words)) {
                throw new RuntimeException(
                    'required_vocabulary_words ต้องเป็นรายการคำศัพท์'
                );
            }

            foreach ($words as $word) {
                if (!is_string($word) || trim($word) === '') {
                    throw new RuntimeException(
                        'คำศัพท์ที่ต้องเรียนก่อนต้องเป็นข้อความที่ไม่ว่าง'
                    );
                }
            }

            if (count(array_unique($words)) !== count($words)) {
                throw new RuntimeException(
                    'คำศัพท์ที่ต้องเรียนก่อนต้องไม่ซ้ำกัน'
                );
            }

            if (
                ($mode === 'after_vocabulary' && count($words) === 0)
                || ($mode === 'lesson_end' && count($words) > 0)
            ) {
                throw new RuntimeException(
                    'after_vocabulary ต้องเลือกศัพท์ '
                    . 'ส่วน lesson_end ต้องใช้รายการศัพท์ว่าง'
                );
            }

            $ids = [];

            foreach ($words as $word) {
                $matches = $lesson->vocabularies()
                    ->where('word', $word)
                    ->get();

                if ($matches->count() !== 1) {
                    throw new RuntimeException(
                        "บท [{$lesson->title}] ต้องมีคำศัพท์ "
                        . "[{$word}] เพียงหนึ่งรายการ"
                    );
                }

                $ids[] = $matches->sole()->id;
            }

            $stored->vocabularies()->sync($ids);

            $stored->update([
                'vocabulary_mode' => $mode,
            ]);

            return true;
        });
    }
}