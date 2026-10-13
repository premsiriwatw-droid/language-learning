<?php

namespace Tests\Feature;

use App\Models\Exercise;
use App\Models\Lesson;
use App\Models\LessonProgress;
use App\Models\Question;
use App\Models\User;
use App\Services\Progress\LearningProgress;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\CreatesContentLesson;
use Tests\TestCase;

class PlayerLevelTest extends TestCase
{
    use RefreshDatabase;
    use CreatesContentLesson;

    public function test_profile_level_uses_only_own_completed_lesson_rewards(): void
    {
        config()->set('learning.levels.xp_per_level', 200);
        $user = User::factory()->create();
        $other = User::factory()->create();
        $lesson = $this->createContentLesson();
        app(LearningProgress::class)->complete($user, $lesson, 730, 3);
        app(LearningProgress::class)->complete($other, $lesson, 2000, 3);
        $pending = Lesson::forceCreate(['unit_id' => $lesson->unit_id, 'title' => 'Pending']);
        LessonProgress::create(['user_id' => $user->id, 'lesson_id' => $pending->id, 'xp' => 999, 'stars' => 0]);
        $this->actingAs($user)->get(route('profile'))->assertOk()
            ->assertViewHas('playerLevel', fn ($level) => $level['level'] === 4
                && $level['total_xp'] === 730 && $level['remaining_xp'] === 70);
    }

    public function test_lesson_completion_announces_level_up_but_replay_does_not_award_again(): void
    {
        config()->set('learning.levels.xp_per_level', 200);
        config()->set('learning.rewards.completion_xp', 20);
        config()->set('learning.rewards.xp_per_first_correct', 10);
        $user = User::factory()->create();
        $previous = $this->createContentLesson();
        app(LearningProgress::class)->complete($user, $previous, 180, 1);
        $lesson = Lesson::forceCreate(['unit_id' => $previous->unit_id, 'title' => 'Level lesson']);
        $exercise = Exercise::factory()->create(['lesson_id' => $lesson->id, 'type' => 'multiple_choice']);
        $question = Question::factory()->for($exercise)->create();
        $right = $question->answers()->create(['answer' => 'Right', 'is_correct' => true]);
        $question->answers()->create(['answer' => 'Wrong', 'is_correct' => false]);
        $this->actingAs($user);
        for ($round = 0; $round < 2; $round++) {
            $this->get(route('lessons.learn', $lesson))->assertRedirect();
            $this->post(route('lessons.learn.submit', ['lesson' => $lesson->id, 'step' => 1]),
                ['answer' => $right->id])->assertRedirect();
            $this->get(route('lessons.learn.summary', $lesson))->assertOk()
                ->assertViewHas('summary', fn ($summary) => $summary['player_level']['level'] === 2
                    && $summary['player_level']['total_xp'] === 210
                    && $summary['level_change'] === ($round === 0 ? ['from' => 1, 'to' => 2] : null));
        }
        $this->assertSame(210, app(LearningProgress::class)->summary($user)['xp']);
    }
}
