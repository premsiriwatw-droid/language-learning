<?php

namespace Tests\Feature;

use App\Models\Lesson;
use Database\Seeders\ChineseContentSeeder;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ChineseContentSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_chinese_content_seeder_creates_expected_content(): void
    {
        $this->seed(DatabaseSeeder::class);

        $lessons = Lesson::with([
            'vocabularies',
            'exercises.questions.answers',
        ])
            ->whereHas(
                'unit.course.language',
                fn ($query) => $query->where('name', 'Chinese')
            )
            ->get();

        $this->assertCount(3, $lessons);

        $greetings = $lessons->firstWhere('title', 'Greetings');
        $selfIntroduction = $lessons->firstWhere('title', 'Self Introduction');
        $numbers = $lessons->firstWhere('title', 'Numbers');

        $this->assertNotNull($greetings);
        $this->assertNotNull($selfIntroduction);
        $this->assertNotNull($numbers);

        /*
        |--------------------------------------------------------------------------
        | Vocabulary
        |--------------------------------------------------------------------------
        */

        $this->assertCount(6, $greetings->vocabularies);
        $this->assertCount(6, $selfIntroduction->vocabularies);
        $this->assertCount(8, $numbers->vocabularies);

        $this->assertEquals(
            20,
            $lessons->sum(
                fn ($lesson) => $lesson->vocabularies->count()
            )
        );

        /*
        |--------------------------------------------------------------------------
        | Exercises
        |--------------------------------------------------------------------------
        */

        foreach ($lessons as $lesson) {
            $this->assertCount(4, $lesson->exercises);

            $this->assertEqualsCanonicalizing(
                [
                    'multiple_choice',
                    'fill_blank',
                    'listening',
                    'image_choice',
                ],
                $lesson->exercises
                    ->pluck('type')
                    ->all()
            );
        }

        $this->assertEquals(
            12,
            $lessons->sum(
                fn ($lesson) => $lesson->exercises->count()
            )
        );

        /*
        |--------------------------------------------------------------------------
        | Questions
        |--------------------------------------------------------------------------
        */

        $questions = $lessons
            ->flatMap(
                fn ($lesson) => $lesson->exercises
            )
            ->flatMap(
                fn ($exercise) => $exercise->questions
            );

        $this->assertCount(12, $questions);

        /*
        |--------------------------------------------------------------------------
        | Answers
        |--------------------------------------------------------------------------
        */

        $answers = $questions
            ->flatMap(
                fn ($question) => $question->answers
            );

        $this->assertCount(48, $answers);

        foreach ($lessons as $lesson) {
            foreach ($lesson->exercises as $exercise) {
                $this->assertCount(
                    1,
                    $exercise->questions
                );

                $question = $exercise->questions->first();

                /*
                 * ทุก Exercise มีตัวเลือก 4 ตัว:
                 *
                 * - multiple_choice
                 * - fill_blank
                 * - listening
                 * - image_choice
                 */
                $this->assertCount(
                    4,
                    $question->answers
                );

                /*
                 * แต่ละข้อมีคำตอบถูกเพียง 1 ตัว
                 */
                $this->assertCount(
                    1,
                    $question->answers
                        ->where('is_correct', true)
                );
            }
        }

        /*
        |--------------------------------------------------------------------------
        | Greetings
        |--------------------------------------------------------------------------
        */

        $this->assertTrue(
            $greetings->vocabularies
                ->contains(
                    fn ($vocabulary) =>
                        $vocabulary->word === '你好' &&
                        $vocabulary->pinyin === 'nǐ hǎo' &&
                        $vocabulary->meaning === 'สวัสดี'
                )
        );

        $greetingsListening = $greetings->exercises
            ->firstWhere('type', 'listening');

        $this->assertNotNull($greetingsListening);

        $greetingsListeningQuestion =
            $greetingsListening->questions->first();

        $this->assertEquals(
            'audio/chinese/greetings/ni-hao.mp3',
            $greetingsListeningQuestion->audio_path
        );

        $this->assertTrue(
            $greetingsListeningQuestion->answers
                ->contains(
                    fn ($answer) =>
                        $answer->answer === '你好' &&
                        (bool) $answer->is_correct === true
                )
        );

        $this->assertCount(
            4,
            $greetingsListeningQuestion->answers
        );

        /*
        |--------------------------------------------------------------------------
        | Self Introduction
        |--------------------------------------------------------------------------
        */

        $this->assertTrue(
            $selfIntroduction->vocabularies
                ->contains(
                    fn ($vocabulary) =>
                        $vocabulary->word === '老师' &&
                        $vocabulary->pinyin === 'lǎoshī' &&
                        $vocabulary->meaning === 'ครู'
                )
        );

        $selfIntroductionListening =
            $selfIntroduction->exercises
                ->firstWhere('type', 'listening');

        $this->assertNotNull(
            $selfIntroductionListening
        );

        $selfIntroductionListeningQuestion =
            $selfIntroductionListening
                ->questions
                ->first();

        $this->assertEquals(
            'audio/chinese/self-introduction/lao-shi.mp3',
            $selfIntroductionListeningQuestion->audio_path
        );

        $this->assertTrue(
            $selfIntroductionListeningQuestion
                ->answers
                ->contains(
                    fn ($answer) =>
                        $answer->answer === '老师' &&
                        (bool) $answer->is_correct === true
                )
        );

        $this->assertCount(
            4,
            $selfIntroductionListeningQuestion->answers
        );

        /*
        |--------------------------------------------------------------------------
        | Numbers
        |--------------------------------------------------------------------------
        */

        $this->assertTrue(
            $numbers->vocabularies
                ->contains(
                    fn ($vocabulary) =>
                        $vocabulary->word === '五' &&
                        $vocabulary->pinyin === 'wǔ' &&
                        $vocabulary->meaning === '5'
                )
        );

        $numbersListening = $numbers->exercises
            ->firstWhere('type', 'listening');

        $this->assertNotNull($numbersListening);

        $numbersListeningQuestion =
            $numbersListening->questions->first();

        $this->assertEquals(
            'audio/chinese/numbers/wu.mp3',
            $numbersListeningQuestion->audio_path
        );

        $this->assertTrue(
            $numbersListeningQuestion->answers
                ->contains(
                    fn ($answer) =>
                        $answer->answer === '五' &&
                        (bool) $answer->is_correct === true
                )
        );

        $this->assertCount(
            4,
            $numbersListeningQuestion->answers
        );
    }

    public function test_chinese_content_seeder_is_idempotent(): void
    {
        /*
         * Seed โครงสร้างและข้อมูลทั้งหมดก่อน 1 รอบ
         */
        $this->seed(DatabaseSeeder::class);

        /*
         * จากนั้นรันเฉพาะ ChineseContentSeeder ซ้ำ
         *
         * ถ้า Seeder เป็น idempotent:
         * จำนวน Vocabulary / Exercise / Question / Answer
         * ต้องไม่เพิ่มขึ้น
         */
        $this->seed(ChineseContentSeeder::class);

        $lessons = Lesson::with([
            'vocabularies',
            'exercises.questions.answers',
        ])
            ->whereHas(
                'unit.course.language',
                fn ($query) => $query->where('name', 'Chinese')
            )
            ->get();

        $this->assertCount(3, $lessons);

        /*
        |--------------------------------------------------------------------------
        | Vocabulary
        |--------------------------------------------------------------------------
        */

        $this->assertEquals(
            20,
            $lessons->sum(
                fn ($lesson) => $lesson->vocabularies->count()
            )
        );

        /*
        |--------------------------------------------------------------------------
        | Exercises
        |--------------------------------------------------------------------------
        */

        $this->assertEquals(
            12,
            $lessons->sum(
                fn ($lesson) => $lesson->exercises->count()
            )
        );

        /*
        |--------------------------------------------------------------------------
        | Questions
        |--------------------------------------------------------------------------
        */

        $questions = $lessons
            ->flatMap(
                fn ($lesson) => $lesson->exercises
            )
            ->flatMap(
                fn ($exercise) => $exercise->questions
            );

        $this->assertCount(
            12,
            $questions
        );

        /*
        |--------------------------------------------------------------------------
        | Answers
        |--------------------------------------------------------------------------
        */

        $answers = $questions
            ->flatMap(
                fn ($question) => $question->answers
            );

        $this->assertCount(
            48,
            $answers
        );

        /*
         * ตรวจเพิ่มว่าแต่ละ Question
         * ยังคงมี 4 Answers และมีคำตอบถูก 1 ตัว
         */
        foreach ($questions as $question) {
            $this->assertCount(
                4,
                $question->answers
            );

            $this->assertCount(
                1,
                $question->answers
                    ->where('is_correct', true)
            );
        }
    }
}