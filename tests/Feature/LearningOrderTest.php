<?php

namespace Tests\Feature;

use App\Models\Exercise;
use App\Models\Question;
use App\Models\Vocabulary;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\CreatesContentLesson;
use Tests\TestCase;

class LearningOrderTest extends TestCase
{
    use RefreshDatabase;
    use CreatesContentLesson;

    public function test_every_vocabulary_is_learned_before_all_question_types(): void
    {
        $lesson = $this->createContentLesson();

        // ตรวจว่าไม่มีคำถามแทรกหลังคำศัพท์คำที่ 2 หรือ 4
        $vocabularies = Vocabulary::factory()
            ->count(6)
            ->create(['lesson_id' => $lesson->id])
            ->sortBy('id')
            ->values();

        $types = [
            'multiple_choice',
            'fill_blank',
            'listening',
            'image_choice',
        ];

        $questionsByType = [];

        // สร้างย้อนลำดับ เพื่อตรวจการเรียงตามประเภท
        foreach (array_reverse($types) as $type) {
            $exercise = Exercise::factory()->create([
                'lesson_id' => $lesson->id,
                'type' => $type,
            ]);

            for ($index = 0; $index < 2; $index++) {
                $question = Question::factory()
                    ->for($exercise)
                    ->create();

                $answer = $question->answers()->create([
                    'answer' => 'คำตอบถูก',
                    'is_correct' => true,
                ]);

                $question->answers()->create([
                    'answer' => 'คำตอบผิด',
                    'is_correct' => false,
                ]);

                $questionsByType[$type][] = [
                    'question' => $question,
                    'answer' => $answer,
                ];
            }
        }

        $this->get(route('lessons.learn', $lesson))
            ->assertRedirect();

        // ขั้นที่ 1–6 ต้องเป็นคำศัพท์ทั้งหมด
        foreach ($vocabularies as $index => $vocabulary) {
            $step = $index + 1;

            $this->get(route('lessons.learn.step', [
                'lesson' => $lesson->id,
                'step' => $step,
            ]))
                ->assertOk()
                ->assertViewHas('total', 14)
                ->assertViewHas('current', fn ($current) =>
                    $current['type'] === 'vocabulary'
                    && $current['vocabulary']->is($vocabulary)
                );

            $this->post(route('lessons.learn.submit', [
                'lesson' => $lesson->id,
                'step' => $step,
            ]))->assertRedirect(route('lessons.learn.step', [
                'lesson' => $lesson->id,
                'step' => $step + 1,
            ]));
        }

        // เรียนศัพท์ครบแล้ว แต่ยังไม่ได้ตอบคำถามหรือจบบท
        $this->get(route('lessons.learn.step', [
            'lesson' => $lesson->id,
            'step' => 7,
        ]))
            ->assertOk()
            ->assertSessionHas(
                'learning_runtime.guest.' . $lesson->id,
                fn ($runtime) =>
                    $runtime['next_step'] === 7
                    && $runtime['results'] === []
                    && $runtime['finished_at'] === null
            );

        // ขั้นที่ 7–14 ต้องเป็นคำถามครบทุกประเภท
        $step = 7;

        foreach ($types as $type) {
            foreach ($questionsByType[$type] as $expected) {
                $this->get(route('lessons.learn.step', [
                    'lesson' => $lesson->id,
                    'step' => $step,
                ]))
                    ->assertOk()
                    ->assertViewHas('current', fn ($current) =>
                        $current['type'] === 'review'
                        && $current['exercise_type'] === $type
                        && $current['question']->is($expected['question'])
                    );

                $answer = in_array(
                    $type,
                    ['multiple_choice', 'image_choice'],
                    true
                )
                    ? $expected['answer']->id
                    : $expected['answer']->answer;

                $this->post(route('lessons.learn.submit', [
                    'lesson' => $lesson->id,
                    'step' => $step,
                ]), [
                    'answer' => $answer,
                ])
                    ->assertRedirect()
                    ->assertSessionHasNoErrors();

                $step++;
            }
        }

        $this->get(route('lessons.learn.summary', $lesson))
            ->assertOk()
            ->assertViewHas('summary', fn ($summary) =>
                $summary['vocabulary_count'] === 6
                && $summary['question_count'] === 8
                && $summary['correct_count'] === 8
                && $summary['score_percent'] === 100.0
            );
    }
}