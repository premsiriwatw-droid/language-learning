<?php

namespace Tests\Feature;

use App\Enums\LearnerProficiency;
use App\Models\Exercise;
use App\Models\LearningAttempt;
use App\Models\LessonProgress;
use App\Models\Question;
use App\Models\User;
use App\Services\Progress\DailyQuestService;
use App\Services\Progress\LearningProgress;
use App\Services\Progress\PlayerLevelCalculator;
use App\Services\Progress\StarRewardService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\CreatesContentLesson;
use Tests\TestCase;

class LearnerGamificationIntegrationTest extends TestCase
{
    use RefreshDatabase;
    use CreatesContentLesson;

    private function quiz(): array
    {
        $lesson = $this->createContentLesson();
        $exercise = Exercise::factory()->create(['lesson_id' => $lesson->id, 'type' => 'multiple_choice']);
        $answers = [];
        for ($i = 0; $i < 2; $i++) {
            $question = Question::factory()->for($exercise)->create();
            $answers[] = [
                'question' => $question,
                'right' => $question->answers()->create(['answer' => 'Right', 'is_correct' => true])->id,
                'wrong' => $question->answers()->create(['answer' => 'Wrong', 'is_correct' => false])->id,
            ];
        }

        return [$lesson, $answers];
    }

    private function finish($lesson, array $answers): void
    {
        $this->get(route('lessons.learn', $lesson))->assertRedirect();
        foreach ($answers as $i => $answer) {
            $this->post(route('lessons.learn.submit', ['lesson' => $lesson->id, 'step' => $i + 1]),
                ['answer' => $answer['right']])->assertRedirect()->assertSessionHasNoErrors();
        }
    }

    public function test_milestone_failure_rolls_back_first_completion_and_allows_a_safe_retry(): void
    {
        config()->set('progression.stars.milestone_lessons', 1);
        $user = User::factory()->create();
        [$lesson] = $this->quiz();
        $failing = \Mockery::mock(StarRewardService::class);
        $failing->shouldReceive('milestone')->once()->andThrow(new \RuntimeException('Reward unavailable'));
        $this->app->instance(StarRewardService::class, $failing);

        try {
            app(LearningProgress::class)->complete($user, $lesson, 30, 1);
            $this->fail('Failed milestone must roll back completion.');
        } catch (\RuntimeException $exception) {
            $this->assertSame('Reward unavailable', $exception->getMessage());
        }
        $this->assertDatabaseCount('lesson_progress', 0);
        $this->assertDatabaseCount('star_rewards', 0);
        $this->app->forgetInstance(StarRewardService::class);
        $saved = app(LearningProgress::class)->complete($user, $lesson, 30, 1);
        $this->assertSame(30, $saved->xp);
        $this->assertSame(1, $saved->stars);
        $this->assertDatabaseCount('star_rewards', 1);
    }

    public function test_verified_completion_connects_rewards_streak_quests_and_profile_without_changing_xp(): void
    {
        $this->freezeTime();
        config()->set('progression.stars.milestone_lessons', 1);
        config()->set('progression.quests.correct_answers.target', 2);
        $user = User::factory()->create();
        [$lesson, $answers] = $this->quiz();
        $this->actingAs($user);

        $this->get(route('lessons.learn', $lesson));
        $this->post(route('lessons.learn.submit', ['lesson' => $lesson->id, 'step' => 1]),
            ['answer' => $answers[0]['right']]);
        $this->assertDatabaseCount('star_rewards', 0);
        $this->assertDatabaseCount('daily_quest_events', 0);
        $this->assertSame(0, $user->fresh()->current_streak);
        $this->post(route('lessons.learn.submit', ['lesson' => $lesson->id, 'step' => 2]),
            ['answer' => $answers[1]['right']])->assertSessionHasNoErrors();

        $progress = LessonProgress::sole();
        $this->assertSame(40, $progress->xp);
        $this->assertSame(3, $progress->stars);
        $this->assertSame(1, $user->fresh()->current_streak);
        $this->assertDatabaseCount('star_rewards', 1);
        $quests = app(DailyQuestService::class)->today($user);
        $this->assertNotNull($quests->firstWhere('type', 'complete_lesson')->completed_at);
        $this->assertNotNull($quests->firstWhere('type', 'correct_answers')->completed_at);
        $this->assertDatabaseCount('daily_quest_events', 3);

        $this->get(route('lessons.learn.summary', $lesson))->assertOk();
        $this->finish($lesson, $answers);
        $this->assertDatabaseCount('learning_attempts', 2);
        $this->assertDatabaseCount('star_rewards', 1);
        $this->assertDatabaseCount('daily_quest_events', 3);
        $this->assertSame(1, $user->fresh()->current_streak);

        $quest = $quests->firstWhere('type', 'complete_lesson');
        $this->post(route('daily-quests.claim', $quest->id))->assertRedirect();
        $this->post(route('daily-quests.claim', $quest->id))->assertRedirect();
        $this->assertDatabaseCount('star_rewards', 2);
        $summary = app(LearningProgress::class)->summary($user);
        $this->assertSame(40, $summary['xp']);
        $this->assertSame(15, $summary['stars']);
        $this->assertSame(1, app(PlayerLevelCalculator::class)->calculate($summary['xp'])['level']);
        $this->get(route('profile'))->assertOk()->assertSee('Beginner')
            ->assertSee('Current Streak: 1')->assertSee('Daily Quests: 2 / 3');
    }

    public function test_completed_attempt_can_promote_using_the_history_saved_in_the_same_transaction(): void
    {
        [$lesson, $answers] = $this->quiz();
        config()->set('progression.promotion.course_titles.beginner', [$lesson->unit->course->title]);
        config()->set('progression.promotion.minimum_scored_lessons', 1);
        $user = User::factory()->create();
        $this->actingAs($user);
        $this->finish($lesson, $answers);
        $this->assertSame(LearnerProficiency::Intermediate, $user->fresh()->learner_proficiency);
        $this->finish($lesson, $answers);
        $this->assertSame(LearnerProficiency::Intermediate, $user->fresh()->learner_proficiency);
    }

    public function test_successful_mistake_review_advances_only_own_quest_and_keeps_history_and_lesson_rewards(): void
    {
        [$lesson, $answers] = $this->quiz();
        $user = User::factory()->create();
        $other = User::factory()->create();
        $this->actingAs($user);
        $this->get(route('lessons.learn', $lesson));
        $this->post(route('lessons.learn.submit', ['lesson' => $lesson->id, 'step' => 1]),
            ['answer' => $answers[0]['wrong']]);
        $this->post(route('lessons.learn.submit', ['lesson' => $lesson->id, 'step' => 1]),
            ['answer' => $answers[0]['right']]);
        $this->post(route('lessons.learn.submit', ['lesson' => $lesson->id, 'step' => 2]),
            ['answer' => $answers[1]['right']]);
        $attempt = LearningAttempt::sole();
        $original = $attempt->getRawOriginal();
        $rewards = LessonProgress::sole()->only(['xp', 'stars']);
        $target = route('learning-history.review.submit', $attempt);

        $this->get(route('learning-history.review', $attempt))->assertOk();
        $this->post($target, ['question_id' => $answers[0]['question']->id, 'action' => 'answer',
            'answer' => $answers[0]['wrong']])->assertRedirect();
        $this->assertSame(0, app(DailyQuestService::class)->today($user)->firstWhere('type', 'review_mistakes')->progress);
        $this->post($target, ['question_id' => $answers[0]['question']->id, 'action' => 'answer',
            'answer' => $answers[0]['right']])->assertRedirect();
        $this->post($target, ['question_id' => $answers[0]['question']->id, 'action' => 'answer',
            'answer' => $answers[0]['right']])->assertRedirect();
        $this->assertSame(1, app(DailyQuestService::class)->today($user)->firstWhere('type', 'review_mistakes')->progress);
        $this->assertSame($original, $attempt->fresh()->getRawOriginal());
        $this->assertSame($rewards, LessonProgress::sole()->only(['xp', 'stars']));
        $this->actingAs($other)->post($target, ['question_id' => $answers[0]['question']->id,
            'action' => 'answer', 'answer' => $answers[0]['right']])->assertNotFound();
        $this->assertSame(0, app(DailyQuestService::class)->today($other)->firstWhere('type', 'review_mistakes')->progress);
    }

    public function test_nonlearner_completion_preserves_existing_learning_behavior_without_gamification(): void
    {
        [$lesson, $answers] = $this->quiz();
        $user = User::factory()->create(['role' => 'teacher']);
        $this->actingAs($user);
        $this->finish($lesson, $answers);
        $this->assertSame(40, LessonProgress::sole()->xp);
        $this->assertSame(0, $user->fresh()->current_streak);
        $this->assertDatabaseCount('daily_quests', 0);
        $this->assertDatabaseCount('star_rewards', 0);
    }
}
