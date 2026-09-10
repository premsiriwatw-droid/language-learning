<?php

namespace Tests\Unit\Quiz;

use App\Models\Exercise;
use App\Models\Question;
use App\Services\Quiz\QuizAnswerChecker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\CreatesContentLesson;
use Tests\TestCase;

class QuizAnswerCheckerTest extends TestCase
{
    use RefreshDatabase;
    use CreatesContentLesson;

    public function test_fill_blank_accepts_the_correct_answer_and_rejects_wrong_answer(): void
    {
        $lesson = $this->createContentLesson();

        $exercise = Exercise::factory()
            ->fillBlank()
            ->create([
                'lesson_id' => $lesson->id,
            ]);

        $question = Question::factory()
            ->for($exercise)
            ->create([
                'question' => 'เติมคำ',
            ]);

        $question->answers()->create([
            'answer' => 'สวัสดี',
            'is_correct' => true,
        ]);

        $checker = app(QuizAnswerChecker::class);

        $this->assertTrue(
            $checker->check(
                $question->fresh('answers', 'exercise'),
                '  สวัสดี  '
            )
        );

        $this->assertFalse(
            $checker->check(
                $question->fresh('answers', 'exercise'),
                'ขอบคุณ'
            )
        );
    }

    public function test_listening_uses_the_same_text_answer_logic_without_requiring_media_schema(): void
    {
        $lesson = $this->createContentLesson();

        $exercise = Exercise::factory()
            ->listening()
            ->create([
                'lesson_id' => $lesson->id,
            ]);

        $question = Question::factory()
            ->for($exercise)
            ->create([
                'question' => 'ฟังเสียงแล้วพิมพ์คำตอบ',
            ]);

        $question->answers()->create([
            'answer' => '你好',
            'is_correct' => true,
        ]);

        $checker = app(QuizAnswerChecker::class);

        $this->assertTrue(
            $checker->check(
                $question->fresh('answers', 'exercise'),
                '你好'
            )
        );

        $this->assertFalse(
            $checker->check(
                $question->fresh('answers', 'exercise'),
                '谢谢'
            )
        );
    }

    public function test_multiple_choice_checks_answer_is_correct(): void
    {
        $lesson = $this->createContentLesson();

        $exercise = Exercise::factory()
            ->multipleChoice()
            ->create([
                'lesson_id' => $lesson->id,
            ]);

        $question = Question::factory()
            ->for($exercise)
            ->create();

        $wrong = $question->answers()->create([
            'answer' => 'ขอบคุณ',
            'is_correct' => false,
        ]);

        $right = $question->answers()->create([
            'answer' => 'สวัสดี',
            'is_correct' => true,
        ]);

        $checker = app(QuizAnswerChecker::class);

        $this->assertTrue(
            $checker->check(
                $question->fresh('answers', 'exercise'),
                $right->id
            )
        );

        $this->assertFalse(
            $checker->check(
                $question->fresh('answers', 'exercise'),
                $wrong->id
            )
        );
    }

    public function test_image_choice_checks_answer_is_correct(): void
    {
        $lesson = $this->createContentLesson();

        $exercise = Exercise::factory()
            ->imageChoice()
            ->create([
                'lesson_id' => $lesson->id,
            ]);

        $question = Question::factory()
            ->for($exercise)
            ->create();

        $wrong = $question->answers()->create([
            'answer' => 'แมว',
            'is_correct' => false,
        ]);

        $right = $question->answers()->create([
            'answer' => 'สุนัข',
            'is_correct' => true,
        ]);

        $checker = app(QuizAnswerChecker::class);

        $this->assertTrue(
            $checker->check(
                $question->fresh('answers', 'exercise'),
                $right->id
            )
        );

        $this->assertFalse(
            $checker->check(
                $question->fresh('answers', 'exercise'),
                $wrong->id
            )
        );
    }

    public function test_choice_from_another_question_is_not_accepted(): void
    {
        $lesson = $this->createContentLesson();

        $exercise = Exercise::factory()
            ->multipleChoice()
            ->create([
                'lesson_id' => $lesson->id,
            ]);

        $question = Question::factory()
            ->for($exercise)
            ->create();

        $otherQuestion = Question::factory()
            ->for($exercise)
            ->create();

        $foreignAnswer = $otherQuestion->answers()->create([
            'answer' => 'สวัสดี',
            'is_correct' => true,
        ]);

        $checker = app(QuizAnswerChecker::class);

        $this->assertFalse(
            $checker->check(
                $question->fresh('answers', 'exercise'),
                $foreignAnswer->id
            )
        );
    }
}
