<?php

namespace Tests\Feature;

use App\Models\Exercise;
use App\Models\Lesson;
use App\Models\Question;
use App\Models\User;
use App\Models\Vocabulary;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\CreatesContentLesson;
use Tests\TestCase;

class LearningSummaryTest extends TestCase
{
    use RefreshDatabase;
    use CreatesContentLesson;

    public function test_summary_uses_first_answers_and_keeps_the_finished_time(): void
    {
        $this->freezeTime();

        $lesson = $this->createContentLesson();

        Vocabulary::factory()->create([
            'lesson_id' => $lesson->id,
        ]);

        $exercise = Exercise::factory()->create([
            'lesson_id' => $lesson->id,
            'type' => 'multiple_choice',
        ]);

        $answers = [];

        for ($index = 0; $index < 2; $index++) {
            $question = Question::factory()
                ->for($exercise)
                ->create();

            $answers[] = [
                'right' => $question->answers()->create([
                    'answer' => 'คำตอบถูก',
                    'is_correct' => true,
                ]),
                'wrong' => $question->answers()->create([
                    'answer' => 'คำตอบผิด',
                    'is_correct' => false,
                ]),
            ];
        }

        $nextLesson = Lesson::forceCreate([
            'unit_id' => $lesson->unit_id,
            'title' => 'บทถัดไป',
        ]);

        $this->get(route('lessons.learn', $lesson));

        // ยืนยันคำศัพท์ step แรก
        $this->post(route('lessons.learn.submit', [
            'lesson' => $lesson->id,
            'step' => 1,
        ]))->assertRedirect(route('lessons.learn.step', [
            'lesson' => $lesson->id,
            'step' => 2,
        ]));

        $this->travel(90)->seconds();

        foreach ($answers as $index => $answer) {
            $step = $index + 2;

            $submitUrl = route('lessons.learn.submit', [
                'lesson' => $lesson->id,
                'step' => $step,
            ]);

            // ข้อที่สองตอบผิดก่อน แล้วลองใหม่จนถูก
            if ($index === 1) {
                $this->post($submitUrl, [
                    'answer' => $answer['wrong']->id,
                ])->assertSessionHasNoErrors();
            }

            $this->post($submitUrl, [
                'answer' => $answer['right']->id,
            ])->assertSessionHasNoErrors();
        }

        $summaryUrl = route('lessons.learn.summary', [
            'lesson' => $lesson->id,
        ]);

        $this->get(route('lessons.learn.step', [
            'lesson' => $lesson->id,
            'step' => 4,
        ]))->assertRedirect($summaryUrl);

        $this->get($summaryUrl)
            ->assertOk()
            ->assertViewIs('frontend.lesson-summary')
            ->assertViewHas('summary', fn ($summary) =>
                $summary['vocabulary_count'] === 1
                && $summary['question_count'] === 2
                && $summary['correct_count'] === 1
                && $summary['wrong_count'] === 1
                && $summary['wrong_attempts'] === 1
                && $summary['score_percent'] === 50.0
                && $summary['elapsed_seconds'] === 90
            )
            ->assertViewHas('nextLesson', fn ($next) =>
                $next->is($nextLesson)
            )
            ->assertSee('50%');

        // รีเฟรช Summary แล้วเวลาต้องไม่เพิ่ม
        $this->travel(20)->seconds();

        $this->get($summaryUrl)
            ->assertViewHas('summary', fn ($summary) =>
                $summary['elapsed_seconds'] === 90
            );

        // เรียนใหม่ต้องล้างผลรอบเดิม
        $this->get(route('lessons.learn', $lesson));

        $this->get($summaryUrl)
            ->assertRedirect(route('lessons.learn.step', [
                'lesson' => $lesson->id,
                'step' => 1,
            ]))
            ->assertSessionHas(
                'learning_runtime.guest.' . $lesson->id,
                fn ($runtime) =>
                    $runtime['results'] === []
                    && $runtime['finished_at'] === null
                    && $runtime['next_step'] === 1
            );
    }

    public function test_summary_cannot_be_opened_before_finishing(): void
    {
        $user = User::factory()->create();
        $lesson = $this->createContentLesson();

        Vocabulary::factory()->create([
            'lesson_id' => $lesson->id,
        ]);

        $firstUrl = route('lessons.learn.step', [
            'lesson' => $lesson->id,
            'step' => 1,
        ]);

        $this->actingAs($user)
            ->get(route('lessons.learn.summary', [
                'lesson' => $lesson->id,
            ]))
            ->assertRedirect($firstUrl);

        $this->get(route('lessons.learn.step', [
            'lesson' => $lesson->id,
            'step' => 999,
        ]))->assertRedirect($firstUrl);

        $this->assertDatabaseCount('lesson_progress', 0);
    }

    public function test_vocabulary_only_lesson_has_no_exam_percentage(): void
    {
        $lesson = $this->createContentLesson();

        Vocabulary::factory()->create([
            'lesson_id' => $lesson->id,
        ]);

        $this->get(route('lessons.learn', $lesson));

        $this->post(route('lessons.learn.submit', [
            'lesson' => $lesson->id,
            'step' => 1,
        ]));

        $this->get(route('lessons.learn.summary', [
            'lesson' => $lesson->id,
        ]))
            ->assertOk()
            ->assertViewHas('summary', fn ($summary) =>
                $summary['vocabulary_count'] === 1
                && $summary['question_count'] === 0
                && $summary['correct_count'] === 0
                && $summary['wrong_count'] === 0
                && $summary['score_percent'] === null
            );
    }
}