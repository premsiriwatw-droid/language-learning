<?php

namespace Tests\Feature;

use App\Models\Exercise;
use App\Models\Question;
use App\Models\User;
use App\Models\Vocabulary;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\CreatesContentLesson;
use Tests\TestCase;

class AdminQuestionVocabularyTest extends TestCase
{
    use RefreshDatabase;
    use CreatesContentLesson;

    protected function setUp(): void
    {
        parent::setUp();

        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin);
    }

    public function test_admin_created_question_appears_after_its_vocabulary_batch(): void
    {
        $lesson = $this->createContentLesson();

        $words = Vocabulary::factory()
            ->count(4)
            ->create(['lesson_id' => $lesson->id])
            ->sortBy('id')
            ->values();

        $exercise = Exercise::factory()->create([
            'lesson_id' => $lesson->id,
            'type' => 'multiple_choice',
        ]);

        $this->post(route('content.questions.store', $exercise), [
            'question' => 'คำถามจาก Admin',
            'vocabulary_mode' => 'after_vocabulary',
            'vocabulary_ids' => [$words[1]->id],
        ])
            ->assertRedirect()
            ->assertSessionHasNoErrors();

        $question = $exercise->questions()->sole();

        $this->assertSame('after_vocabulary', $question->vocabulary_mode);

        $this->assertSame(
            [$words[1]->id],
            $question->vocabularies()->pluck('vocabularies.id')->all()
        );

        $question->answers()->create([
            'answer' => 'คำตอบถูก',
            'is_correct' => true,
        ]);

        $this->get(route('lessons.learn', $lesson))
            ->assertRedirect();

        foreach ([1, 2] as $step) {
            $this->get(route('lessons.learn.step', [
                'lesson' => $lesson->id,
                'step' => $step,
            ]))
                ->assertOk()
                ->assertViewHas('current', fn ($current) =>
                    $current['type'] === 'vocabulary'
                    && $current['vocabulary']->is($words[$step - 1])
                );

            $this->post(route('lessons.learn.submit', [
                'lesson' => $lesson->id,
                'step' => $step,
            ]))->assertRedirect();
        }

        // ต้องพบคำถามหลังศัพท์สองคำแรก ไม่ไปรวมท้ายบท
        $this->get(route('lessons.learn.step', [
            'lesson' => $lesson->id,
            'step' => 3,
        ]))
            ->assertOk()
            ->assertViewHas('total', 5)
            ->assertViewHas('current', fn ($current) =>
                $current['type'] === 'review'
                && $current['question']->is($question)
            );
    }

    public function test_admin_can_replace_mapping_and_move_question_to_lesson_end(): void
    {
        $lesson = $this->createContentLesson();

        $words = Vocabulary::factory()
            ->count(2)
            ->create(['lesson_id' => $lesson->id])
            ->values();

        $exercise = Exercise::factory()->create([
            'lesson_id' => $lesson->id,
            'type' => 'multiple_choice',
        ]);

        $question = Question::factory()->for($exercise)->create([
            'vocabulary_mode' => 'after_vocabulary',
        ]);

        $question->vocabularies()->sync([$words[0]->id]);

        $this->put(route('content.questions.update', $question), [
            'question' => 'แก้ไขคำถาม',
            'vocabulary_mode' => 'after_vocabulary',
            'vocabulary_ids' => [$words[1]->id],
        ])
            ->assertRedirect()
            ->assertSessionHasNoErrors();

        $this->assertDatabaseMissing('question_vocabulary', [
            'question_id' => $question->id,
            'vocabulary_id' => $words[0]->id,
        ]);

        $this->assertDatabaseHas('question_vocabulary', [
            'question_id' => $question->id,
            'vocabulary_id' => $words[1]->id,
        ]);

        $this->put(route('content.questions.update', $question), [
            'question' => 'ทบทวนท้ายบท',
            'vocabulary_mode' => 'lesson_end',
        ])
            ->assertRedirect()
            ->assertSessionHasNoErrors();

        $question->refresh();

        $this->assertSame('lesson_end', $question->vocabulary_mode);
        $this->assertCount(0, $question->vocabularies);
    }

    public function test_cross_lesson_mapping_is_rejected_without_changing_question(): void
    {
        $lesson = $this->createContentLesson();
        $otherLesson = $this->createContentLesson();

        $word = Vocabulary::factory()->create([
            'lesson_id' => $lesson->id,
        ]);

        $otherWord = Vocabulary::factory()->create([
            'lesson_id' => $otherLesson->id,
        ]);

        $exercise = Exercise::factory()->create([
            'lesson_id' => $lesson->id,
            'type' => 'multiple_choice',
        ]);

        $this->post(route('content.questions.store', $exercise), [
            'question' => 'ต้องไม่สร้าง',
            'vocabulary_mode' => 'after_vocabulary',
            'vocabulary_ids' => [$otherWord->id],
        ])
            ->assertRedirect()
            ->assertSessionHasErrors('vocabulary_ids.0');

        $this->assertSame(0, $exercise->questions()->count());

        $question = Question::factory()->for($exercise)->create([
            'question' => 'ข้อความเดิม',
            'vocabulary_mode' => 'after_vocabulary',
        ]);

        $question->vocabularies()->sync([$word->id]);

        $this->put(route('content.questions.update', $question), [
            'question' => 'ต้องไม่เปลี่ยนข้อความ',
            'vocabulary_mode' => 'after_vocabulary',
            'vocabulary_ids' => [$otherWord->id],
        ])
            ->assertRedirect()
            ->assertSessionHasErrors('vocabulary_ids.0');

        $question->refresh();

        $this->assertSame('ข้อความเดิม', $question->question);

        $this->assertSame(
            [$word->id],
            $question->vocabularies()->pluck('vocabularies.id')->all()
        );
    }

    public function test_mapping_requires_an_explicit_mode_and_valid_word_selection(): void
    {
        $lesson = $this->createContentLesson();

        $word = Vocabulary::factory()->create([
            'lesson_id' => $lesson->id,
        ]);

        $exercise = Exercise::factory()->create([
            'lesson_id' => $lesson->id,
            'type' => 'multiple_choice',
        ]);

        $this->post(route('content.questions.store', $exercise), [
            'question' => 'ไม่มีรูปแบบ',
        ])
            ->assertRedirect()
            ->assertSessionHasErrors('vocabulary_mode');

        $this->post(route('content.questions.store', $exercise), [
            'question' => 'ไม่มีคำศัพท์',
            'vocabulary_mode' => 'after_vocabulary',
            'vocabulary_ids' => [],
        ])
            ->assertRedirect()
            ->assertSessionHasErrors('vocabulary_ids');

        $this->post(route('content.questions.store', $exercise), [
            'question' => 'ท้ายบทแต่ส่งศัพท์มาด้วย',
            'vocabulary_mode' => 'lesson_end',
            'vocabulary_ids' => [$word->id],
        ])
            ->assertRedirect()
            ->assertSessionHasErrors('vocabulary_ids');

        $this->assertSame(0, $exercise->questions()->count());
    }
}