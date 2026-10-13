<?php

namespace Tests\Feature;

use App\Models\Exercise;
use App\Models\LearningAttempt;
use App\Models\LessonProgress;
use App\Models\Question;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\CreatesContentLesson;
use Tests\TestCase;

class LearningHistoryTest extends TestCase
{
    use RefreshDatabase;
    use CreatesContentLesson;

    private function fixture(): array
    {
        $lesson = $this->createContentLesson();
        $exercise = Exercise::factory()->create(['lesson_id' => $lesson->id, 'type' => 'multiple_choice']);
        $answers = [];
        for ($i = 0; $i < 2; $i++) {
            $question = Question::factory()->for($exercise)->create();
            $answers[] = [
                'right' => $question->answers()->create(['answer' => 'Right '.$i, 'is_correct' => true])->id,
                'wrong' => $question->answers()->create(['answer' => 'Wrong '.$i, 'is_correct' => false])->id,
            ];
        }
        return [$lesson, $answers];
    }

    public function test_completed_round_is_recorded_once_with_first_answers_and_elapsed_time(): void
    {
        $this->freezeTime();
        [$lesson, $answers] = $this->fixture();
        $this->actingAs(User::factory()->create());
        $this->get(route('lessons.learn', $lesson))->assertRedirect();
        $submit = fn ($step, $answer) => $this->post(route('lessons.learn.submit', [
            'lesson' => $lesson->id, 'step' => $step,
        ]), ['answer' => $answer]);
        $submit(1, $answers[0]['wrong'])->assertRedirect();
        $submit(1, $answers[0]['right'])->assertRedirect();
        $this->assertDatabaseCount('learning_attempts', 0);
        $this->travel(90)->seconds();
        $submit(2, $answers[1]['right'])->assertRedirect();
        $attempt = LearningAttempt::sole();
        $this->assertSame(50.0, $attempt->score_percent);
        $this->assertSame(90, $attempt->elapsed_seconds);
        $this->assertSame(1, $attempt->correct_count);
        $this->assertSame(1, $attempt->wrong_count);
        $this->assertSame('Wrong 0', $attempt->results[0]['first_answer']);
        $this->assertSame('Right 0', $attempt->results[0]['last_answer']);
        $this->assertSame(2, $attempt->results[0]['attempts']);
        $this->get(route('lessons.learn.summary', $lesson))->assertOk();
        $this->get(route('lessons.learn.summary', $lesson))->assertOk();
        $submit(2, $answers[1]['right'])->assertRedirect();
        $this->assertDatabaseCount('learning_attempts', 1);
        $this->travelBack();
    }

    public function test_replaying_keeps_separate_history_without_increasing_original_rewards(): void
    {
        [$lesson, $answers] = $this->fixture();
        $user = User::factory()->create();
        $this->actingAs($user);
        $originalRewards = null;
        for ($round = 0; $round < 2; $round++) {
            $this->get(route('lessons.learn', $lesson))->assertRedirect();
            foreach ($answers as $index => $answer) {
                $this->post(route('lessons.learn.submit', ['lesson' => $lesson->id, 'step' => $index + 1]),
                    ['answer' => $answer['right']])->assertRedirect();
            }
            $progress = LessonProgress::where('user_id', $user->id)->where('lesson_id', $lesson->id)->sole();
            $rewards = [$progress->xp, $progress->stars];
            if ($originalRewards === null) {
                $originalRewards = $rewards;
            } else {
                $this->assertSame($originalRewards, $rewards);
            }
        }
        $this->assertDatabaseCount('learning_attempts', 2);
        $this->assertSame(2, LearningAttempt::pluck('run_key')->unique()->count());
    }

    public function test_guest_completion_does_not_save_personal_history(): void
    {
        [$lesson, $answers] = $this->fixture();
        $this->get(route('lessons.learn', $lesson))->assertRedirect();
        foreach ($answers as $index => $answer) {
            $this->post(route('lessons.learn.submit', ['lesson' => $lesson->id, 'step' => $index + 1]),
                ['answer' => $answer['right']])->assertRedirect();
        }
        $this->assertDatabaseCount('learning_attempts', 0);
    }
}
