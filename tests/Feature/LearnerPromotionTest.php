<?php

namespace Tests\Feature;

use App\Enums\LearnerProficiency;
use App\Models\Course;
use App\Models\Language;
use App\Models\LearningAttempt;
use App\Models\Lesson;
use App\Models\LessonProgress;
use App\Models\Unit;
use App\Models\User;
use App\Services\Progress\LearnerPromotionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Tests\TestCase;

class LearnerPromotionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config()->set('progression.promotion', [
            'course_titles' => [
                'beginner' => ['Chinese Beginner', 'English Beginner'],
                'intermediate' => [],
            ],
            'completion_percent' => 80,
            'first_attempt_score_percent' => 75,
            'minimum_scored_lessons' => 5,
        ]);
    }

    public function test_beginner_promotes_at_both_thresholds_and_does_not_reuse_the_course(): void
    {
        $user = $this->learner();
        $this->completeLessons($user, $this->courseLessons()->take(8), 75);

        $this->assertTrue(app(LearnerPromotionService::class)->promote($user));
        $this->assertSame(LearnerProficiency::Intermediate, $user->learner_proficiency);
        $this->assertSame(LearnerProficiency::Intermediate, $user->fresh()->learner_proficiency);
        $this->assertFalse(app(LearnerPromotionService::class)->promote($user));
    }

    public function test_completion_below_threshold_does_not_promote(): void
    {
        $user = $this->learner();
        $this->completeLessons($user, $this->courseLessons()->take(7), 100);
        $this->assertNotPromoted($user);
    }

    public function test_first_score_below_threshold_does_not_promote(): void
    {
        $user = $this->learner();
        $this->completeLessons($user, $this->courseLessons()->take(8), 74.99);
        $this->assertNotPromoted($user);
    }

    public function test_average_uses_first_scores_from_different_lessons(): void
    {
        $user = $this->learner();
        $lessons = $this->courseLessons()->take(8);
        $this->completeLessons($user, $lessons->take(4), 50);
        $this->completeLessons($user, $lessons->skip(4), 100);
        $this->assertTrue(app(LearnerPromotionService::class)->promote($user));
        $this->assertSame(LearnerProficiency::Intermediate, $user->fresh()->learner_proficiency);
    }

    public function test_fewer_than_minimum_scored_lessons_does_not_promote(): void
    {
        $user = $this->learner();
        $lessons = $this->courseLessons()->take(8);
        $this->completeLessons($user, $lessons->take(4), 100);
        $this->completeLessons($user, $lessons->skip(4), null);
        $this->assertNotPromoted($user);
    }

    public function test_vocabulary_only_lessons_count_completion_but_not_scored_evidence(): void
    {
        $user = $this->learner();
        $lessons = $this->courseLessons()->take(8);
        $this->completeLessons($user, $lessons->take(5), 75);
        $this->completeLessons($user, $lessons->skip(5), null);
        $this->assertTrue(app(LearnerPromotionService::class)->promote($user));
        $this->assertSame(LearnerProficiency::Intermediate, $user->fresh()->learner_proficiency);
    }

    public function test_good_replay_does_not_replace_a_poor_first_score(): void
    {
        $user = $this->learner();
        $lessons = $this->courseLessons()->take(8);
        $this->completeLessons($user, $lessons, 50);
        foreach ($lessons as $lesson) {
            $this->attempt($user, $lesson, 100, 1);
        }
        $this->assertNotPromoted($user);
    }

    public function test_good_replay_does_not_replace_an_unscored_first_round(): void
    {
        $user = $this->learner();
        $lessons = $this->courseLessons()->take(8);
        $this->completeLessons($user, $lessons, null);
        foreach ($lessons as $lesson) {
            $this->attempt($user, $lesson, 100, 1);
        }
        $this->assertNotPromoted($user);
    }

    public function test_first_attempt_is_chronological_even_when_inserted_after_replay(): void
    {
        $user = $this->learner();
        $lessons = $this->courseLessons()->take(8);
        $this->completeLessons($user, $lessons, 100);
        foreach ($lessons as $lesson) {
            $this->attempt($user, $lesson, 50, -1);
        }
        $this->assertNotPromoted($user);
    }

    public function test_separate_courses_cannot_combine_partial_completion(): void
    {
        $user = $this->learner();
        $this->completeLessons($user, $this->courseLessons('Chinese Beginner')->take(4), 100);
        $this->completeLessons($user, $this->courseLessons('English Beginner')->take(4), 100);
        $this->assertNotPromoted($user);
    }

    public function test_one_qualifying_course_is_enough(): void
    {
        $user = $this->learner();
        $this->completeLessons($user, $this->courseLessons('Chinese Beginner')->take(2), 100);
        $this->completeLessons($user, $this->courseLessons('English Beginner')->take(8), 75);
        $this->assertTrue(app(LearnerPromotionService::class)->promote($user));
        $this->assertSame(LearnerProficiency::Intermediate, $user->fresh()->learner_proficiency);
    }

    public function test_other_users_completion_and_scores_do_not_count(): void
    {
        $user = $this->learner();
        $other = $this->learner();
        $this->completeLessons($other, $this->courseLessons()->take(8), 100);
        $this->assertNotPromoted($user);
    }

    public function test_own_completion_cannot_use_another_users_scores(): void
    {
        $user = $this->learner();
        $other = $this->learner();
        foreach ($this->courseLessons()->take(8) as $lesson) {
            LessonProgress::create(['user_id' => $user->id, 'lesson_id' => $lesson->id, 'completed_at' => now()]);
            $this->attempt($other, $lesson, 100);
        }
        $this->assertNotPromoted($user);
    }

    public function test_incomplete_progress_does_not_count_even_with_scores(): void
    {
        $user = $this->learner();
        foreach ($this->courseLessons()->take(8) as $lesson) {
            LessonProgress::create(['user_id' => $user->id, 'lesson_id' => $lesson->id]);
            $this->attempt($user, $lesson, 100);
        }
        $this->assertNotPromoted($user);
    }

    public function test_unconfigured_courses_and_empty_courses_do_not_promote(): void
    {
        $user = $this->learner();
        $this->completeLessons($user, $this->courseLessons('Other Course')->take(10), 100);
        $this->courseLessons('Chinese Beginner', 0);
        $this->assertNotPromoted($user);
    }

    public function test_pending_placement_blocks_promotion(): void
    {
        $user = $this->learner(['placement_required' => true]);
        $this->completeLessons($user, $this->courseLessons()->take(8), 100);
        $this->assertNotPromoted($user);
    }

    public function test_admin_and_teacher_cannot_be_promoted(): void
    {
        $lessons = $this->courseLessons()->take(8);
        foreach (['admin', 'teacher'] as $role) {
            $user = $this->learner(['role' => $role]);
            $this->completeLessons($user, $lessons, 100);
            $this->assertNotPromoted($user);
        }
    }

    public function test_unassigned_proficiency_does_not_promote(): void
    {
        $user = $this->learner(['learner_proficiency' => null]);
        $this->completeLessons($user, $this->courseLessons()->take(8), 100);
        $this->assertFalse(app(LearnerPromotionService::class)->promote($user));
        $this->assertNull($user->fresh()->learner_proficiency);
    }

    public function test_intermediate_promotes_only_from_its_configured_course(): void
    {
        config()->set('progression.promotion.course_titles.intermediate', ['English Intermediate']);
        $user = $this->learner(['learner_proficiency' => LearnerProficiency::Intermediate]);
        $this->completeLessons($user, $this->courseLessons('English Beginner')->take(10), 100);
        $this->assertFalse(app(LearnerPromotionService::class)->promote($user));
        $this->completeLessons($user, $this->courseLessons('English Intermediate')->take(8), 75);
        $this->assertTrue(app(LearnerPromotionService::class)->promote($user));
        $this->assertSame(LearnerProficiency::Advanced, $user->fresh()->learner_proficiency);
    }

    public function test_advanced_is_the_maximum_proficiency(): void
    {
        $user = $this->learner(['learner_proficiency' => LearnerProficiency::Advanced]);
        $this->completeLessons($user, $this->courseLessons()->take(10), 100);
        $this->assertFalse(app(LearnerPromotionService::class)->promote($user));
        $this->assertSame(LearnerProficiency::Advanced, $user->fresh()->learner_proficiency);
    }

    public function test_promotion_thresholds_and_minimum_evidence_are_configurable(): void
    {
        config()->set('progression.promotion.completion_percent', 60);
        config()->set('progression.promotion.first_attempt_score_percent', 90);
        config()->set('progression.promotion.minimum_scored_lessons', 3);
        $user = $this->learner();
        $lessons = $this->courseLessons()->take(6);
        $this->completeLessons($user, $lessons->take(3), 90);
        $this->completeLessons($user, $lessons->skip(3), null);
        $this->assertTrue(app(LearnerPromotionService::class)->promote($user));
        $this->assertSame(LearnerProficiency::Intermediate, $user->fresh()->learner_proficiency);
    }

    private function learner(array $attributes = []): User
    {
        return User::factory()->create(array_merge([
            'role' => 'user',
            'learner_proficiency' => LearnerProficiency::Beginner,
            'placement_required' => false,
        ], $attributes));
    }

    private function courseLessons(string $title = 'Chinese Beginner', int $count = 10): Collection
    {
        $language = Language::factory()->create();
        $course = Course::factory()->for($language)->create(['title' => $title]);
        $unit = Unit::factory()->for($course)->create();

        return Lesson::factory()->count($count)->for($unit)->create();
    }

    private function completeLessons(User $user, Collection $lessons, ?float $score): void
    {
        foreach ($lessons as $lesson) {
            LessonProgress::create(['user_id' => $user->id, 'lesson_id' => $lesson->id, 'completed_at' => now()]);
            $this->attempt($user, $lesson, $score);
        }
    }

    private function attempt(User $user, Lesson $lesson, ?float $score, int $dayOffset = 0): void
    {
        $completed = now()->addDays($dayOffset);
        $questionCount = $score === null ? 0 : 10;

        LearningAttempt::create([
            'user_id' => $user->id,
            'lesson_id' => $lesson->id,
            'run_key' => (string) Str::uuid(),
            'lesson_title' => $lesson->title,
            'vocabulary_count' => 0,
            'question_count' => $questionCount,
            'correct_count' => $questionCount === 0 ? 0 : (int) round($score / 10),
            'wrong_count' => $questionCount === 0 ? 0 : $questionCount - (int) round($score / 10),
            'wrong_attempts' => 0,
            'score_percent' => $score,
            'elapsed_seconds' => 60,
            'started_at' => $completed->copy()->subMinute(),
            'completed_at' => $completed,
            'results' => [],
        ]);
    }

    private function assertNotPromoted(User $user): void
    {
        $this->assertFalse(app(LearnerPromotionService::class)->promote($user));
        $this->assertSame(LearnerProficiency::Beginner, $user->fresh()->learner_proficiency);
    }
}
