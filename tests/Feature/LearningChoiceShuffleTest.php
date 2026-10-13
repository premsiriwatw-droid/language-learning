<?php

namespace Tests\Feature;

use App\Models\Exercise;
use App\Models\Question;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\CreatesContentLesson;
use Tests\TestCase;

class LearningChoiceShuffleTest extends TestCase
{
    use RefreshDatabase;
    use CreatesContentLesson;

    public function test_order_is_stable_on_refresh_retry_and_session_loss_but_new_round_has_new_seed(): void
    {
        $user = User::factory()->create();
        $lesson = $this->createContentLesson();
        $exercise = Exercise::factory()->create(['lesson_id' => $lesson->id, 'type' => 'multiple_choice']);
        $question = Question::factory()->for($exercise)->create();
        $right = $question->answers()->create(['answer' => 'ถูก', 'is_correct' => true]);
        $wrong = $question->answers()->create(['answer' => 'ผิด', 'is_correct' => false]);
        $question->answers()->create(['answer' => 'ตัวเลือกที่สาม', 'is_correct' => false]);

        $key = 'learning_runtime.'.$user->id.'.'.$lesson->id;
        $stepUrl = route('lessons.learn.step', ['lesson' => $lesson->id, 'step' => 1]);

        $this->actingAs($user)->get(route('lessons.learn', $lesson))->assertRedirect();
        $seed = session($key)['choice_seed'];

        $response = $this->get($stepUrl)->assertOk();
        $ids = $response->viewData('answerChoices')->modelKeys();
        $this->assertEqualsCanonicalizing($question->answers()->pluck('id')->all(), $ids);

        $this->assertSame($ids, $this->get($stepUrl)->assertOk()->viewData('answerChoices')->modelKeys());

        $this->post(route('lessons.learn.submit', ['lesson' => $lesson->id, 'step' => 1]), [
            'answer' => $wrong->id,
        ])->assertRedirect()->assertSessionHasNoErrors();

        $this->assertSame($ids, $this->get($stepUrl)->assertOk()->viewData('answerChoices')->modelKeys());

        $this->app['session']->flush();
        $this->app['auth']->forgetGuards();
        $this->actingAs($user);

        $this->assertSame($ids, $this->get($stepUrl)->assertOk()->viewData('answerChoices')->modelKeys());
        $this->assertSame($seed, session($key)['choice_seed']);

        $this->post(route('lessons.learn.submit', ['lesson' => $lesson->id, 'step' => 1]), [
            'answer' => $right->id,
        ])->assertRedirect()->assertSessionHasNoErrors();

        $this->get(route('lessons.learn.summary', $lesson))->assertOk()
            ->assertViewHas('summary', fn ($summary) => $summary['score_percent'] === 0.0);

        $this->get(route('lessons.learn', $lesson))->assertRedirect();
        $this->assertNotSame($seed, session($key)['choice_seed']);
        $this->get($stepUrl)->assertOk()->assertViewHas('reviewResult', null);
    }
}
