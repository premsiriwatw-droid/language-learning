<?php

namespace Tests\Feature;

use App\Models\Exercise;
use App\Models\LessonProgress;
use App\Models\Question;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\CreatesContentLesson;
use Tests\TestCase;

class LearningResumeTest extends TestCase
{
    use RefreshDatabase;
    use CreatesContentLesson;

    public function test_session_loss_restores_the_current_step_and_first_answers(): void
    {
        config()->set('learning.rewards', [
            'completion_xp' => 20,
            'xp_per_first_correct' => 10,
            'three_star_percent' => 90,
            'two_star_percent' => 70,
            'vocabulary_only_stars' => 1,
        ]);

        $user = User::factory()->create();
        $lesson = $this->createContentLesson();

        $exercise = Exercise::factory()->create([
            'lesson_id' => $lesson->id,
            'type' => 'multiple_choice',
        ]);

        $questions = [];

        for ($index = 0; $index < 2; $index++) {
            $question = Question::factory()
                ->for($exercise)
                ->create();

            $right = $question->answers()->create([
                'answer' => 'คำตอบถูก',
                'is_correct' => true,
            ]);

            $wrong = $question->answers()->create([
                'answer' => 'คำตอบผิด',
                'is_correct' => false,
            ]);

            $questions[] = [
                'question' => $question,
                'right' => $right,
                'wrong' => $wrong,
            ];
        }

        $this->actingAs($user)
            ->get(route('lessons.learn', $lesson))
            ->assertRedirect();

        // ข้อแรกตอบผิด แล้วลองใหม่จนถูก
        foreach (['wrong', 'right'] as $answerType) {
            $this->post(route('lessons.learn.submit', [
                'lesson' => $lesson->id,
                'step' => 1,
            ]), [
                'answer' => $questions[0][$answerType]->id,
            ])
                ->assertRedirect()
                ->assertSessionHasNoErrors();
        }

        $progress = LessonProgress::where('user_id', $user->id)
            ->where('lesson_id', $lesson->id)
            ->firstOrFail();

        $this->assertSame(2, $progress->runtime_state['next_step']);
        $this->assertNull($progress->completed_at);
        $this->assertSame(0, $progress->xp);
        $this->assertSame(0, $progress->stars);

        $firstResult = $progress->runtime_state['results'][
            $questions[0]['question']->id
        ];

        $this->assertFalse($firstResult['first_correct']);
        $this->assertTrue($firstResult['solved']);
        $this->assertSame(2, $firstResult['attempts']);

        // จำลอง session หายทั้งหมด แล้วผู้ใช้คนเดิมกลับมา
        $this->app['session']->flush();
        $this->app['auth']->forgetGuards();

        $runtimeKey = 'learning_runtime.'
            . $user->id
            . '.'
            . $lesson->id;

        $this->assertNull(
            $this->app['session']->get($runtimeKey)
        );

        // เข้า URL เรียนต่อโดยตรง ไม่เรียก URL เริ่มรอบใหม่
        $this->actingAs($user)
            ->get(route('lessons.learn.step', [
                'lesson' => $lesson->id,
                'step' => 2,
            ]))
            ->assertOk()
            ->assertViewHas('current', fn ($current) =>
                $current['question']->is($questions[1]['question'])
            )
            ->assertSessionHas($runtimeKey, function ($runtime) use ($questions) {
                $result = $runtime['results'][
                    $questions[0]['question']->id
                ] ?? [];

                return $runtime['next_step'] === 2
                    && ($result['first_correct'] ?? null) === false
                    && ($result['solved'] ?? false) === true
                    && ($result['attempts'] ?? 0) === 2;
            });

        // เปิด Summary ก่อนตอบข้อสุดท้าย ต้องกลับมาเรียนต่อ
        $this->get(route('lessons.learn.summary', $lesson))
            ->assertRedirect(route('lessons.learn.step', [
                'lesson' => $lesson->id,
                'step' => 2,
            ]));

        $this->post(route('lessons.learn.submit', [
            'lesson' => $lesson->id,
            'step' => 2,
        ]), [
            'answer' => $questions[1]['right']->id,
        ])
            ->assertRedirect()
            ->assertSessionHasNoErrors();

        // คะแนนต้องยังคิดจากคำตอบครั้งแรกก่อน session หาย
        $this->get(route('lessons.learn.summary', $lesson))
            ->assertOk()
            ->assertViewHas('summary', fn ($summary) =>
                $summary['correct_count'] === 1
                && $summary['wrong_count'] === 1
                && $summary['wrong_attempts'] === 1
                && $summary['score_percent'] === 50.0
                && $summary['saved_xp'] === 30
                && $summary['saved_stars'] === 1
            );

        $progress->refresh();

        $this->assertNotNull($progress->completed_at);
        $this->assertSame(30, $progress->xp);
        $this->assertSame(1, $progress->stars);
        $this->assertDatabaseCount('lesson_progress', 1);
    }
}