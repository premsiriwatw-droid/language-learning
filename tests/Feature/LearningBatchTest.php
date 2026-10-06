<?php

namespace Tests\Feature;

use App\Models\Exercise;
use App\Models\Question;
use App\Models\Vocabulary;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\CreatesContentLesson;
use Tests\TestCase;

class LearningBatchTest extends TestCase
{
    use RefreshDatabase;
    use CreatesContentLesson;

    public function test_reviews_follow_learned_batches_and_wait_for_all_required_words(): void
    {
        $lesson = $this->createContentLesson();

        // ใช้ 5 คำ เพื่อตรวจชุดสุดท้ายที่มีเพียงคำเดียวด้วย
        $words = Vocabulary::factory()
            ->count(5)
            ->create(['lesson_id' => $lesson->id])
            ->sortBy('id')
            ->values();

        $exercise = Exercise::factory()->create([
            'lesson_id' => $lesson->id,
            'type' => 'multiple_choice',
        ]);

        $createQuestion = function (array $requiredIds) use ($exercise) {
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

            $question->vocabularies()->sync($requiredIds);

            return [
                'question' => $question,
                'answer' => $answer->id,
            ];
        };

        // สร้างข้อที่ต้องรอก่อน เพื่อพิสูจน์ว่าไม่ได้เรียงตาม ID อย่างเดียว
        $later = $createQuestion([
            $words[0]->id,
            $words[3]->id,
        ]);

        $early = $createQuestion([
            $words[1]->id,
        ]);

        $last = $createQuestion([
            $words[4]->id,
        ]);

        // ข้อไม่ผูกคำศัพท์ ต้องอยู่ท้ายบท
        $unlinked = $createQuestion([]);

        $expectedFlow = [
            ['vocabulary', $words[0]],
            ['vocabulary', $words[1]],
            ['review', $early],
            ['vocabulary', $words[2]],
            ['vocabulary', $words[3]],
            ['review', $later],
            ['vocabulary', $words[4]],
            ['review', $last],
            ['review', $unlinked],
        ];

        $this->get(route('lessons.learn', $lesson))
            ->assertRedirect();

        // ยังไม่ได้เรียน ห้ามเปิดหรือส่งคำตอบข้อแรกข้ามขั้น
        $firstStepUrl = route('lessons.learn.step', [
            'lesson' => $lesson->id,
            'step' => 1,
        ]);

        $this->get(route('lessons.learn.step', [
            'lesson' => $lesson->id,
            'step' => 3,
        ]))->assertRedirect($firstStepUrl);

        $this->post(route('lessons.learn.submit', [
            'lesson' => $lesson->id,
            'step' => 3,
        ]), [
            'answer' => $early['answer'],
        ])
            ->assertRedirect($firstStepUrl)
            ->assertSessionHas(
                'learning_runtime.guest.' . $lesson->id,
                fn ($runtime) =>
                    $runtime['next_step'] === 1
                    && $runtime['results'] === []
            );

        // เล่นตามลำดับจริงจนจบ
        foreach ($expectedFlow as $index => [$type, $expected]) {
            $step = $index + 1;

            $this->get(route('lessons.learn.step', [
                'lesson' => $lesson->id,
                'step' => $step,
            ]))
                ->assertOk()
                ->assertViewHas('total', 9)
                ->assertViewHas('current', function ($current) use (
                    $type,
                    $expected
                ) {
                    if ($current['type'] !== $type) {
                        return false;
                    }

                    return $type === 'vocabulary'
                        ? $current['vocabulary']->is($expected)
                        : $current['question']->is($expected['question']);
                });

            $payload = $type === 'review'
                ? ['answer' => $expected['answer']]
                : [];

            $this->post(route('lessons.learn.submit', [
                'lesson' => $lesson->id,
                'step' => $step,
            ]), $payload)
                ->assertRedirect()
                ->assertSessionHasNoErrors();
        }

        $this->get(route('lessons.learn.summary', $lesson))
            ->assertOk()
            ->assertViewHas('summary', fn ($summary) =>
                $summary['vocabulary_count'] === 5
                && $summary['question_count'] === 4
                && $summary['correct_count'] === 4
                && $summary['score_percent'] === 100.0
            )
            ->assertSessionHas(
                'learning_runtime.guest.' . $lesson->id,
                fn ($runtime) =>
                    count($runtime['results']) === 4
                    && collect($runtime['results'])->every(
                        fn ($result) => $result['attempts'] === 1
                    )
                    && $runtime['finished_at'] !== null
            );
    }
}