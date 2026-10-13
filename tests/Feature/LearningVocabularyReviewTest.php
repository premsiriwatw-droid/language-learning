<?php

namespace Tests\Feature;

use App\Models\Exercise;
use App\Models\Question;
use App\Models\Vocabulary;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\CreatesContentLesson;
use Tests\TestCase;

class LearningVocabularyReviewTest extends TestCase
{
    use RefreshDatabase;
    use CreatesContentLesson;

    public function test_review_only_exposes_completed_vocabulary_and_keeps_answer_results(): void
    {
        config()->set('learning.vocabulary_batch_size', 2);
        config()->set('learning.group_related_vocabulary', false);
        $lesson = $this->createContentLesson();
        $words = Vocabulary::factory()->count(3)->create(['lesson_id' => $lesson->id])->sortBy('id')->values();
        $exercise = Exercise::factory()->create(['lesson_id' => $lesson->id, 'type' => 'multiple_choice']);
        $question = Question::factory()->for($exercise)->create();
        $question->vocabularies()->sync([$words[0]->id]);
        $right = $question->answers()->create(['answer' => 'Right', 'is_correct' => true]);
        $wrong = $question->answers()->create(['answer' => 'Wrong', 'is_correct' => false]);

        $this->get(route('lessons.learn', $lesson))->assertRedirect();
        $this->get(route('lessons.learn.step', ['lesson' => $lesson->id, 'step' => 1]))
            ->assertOk()->assertViewHas('learnedVocabularies', fn ($items) => $items->isEmpty());
        foreach ([1, 2] as $step) {
            $this->post(route('lessons.learn.submit', ['lesson' => $lesson->id, 'step' => $step]))->assertRedirect();
        }

        $url = route('lessons.learn.step', ['lesson' => $lesson->id, 'step' => 3]);
        $submit = route('lessons.learn.submit', ['lesson' => $lesson->id, 'step' => 3]);
        $checkWords = fn ($items) => $items->pluck('id')->all() === [$words[0]->id, $words[1]->id];
        $this->get($url)->assertOk()->assertViewHas('learnedVocabularies', $checkWords)
            ->assertDontSee('← ย้อนกลับ');

        $key = 'learning_runtime.guest.'.$lesson->id;
        $this->post($submit, ['answer' => $wrong->id])->assertRedirect();
        $before = session($key);
        $this->get($url)->assertOk()->assertViewHas('reviewResult', 'wrong')
            ->assertSee('← ย้อนกลับ')
            ->assertViewHas('learnedVocabularies', $checkWords)->assertSessionHas($key, $before);

        $this->post($submit, ['answer' => $right->id])->assertRedirect();
        $before = session($key);
        $this->assertFalse($before['results'][$question->id]['first_correct']);
        $this->assertSame(2, $before['results'][$question->id]['attempts']);
        $this->get(route('lessons.learn.step', ['lesson' => $lesson->id, 'step' => 2]))
            ->assertOk()->assertViewHas('isCompletedStep', true)
            ->assertSessionHas($key, $before);
        $this->get($url)->assertOk()->assertViewHas('reviewResult', 'correct')
            ->assertSee('ผ่านแล้ว — ไม่ต้องทำข้อนี้ซ้ำ')
            ->assertSee('← ย้อนกลับ')
            ->assertViewHas('learnedVocabularies', $checkWords)->assertSessionHas($key, $before);
        $this->assertDatabaseCount('lesson_progress', 0);
    }
}
