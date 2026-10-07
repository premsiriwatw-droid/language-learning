<?php

namespace Tests\Feature;

use App\Models\Exercise;
use App\Models\Question;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\CreatesContentLesson;
use Tests\TestCase;

class QuizTest extends TestCase
{
    use RefreshDatabase;
    use CreatesContentLesson;

    public function test_multiple_choice_quiz_can_be_displayed_and_submitted(): void
    {
        $lesson = $this->createContentLesson();

        $exercise = Exercise::factory()
            ->multipleChoice()
            ->create([
                'lesson_id' => $lesson->id,
                'title' => 'Greetings',
            ]);

        $question = Question::factory()
            ->for($exercise)
            ->create([
                'question' => 'คำทักทายคืออะไร?',
            ]);

        $wrong = $question->answers()->create([
            'answer' => 'ขอบคุณ',
            'is_correct' => false,
        ]);

        $right = $question->answers()->create([
            'answer' => 'สวัสดี',
            'is_correct' => true,
        ]);

        $this->get(route('quiz.show', $exercise))
            ->assertOk()
            ->assertSee('คำทักทายคืออะไร?')
            ->assertSee('สวัสดี');

        $this->post(route('quiz.submit', $exercise), [
            'answers' => [$question->id => $right->id],
        ])
            ->assertOk()
            ->assertSee('✓ ถูกต้อง');

        $this->post(route('quiz.submit', $exercise), [
            'answers' => [$question->id => $wrong->id],
        ])
            ->assertOk()
            ->assertSee('✗ ยังไม่ถูกต้อง');
    }

    public function test_fill_blank_quiz_can_be_submitted(): void
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
                'question' => 'เติมคำ: สวัสดี = ?',
            ]);

        $question->answers()->create([
            'answer' => 'สวัสดี',
            'is_correct' => true,
        ]);

        $this->post(route('quiz.submit', $exercise), [
            'answers' => [$question->id => ' สวัสดี '],
        ])
            ->assertOk()
            ->assertSee('✓ ถูกต้อง');
    }

    public function test_listening_and_image_choice_have_supported_quiz_ui(): void
    {
        $lesson = $this->createContentLesson();

        $listening = Exercise::factory()
            ->listening()
            ->create([
                'lesson_id' => $lesson->id,
            ]);

        Question::factory()
            ->for($listening)
            ->create();

        $this->get(route('quiz.show', $listening))
            ->assertOk()
            ->assertSee('เล่นเสียง');

        $image = Exercise::factory()
            ->imageChoice()
            ->create([
                'lesson_id' => $lesson->id,
            ]);

        Question::factory()
            ->for($image)
            ->create();

        $this->get(route('quiz.show', $image))
            ->assertOk()
            ->assertSee('Image placeholder');
    }
}