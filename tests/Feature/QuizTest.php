<?php

namespace Tests\Feature;

use App\Models\Exercise;
use App\Models\Question;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class QuizTest extends TestCase
{
    use RefreshDatabase;

    public function test_multiple_choice_quiz_can_be_displayed_and_submitted(): void
    {
        $exercise = Exercise::factory()->multipleChoice()->create(['title' => 'Greetings']);
        $question = Question::factory()->for($exercise)->create(['question' => '你好 แปลว่าอะไร?']);
        $wrong = $question->answers()->create(['answer' => 'ขอบคุณ', 'is_correct' => false]);
        $right = $question->answers()->create(['answer' => 'สวัสดี', 'is_correct' => true]);

        $this->get(route('quiz.show', $exercise))
            ->assertOk()
            ->assertSee('你好 แปลว่าอะไร?')
            ->assertSee('สวัสดี');

        $this->post(route('quiz.submit', $exercise), [
            'answers' => [$question->id => $right->id],
        ])->assertOk()->assertSee('✓ ถูกต้อง');

        $this->post(route('quiz.submit', $exercise), [
            'answers' => [$question->id => $wrong->id],
        ])->assertOk()->assertSee('✗ ยังไม่ถูกต้อง');
    }

    public function test_fill_blank_quiz_can_be_submitted(): void
    {
        $exercise = Exercise::factory()->fillBlank()->create();
        $question = Question::factory()->for($exercise)->create(['question' => 'เติมคำ: 你好']);
        $question->answers()->create(['answer' => '你好', 'is_correct' => true]);

        $this->post(route('quiz.submit', $exercise), [
            'answers' => [$question->id => ' 你好 '],
        ])->assertOk()->assertSee('✓ ถูกต้อง');
    }

    public function test_listening_and_image_choice_have_supported_quiz_ui_without_media_columns(): void
    {
        $listening = Exercise::factory()->listening()->create();
        Question::factory()->for($listening)->create();
        $this->get(route('quiz.show', $listening))->assertOk()->assertSee('รอ Content/Data owner เพิ่ม audio field ใน Question');

        $image = Exercise::factory()->imageChoice()->create();
        Question::factory()->for($image)->create();
        $this->get(route('quiz.show', $image))->assertOk()->assertSee('รอ Content/Data owner เพิ่ม image field ใน Question');
    }
}
