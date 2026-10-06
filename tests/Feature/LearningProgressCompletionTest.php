<?php

namespace Tests\Feature;

use App\Models\Exercise;
use App\Models\Lesson;
use App\Models\LessonProgress;
use App\Models\Question;
use App\Models\User;
use App\Models\Vocabulary;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\CreatesContentLesson;
use Tests\TestCase;

class LearningProgressCompletionTest extends TestCase
{
    use RefreshDatabase;
    use CreatesContentLesson;

    protected function setUp(): void
    {
        parent::setUp();

        config()->set('learning.rewards', [
            'completion_xp' => 20,
            'xp_per_first_correct' => 10,
            'three_star_percent' => 90,
            'two_star_percent' => 70,
            'vocabulary_only_stars' => 1,
        ]);
    }

    public function test_rewards_are_saved_only_after_every_question_is_solved(): void
    {
        $user = User::factory()->create();
        [$lesson, $answers] = $this->createQuizLesson();

        $this->actingAs($user)
            ->get(route('lessons.learn', $lesson))
            ->assertRedirect();

        // เปิดท้ายบทหรือส่งข้อที่สองก่อน ไม่ได้รางวัล
        $this->get(route('lessons.learn.step', [
            'lesson' => $lesson->id,
            'step' => 999,
        ]))->assertRedirect();

        $this->submitAnswer($lesson, 2, $answers[1]['right'])
            ->assertRedirect();

        $this->assertDatabaseCount('lesson_progress', 0);

        $this->submitAnswer($lesson, 1, $answers[0]['right'])
            ->assertSessionHasNoErrors();

        $this->assertDatabaseCount('lesson_progress', 0);

        // ข้อสุดท้ายตอบผิด ยังไม่ถือว่าจบ
        $this->submitAnswer($lesson, 2, $answers[1]['wrong'])
            ->assertSessionHasNoErrors();

        $this->assertDatabaseCount('lesson_progress', 0);

        // ลองใหม่จนถูก: ถูกครั้งแรก 1/2 ได้ 30 XP และ 1 ดาว
        $this->submitAnswer($lesson, 2, $answers[1]['right'])
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('lesson_progress', [
            'user_id' => $user->id,
            'lesson_id' => $lesson->id,
            'xp' => 30,
            'stars' => 1,
        ]);

        $progress = LessonProgress::where('user_id', $user->id)
            ->where('lesson_id', $lesson->id)
            ->firstOrFail();

        $this->assertNotNull($progress->completed_at);

        $this->get(route('lessons.learn.summary', $lesson))
            ->assertOk()
            ->assertViewHas('summary', fn ($summary) =>
                $summary['score_percent'] === 50.0
                && $summary['progress_saved'] === true
                && $summary['saved_xp'] === 30
                && $summary['saved_stars'] === 1
            )
            ->assertSee('30 XP');
    }

    public function test_replay_and_repeated_requests_keep_the_original_rewards(): void
    {
        $this->freezeTime();

        $user = User::factory()->create();
        [$lesson, $answers] = $this->createQuizLesson();

        $this->actingAs($user)
            ->get(route('lessons.learn', $lesson));

        $this->submitAnswer($lesson, 1, $answers[0]['wrong']);
        $this->submitAnswer($lesson, 1, $answers[0]['right']);
        $this->submitAnswer($lesson, 2, $answers[1]['right']);

        $progress = LessonProgress::where('user_id', $user->id)
            ->where('lesson_id', $lesson->id)
            ->firstOrFail();

        $completedAt = $progress->completed_at->toISOString();

        $this->assertSame(30, $progress->xp);
        $this->assertSame(1, $progress->stars);

        $this->travel(60)->seconds();

        // ส่งข้อสุดท้ายซ้ำ และเปิด Summary ซ้ำ
        $this->submitAnswer($lesson, 2, $answers[1]['right'])
            ->assertRedirect();

        $this->get(route('lessons.learn.summary', $lesson))
            ->assertOk();

        // เรียนรอบใหม่ ตอบถูกครั้งแรกทุกข้อ
        $this->get(route('lessons.learn', $lesson));
        $this->submitAnswer($lesson, 1, $answers[0]['right']);
        $this->submitAnswer($lesson, 2, $answers[1]['right']);

        $this->get(route('lessons.learn.summary', $lesson))
            ->assertOk()
            ->assertViewHas('summary', fn ($summary) =>
                $summary['score_percent'] === 100.0
                && $summary['calculated_xp'] === 40
                && $summary['calculated_stars'] === 3
                && $summary['saved_xp'] === 30
                && $summary['saved_stars'] === 1
            );

        $progress->refresh();

        $this->assertSame(30, $progress->xp);
        $this->assertSame(1, $progress->stars);
        $this->assertSame(
            $completedAt,
            $progress->completed_at->toISOString()
        );

        $this->assertDatabaseCount('lesson_progress', 1);
    }

    public function test_vocabulary_only_completion_saves_rewards_for_the_current_user(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $lesson = $this->createContentLesson();

        Vocabulary::factory()->create([
            'lesson_id' => $lesson->id,
        ]);

        $this->actingAs($user)
            ->get(route('lessons.learn', $lesson));

        $this->post(route('lessons.learn.submit', [
            'lesson' => $lesson->id,
            'step' => 1,
        ]))->assertRedirect();

        $this->assertDatabaseHas('lesson_progress', [
            'user_id' => $user->id,
            'lesson_id' => $lesson->id,
            'xp' => 20,
            'stars' => 1,
        ]);

        $this->assertDatabaseMissing('lesson_progress', [
            'user_id' => $otherUser->id,
            'lesson_id' => $lesson->id,
        ]);

        $this->get(route('lessons.learn.summary', $lesson))
            ->assertOk()
            ->assertViewHas('summary', fn ($summary) =>
                $summary['score_percent'] === null
                && $summary['progress_saved'] === true
                && $summary['saved_xp'] === 20
                && $summary['saved_stars'] === 1
            );
    }

    public function test_guest_completion_does_not_save_rewards(): void
    {
        [$lesson, $answers] = $this->createQuizLesson();

        $this->get(route('lessons.learn', $lesson));

        $this->submitAnswer($lesson, 1, $answers[0]['right']);
        $this->submitAnswer($lesson, 2, $answers[1]['right']);

        $this->get(route('lessons.learn.summary', $lesson))
            ->assertOk()
            ->assertViewHas('summary', fn ($summary) =>
                $summary['progress_saved'] === false
                && $summary['saved_xp'] === null
                && $summary['saved_stars'] === null
            );

        $this->assertDatabaseCount('lesson_progress', 0);
    }

    private function createQuizLesson(): array
    {
        $lesson = $this->createContentLesson();

        $exercise = Exercise::factory()->create([
            'lesson_id' => $lesson->id,
            'type' => 'multiple_choice',
        ]);

        $answers = [];

        for ($index = 0; $index < 2; $index++) {
            $question = Question::factory()
                ->for($exercise)
                ->create();

            $right = $question->answers()->create([
                'answer' => 'คำตอบถูก',
                'is_correct' => true,
            ]);

            $wrong = $question->answers()->create([
                'answer' => 'คำตอบผิด',
                'is_correct' => false,
            ]);

            $answers[] = [
                'right' => $right->id,
                'wrong' => $wrong->id,
            ];
        }

        return [$lesson, $answers];
    }

    private function submitAnswer(
        Lesson $lesson,
        int $step,
        int $answerId
    ) {
        return $this->post(route('lessons.learn.submit', [
            'lesson' => $lesson->id,
            'step' => $step,
        ]), [
            'answer' => $answerId,
        ]);
    }
}