<?php

namespace Tests\Feature;

use App\Models\Exercise;
use App\Models\LearningAttempt;
use App\Models\Question;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\CreatesContentLesson;
use Tests\TestCase;

class LearningHistoryPagesTest extends TestCase
{
    use RefreshDatabase;
    use CreatesContentLesson;

    private function fixture(): array
    {
        $user = User::factory()->create();
        $lesson = $this->createContentLesson();
        $exercise = Exercise::factory()->create(['lesson_id' => $lesson->id, 'type' => 'multiple_choice']);
        $question = Question::factory()->for($exercise)->create();
        $right = $question->answers()->create(['answer' => 'Right', 'is_correct' => true]);
        $wrong = $question->answers()->create(['answer' => 'Wrong', 'is_correct' => false]);
        $attempt = LearningAttempt::create([
            'user_id' => $user->id, 'lesson_id' => $lesson->id,
            'run_key' => bin2hex(random_bytes(32)), 'lesson_title' => $lesson->title,
            'language_name' => 'English', 'vocabulary_count' => 0, 'question_count' => 1,
            'correct_count' => 0, 'wrong_count' => 1, 'wrong_attempts' => 1,
            'score_percent' => 0, 'elapsed_seconds' => 30,
            'started_at' => now()->subSeconds(30), 'completed_at' => now(),
            'results' => [[
                'question_id' => $question->id, 'question' => $question->question,
                'exercise_type' => 'multiple_choice', 'first_correct' => false,
                'attempts' => 2, 'wrong_attempts' => 1, 'first_answer' => 'Wrong',
                'last_answer' => 'Right', 'vocabulary' => [],
            ]],
        ]);
        return [$user, $attempt, $question, $right, $wrong];
    }

    public function test_history_and_practice_require_login(): void
    {
        [$user, $attempt] = $this->fixture();
        foreach (['learning-history.index', 'learning-history.show', 'learning-history.review'] as $route) {
            $this->get(route($route, $route === 'learning-history.index' ? [] : $attempt))->assertRedirect(route('login'));
        }
    }

    public function test_users_can_only_view_and_practice_their_own_history(): void
    {
        [$user, $attempt, $question] = $this->fixture();
        $this->actingAs(User::factory()->create());
        $this->get(route('learning-history.index'))->assertOk()
            ->assertViewHas('attempts', fn ($items) => $items->isEmpty());
        $this->get(route('learning-history.show', $attempt))->assertNotFound();
        $this->get(route('learning-history.review', $attempt))->assertNotFound();
        $this->post(route('learning-history.review.submit', $attempt), ['question_id' => $question->id, 'action' => 'next'])->assertNotFound();
    }

    public function test_wrong_answer_can_be_retried_without_changing_history_or_awarding_rewards(): void
    {
        [$user, $attempt, $question, $right, $wrong] = $this->fixture();
        $original = $attempt->getAttributes();
        $this->actingAs($user);
        $this->get(route('learning-history.show', $attempt))->assertOk()->assertSee('Wrong');
        $url = route('learning-history.review', $attempt);
        $submit = route('learning-history.review.submit', $attempt);
        $this->get($url)->assertOk()->assertViewHas('question', fn ($item) => $item->is($question));
        $this->post($submit, ['question_id' => $question->id, 'action' => 'next'])->assertRedirect($url);
        $this->get($url)->assertViewHas('question', fn ($item) => $item->is($question));
        $this->post($submit, ['question_id' => $question->id, 'action' => 'answer', 'answer' => $wrong->id])->assertRedirect($url);
        $this->get($url)->assertOk()->assertViewHas('result', fn ($result) => $result['solved'] === false);
        $this->post($submit, ['question_id' => $question->id, 'action' => 'answer', 'answer' => $right->id])->assertRedirect($url);
        $this->get($url)->assertOk()->assertViewHas('result', fn ($result) => $result['solved'] === true);
        $this->post($submit, ['question_id' => $question->id, 'action' => 'next'])->assertRedirect($url);
        $this->get($url)->assertOk()->assertViewHas('question', null)->assertSee('ทบทวนครบแล้ว');
        $after = $attempt->fresh()->getAttributes();
        ksort($original);
        ksort($after);
        $this->assertSame($original, $after);
        $this->assertDatabaseCount('learning_attempts', 1);
        $this->assertDatabaseCount('lesson_progress', 0);
    }

    public function test_deleted_question_keeps_history_but_is_skipped_in_practice(): void
    {
        [$user, $attempt, $question] = $this->fixture();
        $question->answers()->delete();
        $question->delete();
        $this->actingAs($user)->get(route('learning-history.show', $attempt))->assertOk()->assertSee('Wrong');
        $this->get(route('learning-history.review', $attempt))->assertOk()
            ->assertViewHas('question', null)->assertViewHas('skippedCount', 1);
    }

    public function test_correct_first_answers_are_excluded_and_foreign_options_are_rejected(): void
    {
        [$user, $attempt, $question, $right] = $this->fixture();
        $second = Question::factory()->for($question->exercise)->create();
        $foreign = $second->answers()->create(['answer' => 'Foreign', 'is_correct' => true]);
        $results = $attempt->results;
        $results[] = array_replace($results[0], ['question_id' => $second->id, 'first_correct' => true]);
        $attempt->update(['results' => $results]);
        $this->actingAs($user)->get(route('learning-history.review', $attempt))
            ->assertOk()->assertViewHas('total', 1);
        $this->post(route('learning-history.review.submit', $attempt), [
            'question_id' => $question->id, 'action' => 'answer', 'answer' => $foreign->id,
        ])->assertRedirect()->assertSessionHasErrors('answer');
        $this->assertDatabaseCount('lesson_progress', 0);
    }
}
