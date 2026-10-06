<?php

namespace Tests\Feature;

use App\Models\Exercise;
use App\Models\Question;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\CreatesContentLesson;
use Tests\TestCase;

class LearningRuntimeTest extends TestCase
{
    use RefreshDatabase;
    use CreatesContentLesson;

    public function test_learning_flow_plays_every_question_from_every_exercise(): void
    {
        $lesson = $this->createContentLesson();

        $types = [
            'multiple_choice',
            'fill_blank',
            'listening',
            'image_choice',
        ];

        $expectedQuestions = [];

        // แต่ละประเภทมี 2 Exercises แต่ละ Exercise มี 2 Questions
        foreach ($types as $type) {
            for ($exerciseIndex = 0; $exerciseIndex < 2; $exerciseIndex++) {
                $exercise = Exercise::factory()->create([
                    'lesson_id' => $lesson->id,
                    'type' => $type,
                ]);

                for ($questionIndex = 0; $questionIndex < 2; $questionIndex++) {
                    $question = Question::factory()
                        ->for($exercise)
                        ->create();

                    $right = $question->answers()->create([
                        'answer' => 'คำตอบถูก',
                        'is_correct' => true,
                    ]);

                    $question->answers()->create([
                        'answer' => 'คำตอบผิด',
                        'is_correct' => false,
                    ]);

                    $expectedQuestions[] = [
                        'type' => $type,
                        'question' => $question,
                        'right' => $right,
                    ];
                }
            }
        }

        $this->assertCount(16, $expectedQuestions);

        $this->get(route('lessons.learn', $lesson))
            ->assertRedirect(route('lessons.learn.step', [
                'lesson' => $lesson->id,
                'step' => 1,
            ]));

        foreach ($expectedQuestions as $index => $expected) {
            $step = $index + 1;

            $stepUrl = route('lessons.learn.step', [
                'lesson' => $lesson->id,
                'step' => $step,
            ]);

            $this->get($stepUrl)
                ->assertOk()
                ->assertViewIs('frontend.learn-step')
                ->assertViewHas('total', 16)
                ->assertViewHas('current', function ($current) use ($expected) {
                    return $current['type'] === 'review'
                        && $current['exercise_type'] === $expected['type']
                        && $current['question']->is($expected['question']);
                });

            // ตอบทุกข้อจริงตามรูปแบบที่ checker รับ
            $submitted = in_array(
                $expected['type'],
                ['fill_blank', 'listening'],
                true
            )
                ? $expected['right']->answer
                : $expected['right']->id;

            $this->post(route('lessons.learn.submit', [
                'lesson' => $lesson->id,
                'step' => $step,
            ]), [
                'answer' => $submitted,
            ])
                ->assertSessionHasNoErrors()
                ->assertRedirect($stepUrl);

            $this->get($stepUrl)
                ->assertViewHas('reviewResult', 'correct');
        }

        $this->get(route('lessons.learn.step', [
            'lesson' => $lesson->id,
            'step' => 16,
        ]))->assertSessionHas(
            'learning_runtime.guest.' . $lesson->id,
            function ($runtime) {
                if (
                    $runtime['next_step'] !== 17
                    || $runtime['question_count'] !== 16
                    || count($runtime['results']) !== 16
                    || $runtime['finished_at'] === null
                ) {
                    return false;
                }

                foreach ($runtime['results'] as $result) {
                    if (
                        !$result['solved']
                        || $result['first_correct'] !== true
                        || $result['attempts'] !== 1
                        || $result['wrong_attempts'] !== 0
                    ) {
                        return false;
                    }
                }

                return true;
            }
        );
    }

    public function test_wrong_answer_can_be_retried_without_losing_the_first_result(): void
    {
        $lesson = $this->createContentLesson();

        $exercise = Exercise::factory()->create([
            'lesson_id' => $lesson->id,
            'type' => 'multiple_choice',
        ]);

        $question = Question::factory()
            ->for($exercise)
            ->create();

        $wrong = $question->answers()->create([
            'answer' => 'คำตอบผิด',
            'is_correct' => false,
        ]);

        $right = $question->answers()->create([
            'answer' => 'คำตอบถูก',
            'is_correct' => true,
        ]);

        $stepUrl = route('lessons.learn.step', [
            'lesson' => $lesson->id,
            'step' => 1,
        ]);

        $submitUrl = route('lessons.learn.submit', [
            'lesson' => $lesson->id,
            'step' => 1,
        ]);

        $key = 'learning_runtime.guest.' . $lesson->id;

        $this->get(route('lessons.learn', $lesson));

        // ตอบผิดครั้งแรก
        $this->post($submitUrl, [
            'answer' => $wrong->id,
        ])
            ->assertSessionHasNoErrors()
            ->assertRedirect($stepUrl);

        $this->get($stepUrl)
            ->assertOk()
            ->assertViewHas('reviewResult', 'wrong')
            ->assertViewHas('selectedAnswer', $wrong->id)
            ->assertSee('ยังไม่ถูก ลองอีกครั้ง')
            ->assertDontSee('✅ ถูกต้อง!');

        // รีเฟรชแล้ว feedback ต้องยังอยู่
        $this->get($stepUrl)
            ->assertViewHas('reviewResult', 'wrong');

        // ลองใหม่จนตอบถูก
        $this->post($submitUrl, [
            'answer' => $right->id,
        ])
            ->assertSessionHasNoErrors()
            ->assertRedirect($stepUrl);

        $this->get($stepUrl)
            ->assertViewHas('reviewResult', 'correct');

        // ส่งคำตอบเดิมซ้ำ ต้องไม่เพิ่ม attempts
        $this->post($submitUrl, [
            'answer' => $right->id,
        ])->assertRedirect($stepUrl);

        $this->get($stepUrl)->assertSessionHas(
            $key,
            function ($runtime) use ($question) {
                $result = $runtime['results'][$question->id];

                return $result['attempts'] === 2
                    && $result['wrong_attempts'] === 1
                    && $result['first_correct'] === false
                    && $result['solved'] === true
                    && $runtime['next_step'] === 2
                    && $runtime['finished_at'] !== null;
            }
        );
    }

    public function test_skipping_to_a_later_question_does_not_finish_the_lesson(): void
    {
        $lesson = $this->createContentLesson();

        $exercise = Exercise::factory()->create([
            'lesson_id' => $lesson->id,
            'type' => 'multiple_choice',
        ]);

        $questions = Question::factory()
            ->count(2)
            ->for($exercise)
            ->create();

        foreach ($questions as $question) {
            $question->answers()->create([
                'answer' => 'คำตอบถูก',
                'is_correct' => true,
            ]);
        }

        $firstUrl = route('lessons.learn.step', [
            'lesson' => $lesson->id,
            'step' => 1,
        ]);

        $this->get(route('lessons.learn', $lesson));

        // เปิด URL ท้ายบทเอง ต้องกลับมาข้อที่ยังไม่ได้ทำ
        $this->get(route('lessons.learn.step', [
            'lesson' => $lesson->id,
            'step' => 999,
        ]))->assertRedirect($firstUrl);

        $secondAnswer = $questions[1]->answers()->first();

        // ส่งคำตอบข้อ 2 โดยยังไม่ได้ทำข้อ 1
        $this->post(route('lessons.learn.submit', [
            'lesson' => $lesson->id,
            'step' => 2,
        ]), [
            'answer' => $secondAnswer->id,
        ])->assertRedirect($firstUrl);

        $this->get($firstUrl)->assertSessionHas(
            'learning_runtime.guest.' . $lesson->id,
            fn ($runtime) =>
                $runtime['next_step'] === 1
                && $runtime['results'] === []
                && $runtime['finished_at'] === null
        );
    }
}