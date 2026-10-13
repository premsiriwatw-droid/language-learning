<?php

namespace Tests\Feature;

use App\Models\DailyQuest;
use App\Models\User;
use App\Services\Progress\DailyQuestService;
use App\Services\Progress\LearningProgress;
use App\Services\Progress\StarRewardService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class DailyQuestTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config()->set('app.timezone', 'UTC');
        config()->set('progression.quests', [
            'complete_lesson' => ['target' => 1, 'reward_stars' => 2],
            'correct_answers' => ['target' => 5, 'reward_stars' => 3],
            'review_mistakes' => ['target' => 3, 'reward_stars' => 3],
        ]);
        $this->travelTo(Carbon::parse('2026-10-11 12:00:00', 'UTC'));
    }

    protected function tearDown(): void
    {
        $this->travelBack();
        parent::tearDown();
    }

    public function test_daily_quests_require_login_and_only_show_the_current_user(): void
    {
        $this->get(route('daily-quests.index'))->assertRedirect(route('login'));
        $this->post(route('daily-quests.claim', ['questId' => 1]))->assertRedirect(route('login'));
        $user = User::factory()->create();
        $other = User::factory()->create();
        app(DailyQuestService::class)->recordLesson($other, 7, now());

        $this->actingAs($user)->get(route('daily-quests.index'))->assertOk()
            ->assertViewHas('quests', fn ($quests) => $quests->count() === 3
                && $quests->every(fn ($quest) => $quest->user_id === $user->id && $quest->progress === 0));
        $this->get(route('daily-quests.index'))->assertOk();
        $this->assertDatabaseCount('daily_quests', 6);
    }

    #[DataProvider('nonLearnerRoles')]
    public function test_non_learners_cannot_access_or_claim_daily_quests(string $role): void
    {
        $user = User::factory()->create(['role' => $role]);
        $this->actingAs($user)->get(route('daily-quests.index'))->assertForbidden();
        $this->post(route('daily-quests.claim', ['questId' => 1]))->assertForbidden();
        $this->assertDatabaseCount('daily_quests', 0);
        $this->assertDatabaseCount('star_rewards', 0);
    }

    public static function nonLearnerRoles(): array
    {
        return [['admin'], ['teacher']];
    }

    public function test_correct_and_review_progress_count_unique_questions_and_cap_at_target(): void
    {
        $user = User::factory()->create();
        $service = app(DailyQuestService::class);
        $service->recordCorrectAnswer($user, 10, now());
        $service->recordCorrectAnswer($user, 10, now());
        $service->recordReview($user, 10, now());
        $service->recordReview($user, 10, now());
        $quests = $service->today($user);
        $this->assertSame(1, $quests->firstWhere('type', 'correct_answers')->progress);
        $this->assertSame(1, $quests->firstWhere('type', 'review_mistakes')->progress);
        $this->assertNull($quests->firstWhere('type', 'correct_answers')->completed_at);

        foreach (range(11, 17) as $questionId) {
            $service->recordCorrectAnswer($user, $questionId, now());
            $service->recordReview($user, $questionId, now());
        }

        $quests = $service->today($user);
        $this->assertSame(5, $quests->firstWhere('type', 'correct_answers')->progress);
        $this->assertSame(3, $quests->firstWhere('type', 'review_mistakes')->progress);
        $this->assertNotNull($quests->firstWhere('type', 'correct_answers')->completed_at);
        $this->assertNotNull($quests->firstWhere('type', 'review_mistakes')->completed_at);
        $this->assertDatabaseCount('daily_quest_events', 8);
        $this->assertDatabaseCount('star_rewards', 0);
    }

    public function test_lesson_quest_completes_once_and_claim_is_idempotent_without_changing_xp(): void
    {
        $user = User::factory()->create();
        $service = app(DailyQuestService::class);
        $service->recordLesson($user, 2, now());
        $service->recordLesson($user, 2, now());
        $service->recordLesson($user, 3, now());
        $quest = $service->today($user)->firstWhere('type', 'complete_lesson');
        $this->assertSame(1, $quest->progress);
        $this->assertNotNull($quest->completed_at);
        $this->assertNull($quest->claimed_at);
        $this->assertDatabaseCount('star_rewards', 0);

        $this->actingAs($user)->post(route('daily-quests.claim', ['questId' => $quest->id]))
            ->assertRedirect(route('daily-quests.index'))->assertSessionHasNoErrors();
        $firstClaim = $quest->fresh()->claimed_at->toISOString();
        $this->travel(1)->hours();
        $this->post(route('daily-quests.claim', ['questId' => $quest->id]))
            ->assertRedirect(route('daily-quests.index'))->assertSessionHasNoErrors();

        $this->assertSame($firstClaim, $quest->fresh()->claimed_at->toISOString());
        $this->assertSame(2, app(StarRewardService::class)->totalBonus($user));
        $this->assertSame(0, app(LearningProgress::class)->summary($user)['xp']);
        $this->assertDatabaseCount('star_rewards', 1);
        $this->assertDatabaseCount('daily_quest_events', 1);
        $this->get(route('daily-quests.index'))->assertSee('รับรางวัลแล้ว');
    }

    public function test_incomplete_quest_and_another_users_quest_cannot_be_claimed(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create();
        $service = app(DailyQuestService::class);
        $mine = $service->today($user)->firstWhere('type', 'complete_lesson');
        $service->recordLesson($other, 1, now());
        $theirs = $service->today($other)->firstWhere('type', 'complete_lesson');

        $this->actingAs($user)->post(route('daily-quests.claim', ['questId' => $mine->id]))
            ->assertSessionHasErrors('quest');
        $this->post(route('daily-quests.claim', ['questId' => $theirs->id]))->assertNotFound();
        $this->assertNull($mine->fresh()->claimed_at);
        $this->assertNull($theirs->fresh()->claimed_at);
        $this->assertDatabaseCount('star_rewards', 0);
    }

    public function test_new_day_has_fresh_quests_and_previous_day_reward_cannot_be_claimed(): void
    {
        $user = User::factory()->create();
        $service = app(DailyQuestService::class);
        $service->recordLesson($user, 1, now());
        $old = $service->today($user)->firstWhere('type', 'complete_lesson');
        $this->travel(1)->days();

        $new = $service->today($user);
        $this->assertTrue($new->every(fn ($quest) => $quest->progress === 0
            && $quest->quest_date->toDateString() === '2026-10-12'));
        $this->actingAs($user)->post(route('daily-quests.claim', ['questId' => $old->id]))
            ->assertSessionHasErrors('quest');
        $service->recordLesson($user, 1, now());
        $quest = $service->today($user)->firstWhere('type', 'complete_lesson');
        $service->claim($user, $quest->id);

        $this->assertSame(2, app(StarRewardService::class)->totalBonus($user));
        $this->assertDatabaseCount('daily_quests', 6);
        $this->assertDatabaseCount('daily_quest_events', 2);
        $this->assertNull($old->fresh()->claimed_at);
    }

    public function test_quest_activity_is_scoped_to_the_application_day(): void
    {
        config()->set('app.timezone', 'Asia/Bangkok');
        $user = User::factory()->create();
        $this->travelTo(Carbon::parse('2026-10-11 17:15:00', 'UTC'));
        app(DailyQuestService::class)->recordReview($user, 5, Carbon::parse('2026-10-11 17:01:00', 'UTC'));

        $quest = app(DailyQuestService::class)->today($user)->firstWhere('type', 'review_mistakes');
        $this->assertSame('2026-10-12', $quest->quest_date->toDateString());
        $this->assertSame(1, $quest->progress);
    }

    public function test_future_activity_is_rejected_without_quest_or_event_rows(): void
    {
        $user = User::factory()->create();

        try {
            app(DailyQuestService::class)->recordLesson($user, 1, now()->addDay());
            $this->fail('Future activity must be rejected.');
        } catch (InvalidArgumentException) {
            $this->assertDatabaseCount('daily_quests', 0);
            $this->assertDatabaseCount('daily_quest_events', 0);
        }
    }

    public function test_configured_target_and_reward_are_snapshotted_for_the_day(): void
    {
        config()->set('progression.quests.review_mistakes', ['target' => 2, 'reward_stars' => 7]);
        $user = User::factory()->create();
        $service = app(DailyQuestService::class);
        $service->today($user);
        config()->set('progression.quests.review_mistakes', ['target' => 5, 'reward_stars' => 99]);
        $service->recordReview($user, 1, now());
        $service->recordReview($user, 2, now());
        $quest = $service->today($user)->firstWhere('type', 'review_mistakes');
        $service->claim($user, $quest->id);

        $this->assertSame(2, $quest->target);
        $this->assertSame(7, $quest->reward_stars);
        $this->assertSame(7, app(StarRewardService::class)->totalBonus($user));
        $this->assertSame(0, DailyQuest::where('user_id', '!=', $user->id)->count());
    }
}
