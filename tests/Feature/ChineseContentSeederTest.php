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

        $this->assertCount(12, $greetings->vocabularies);
        $this->assertCount(12, $selfIntroduction->vocabularies);
        $this->assertCount(15, $numbers->vocabularies);

        $this->assertEquals(
            39,
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

        $this->assertCount(30, $questions);

        foreach ($lessons as $lesson) {
            $lessonQuestions = $lesson->exercises
                ->flatMap(
                    fn ($exercise) => $exercise->questions
                );

            $this->assertCount(10, $lessonQuestions);

            $multipleChoice = $lesson->exercises
                ->firstWhere('type', 'multiple_choice');

            $fillBlank = $lesson->exercises
                ->firstWhere('type', 'fill_blank');

            $listening = $lesson->exercises
                ->firstWhere('type', 'listening');

            $imageChoice = $lesson->exercises
                ->firstWhere('type', 'image_choice');

            $this->assertNotNull($multipleChoice);
            $this->assertNotNull($fillBlank);
            $this->assertNotNull($listening);
            $this->assertNotNull($imageChoice);

            $this->assertCount(4, $multipleChoice->questions);
            $this->assertCount(4, $fillBlank->questions);
            $this->assertCount(1, $listening->questions);
            $this->assertCount(1, $imageChoice->questions);
        }

        /*
        |--------------------------------------------------------------------------
        | Answers
        |--------------------------------------------------------------------------
        */

        $answers = $questions
            ->flatMap(
                fn ($question) => $question->answers
            );

        $this->assertCount(120, $answers);

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

        $this->assertTrue(
            $greetings->vocabularies
                ->contains(
                    fn ($vocabulary) =>
                        $vocabulary->word === '晚安' &&
                        $vocabulary->pinyin === 'wǎn\'ān' &&
                        $vocabulary->meaning === 'ราตรีสวัสดิ์'
                )
        );

        $this->assertTrue(
            $greetings->vocabularies
                ->contains(
                    fn ($vocabulary) =>
                        $vocabulary->word === '欢迎' &&
                        $vocabulary->pinyin === 'huānyíng' &&
                        $vocabulary->meaning === 'ยินดีต้อนรับ'
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

        $greetingsImageChoice = $greetings->exercises
            ->firstWhere('type', 'image_choice');

        $this->assertNotNull($greetingsImageChoice);

        $this->assertEquals(
            'images/chinese/greetings/goodbye.jpg',
            $greetingsImageChoice->questions->first()->image_path
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

        $this->assertTrue(
            $selfIntroduction->vocabularies
                ->contains(
                    fn ($vocabulary) =>
                        $vocabulary->word === '名字' &&
                        $vocabulary->pinyin === 'míngzi' &&
                        $vocabulary->meaning === 'ชื่อ'
                )
        );

        $this->assertTrue(
            $selfIntroduction->vocabularies
                ->contains(
                    fn ($vocabulary) =>
                        $vocabulary->word === '泰国' &&
                        $vocabulary->pinyin === 'Tàiguó' &&
                        $vocabulary->meaning === 'ประเทศไทย'
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

        $selfIntroductionImageChoice =
            $selfIntroduction->exercises
                ->firstWhere('type', 'image_choice');

        $this->assertNotNull($selfIntroductionImageChoice);

        $this->assertEquals(
            'images/chinese/self-introduction/teacher.jpg',
            $selfIntroductionImageChoice->questions->first()->image_path
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

        $this->assertTrue(
            $numbers->vocabularies
                ->contains(
                    fn ($vocabulary) =>
                        $vocabulary->word === '十' &&
                        $vocabulary->pinyin === 'shí' &&
                        $vocabulary->meaning === '10'
                )
        );

        $this->assertTrue(
            $numbers->vocabularies
                ->contains(
                    fn ($vocabulary) =>
                        $vocabulary->word === '两' &&
                        $vocabulary->pinyin === 'liǎng' &&
                        $vocabulary->meaning === 'สอง (ใช้หน้าลักษณนาม)'
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

        $numbersImageChoice = $numbers->exercises
            ->firstWhere('type', 'image_choice');

        $this->assertNotNull($numbersImageChoice);

        $this->assertEquals(
            'images/chinese/numbers/three-apples.jpg',
            $numbersImageChoice->questions->first()->image_path
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
            39,
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
            30,
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
            120,
            $answers
        );

        /*
         * ตรวจว่าแต่ละ Lesson ยังมี Question 10 ข้อ
         * และแต่ละ Question มี 4 Answers พร้อมคำตอบถูกเพียง 1 ตัว
         */
        foreach ($lessons as $lesson) {
            $lessonQuestions = $lesson->exercises
                ->flatMap(
                    fn ($exercise) => $exercise->questions
                );

            $this->assertCount(
                10,
                $lessonQuestions
            );
        }

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