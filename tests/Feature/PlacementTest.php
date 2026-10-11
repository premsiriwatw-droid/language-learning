<?php

namespace Tests\Feature;

use App\Enums\LearnerProficiency;
use App\Models\Course;
use App\Models\Exercise;
use App\Models\Language;
use App\Models\Lesson;
use App\Models\Question;
use App\Models\Unit;
use App\Models\User;
use App\Services\Progress\PlacementTestService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class PlacementTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_cannot_view_or_submit_placement(): void
    {
        $this->get(route('placement.show'))->assertRedirect(route('login'));
        $this->post(route('placement.submit'), [])->assertRedirect(route('login'));
    }

    #[DataProvider('nonLearnerRoles')]
    public function test_non_learner_roles_cannot_use_placement(string $role): void
    {
        $user = User::factory()->create(['role' => $role]);
        $this->actingAs($user)->get(route('placement.show'))->assertForbidden();
        $this->post(route('placement.submit'), [])->assertForbidden();
        $this->assertSame($role, $user->fresh()->role);
    }

    public static function nonLearnerRoles(): array
    {
        return [['admin'], ['teacher']];
    }

    public function test_test_reuses_twelve_questions_across_lesson_positions_without_exposing_answers(): void
    {
        $language = $this->bank();
        $user = $this->pendingLearner();
        $response = $this->actingAs($user)->get(route('placement.show'));
        $response->assertOk()->assertDontSee('SERVER_ONLY_EXPLANATION');
        $state = session('placement_test');
        $questions = $response->viewData('questions');

        $this->assertCount(12, $questions);
        $this->assertSame($language->id, $state['language_id']);
        $this->assertSame($user->id, $state['user_id']);
        $this->assertSame(12, Question::whereIn('id', $state['question_ids'])
            ->get()->pluck('exercise.lesson.id')->unique()->count());
        foreach ($questions as $question) {
            $this->assertSame(['id', 'question', 'answers'], array_keys($question));
            foreach ($question['answers'] as $answer) {
                $this->assertSame(['id', 'answer'], array_keys($answer));
            }
        }
        $this->assertDatabaseCount('learning_attempts', 0);
        $this->assertDatabaseCount('lesson_progress', 0);
    }

    public function test_repeated_get_preserves_the_pinned_test_and_choices(): void
    {
        $this->bank();
        $this->actingAs($this->pendingLearner());
        $first = $this->get(route('placement.show'))->assertOk();
        $state = session('placement_test');
        $second = $this->get(route('placement.show'))->assertOk();
        $this->assertSame($state, session('placement_test'));
        $this->assertSame($first->viewData('questions'), $second->viewData('questions'));
    }

    public function test_selecting_another_language_starts_a_test_using_only_that_language(): void
    {
        $this->bank('English');
        $chinese = $this->bank('Chinese');
        $this->actingAs($this->pendingLearner())->get(route('placement.show'))->assertOk();
        $previousToken = session('placement_test.token');
        $this->get(route('placement.show', ['language_id' => $chinese->id]))->assertOk();
        $state = session('placement_test');

        $this->assertNotSame($previousToken, $state['token']);
        $this->assertSame($chinese->id, $state['language_id']);
        $this->assertSame([$chinese->id], Question::whereIn('id', $state['question_ids'])->get()
            ->pluck('exercise.lesson.unit.course.language_id')->unique()->all());
    }

    #[DataProvider('scoreBands')]
    public function test_submission_calculates_and_saves_the_recommended_proficiency(int $correct, string $level): void
    {
        $this->bank();
        $user = $this->pendingLearner();
        $state = $this->start($user);
        $this->post(route('placement.submit'), [
            'token' => $state['token'], 'answers' => $this->answers($state, $correct),
            'score' => 100, 'learner_proficiency' => 'advanced',
        ])->assertRedirect(route('profile'))->assertSessionHas('status');

        $this->assertSame(LearnerProficiency::from($level), $user->fresh()->learner_proficiency);
        $this->assertFalse($user->fresh()->placement_required);
        $this->assertNull(session('placement_test'));
        $this->assertDatabaseCount('lesson_progress', 0);
        $this->assertDatabaseCount('learning_attempts', 0);
    }

    public static function scoreBands(): array
    {
        return [[0, 'beginner'], [5, 'beginner'], [6, 'intermediate'], [9, 'intermediate'], [10, 'advanced'], [12, 'advanced']];
    }

    public function test_recommendation_uses_configurable_exact_percentage_boundaries(): void
    {
        $placement = app(PlacementTestService::class);
        $this->assertSame(LearnerProficiency::Beginner, $placement->recommendation(49.99));
        $this->assertSame(LearnerProficiency::Intermediate, $placement->recommendation(50));
        $this->assertSame(LearnerProficiency::Intermediate, $placement->recommendation(79.99));
        $this->assertSame(LearnerProficiency::Advanced, $placement->recommendation(80));
        config()->set('progression.placement.intermediate_percent', 60);
        config()->set('progression.placement.advanced_percent', 90);
        $this->assertSame(LearnerProficiency::Beginner, $placement->recommendation(50));
        $this->assertSame(LearnerProficiency::Intermediate, $placement->recommendation(80));
        $this->assertSame(LearnerProficiency::Advanced, $placement->recommendation(90));
    }

    public function test_consumed_or_invalid_token_cannot_change_a_saved_result(): void
    {
        $this->bank();
        $user = $this->pendingLearner();
        $state = $this->start($user);
        $this->post(route('placement.submit'), [
            'token' => str_repeat('x', 64), 'answers' => $this->answers($state, 12),
        ])->assertSessionHasErrors('token');
        $this->assertNull($user->fresh()->learner_proficiency);

        $this->post(route('placement.submit'), [
            'token' => $state['token'], 'answers' => $this->answers($state, 6),
        ])->assertRedirect(route('profile'));
        $this->post(route('placement.submit'), [
            'token' => $state['token'], 'answers' => $this->answers($state, 12),
        ])->assertSessionHasErrors('token');
        $this->assertSame(LearnerProficiency::Intermediate, $user->fresh()->learner_proficiency);
    }

    public function test_answer_from_another_question_is_rejected(): void
    {
        $this->bank();
        $user = $this->pendingLearner();
        $state = $this->start($user);
        $answers = $this->answers($state, 12);
        $answers[$state['question_ids'][0]] = $answers[$state['question_ids'][1]];
        $this->post(route('placement.submit'), ['token' => $state['token'], 'answers' => $answers])
            ->assertSessionHasErrors('answers');
        $this->assertNull($user->fresh()->learner_proficiency);
    }

    public function test_unpinned_question_and_missing_or_extra_answers_are_rejected(): void
    {
        $this->bank();
        $user = $this->pendingLearner();
        $state = $this->start($user);
        $answers = $this->answers($state, 12);
        $foreign = Question::whereNotIn('id', $state['question_ids'])->firstOrFail();
        unset($answers[$state['question_ids'][0]]);
        $answers[$foreign->id] = $foreign->answers()->where('is_correct', true)->firstOrFail()->id;
        $this->post(route('placement.submit'), ['token' => $state['token'], 'answers' => $answers])
            ->assertSessionHasErrors('answers');

        $answers = $this->answers($state, 12);
        unset($answers[$state['question_ids'][0]]);
        $this->post(route('placement.submit'), ['token' => $state['token'], 'answers' => $answers])
            ->assertSessionHasErrors('answers');

        $answers = $this->answers($state, 12);
        $answers[$foreign->id] = $foreign->answers()->where('is_correct', true)->firstOrFail()->id;
        $this->post(route('placement.submit'), ['token' => $state['token'], 'answers' => $answers])
            ->assertSessionHasErrors('answers');
        $this->assertNull($user->fresh()->learner_proficiency);
    }

    public function test_non_integer_answers_are_rejected(): void
    {
        $this->bank();
        $state = $this->start($this->pendingLearner());
        $answers = $this->answers($state, 12);
        $answers[$state['question_ids'][0]] = ['answer' => 1];
        $this->post(route('placement.submit'), ['token' => $state['token'], 'answers' => $answers])
            ->assertSessionHasErrors();
    }

    public function test_user_cannot_submit_another_users_run_or_target_another_user(): void
    {
        $this->bank();
        $first = $this->pendingLearner();
        $second = $this->pendingLearner();
        $state = $this->start($first);
        $payload = ['token' => $state['token'], 'answers' => $this->answers($state, 12), 'user_id' => $second->id];
        $this->actingAs($second)->post(route('placement.submit'), $payload)->assertSessionHasErrors('token');
        $this->assertNull($first->fresh()->learner_proficiency);
        $this->assertNull($second->fresh()->learner_proficiency);

        $this->actingAs($first)->post(route('placement.submit'), $payload)->assertRedirect(route('profile'));
        $this->assertSame(LearnerProficiency::Advanced, $first->fresh()->learner_proficiency);
        $this->assertNull($second->fresh()->learner_proficiency);
    }

    public function test_insufficient_content_is_safe_and_does_not_create_or_change_progress(): void
    {
        $user = $this->pendingLearner();
        $this->actingAs($user)->get(route('placement.show'))->assertOk()
            ->assertViewHas('questions', [])->assertSee('ยังมีคำถามพร้อมใช้งานไม่ครบ');
        $this->assertNull(session('placement_test'));
        $this->assertNull($user->fresh()->learner_proficiency);
        $this->assertTrue($user->fresh()->placement_required);
        $this->assertDatabaseCount('questions', 0);
        $this->assertDatabaseCount('lesson_progress', 0);
    }

    public function test_ineligible_exercises_and_invalid_questions_are_not_selected(): void
    {
        $language = $this->bank();
        $lesson = Lesson::firstOrFail();
        foreach (['listening', 'image_choice', 'fill_blank'] as $type) {
            $exercise = Exercise::factory()->create(['lesson_id' => $lesson->id, 'type' => $type]);
            $question = Question::factory()->for($exercise)->create();
            $question->answers()->create(['answer' => 'Right', 'is_correct' => true]);
            $question->answers()->create(['answer' => 'Wrong', 'is_correct' => false]);
        }
        $exercise = $lesson->exercises()->where('type', 'multiple_choice')->firstOrFail();
        $invalid = Question::factory()->for($exercise)->create();
        $invalid->answers()->create(['answer' => 'First correct', 'is_correct' => true]);
        $invalid->answers()->create(['answer' => 'Second correct', 'is_correct' => true]);
        $selected = app(PlacementTestService::class)->createTest($language->id);
        $this->assertCount(12, $selected);
        $this->assertFalse($selected->contains('id', $invalid->id));
        $this->assertSame(['multiple_choice'], $selected->pluck('exercise.type')->unique()->all());
    }

    private function pendingLearner(): User
    {
        return User::factory()->create(['learner_proficiency' => null, 'placement_required' => true]);
    }

    private function start(User $user): array
    {
        $this->actingAs($user)->get(route('placement.show'))->assertOk();

        return session('placement_test');
    }

    private function answers(array $state, int $correctCount): array
    {
        $answers = [];
        foreach ($state['question_ids'] as $index => $id) {
            $answers[$id] = Question::findOrFail($id)->answers()
                ->where('is_correct', $index < $correctCount)->firstOrFail()->id;
        }

        return $answers;
    }

    private function bank(string $name = 'English'): Language
    {
        $language = Language::create(['name' => $name]);
        $course = Course::forceCreate(['language_id' => $language->id, 'title' => $name.' Beginner']);
        for ($unitNumber = 1; $unitNumber <= 3; $unitNumber++) {
            $unit = Unit::forceCreate(['course_id' => $course->id, 'title' => 'Unit '.$unitNumber]);
            for ($lessonNumber = 1; $lessonNumber <= 4; $lessonNumber++) {
                $lesson = Lesson::forceCreate(['unit_id' => $unit->id, 'title' => 'Lesson '.$lessonNumber]);
                $exercise = Exercise::factory()->create(['lesson_id' => $lesson->id, 'type' => 'multiple_choice']);
                for ($number = 1; $number <= 2; $number++) {
                    $question = Question::factory()->for($exercise)->create([
                        'question' => 'Choose '.$number.' in '.$unitNumber.'.'.$lessonNumber,
                        'explanation' => 'SERVER_ONLY_EXPLANATION',
                    ]);
                    $question->answers()->create(['answer' => 'Right '.$question->id, 'is_correct' => true]);
                    $question->answers()->create(['answer' => 'Wrong '.$question->id, 'is_correct' => false]);
                }
            }
        }

        return $language;
    }
}
