<?php

namespace Tests\Feature;

use App\Models\LessonProgress;
use App\Models\StarReward;
use App\Models\User;
use App\Services\Progress\DailyQuestService;
use App\Services\Progress\LearningProgress;
use App\Services\Progress\PlayerLevelCalculator;
use App\Services\Progress\StarRewardService;
use App\Services\Progress\StreakService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Schema;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\Support\CreatesContentLesson;
use Tests\TestCase;

class GamificationRewardsStreakTest extends TestCase
{
    use CreatesContentLesson;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config()->set('app.timezone', 'UTC');
        config()->set('progression.stars', ['milestone_lessons' => 5, 'milestone_reward' => 10]);
        $this->travelTo(Carbon::parse('2026-10-11 12:00:00', 'UTC'));
    }

    protected function tearDown(): void
    {
        $this->travelBack();
        parent::tearDown();
    }

    public function test_bonus_stars_are_idempotent_and_user_scoped_without_writing_xp(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create();
        $rewards = app(StarRewardService::class);

        $first = $rewards->add($user, 'milestone:5', 10);
        $duplicate = $rewards->add($user, 'milestone:5', 999);
        $rewards->add($other, 'milestone:5', 20);

        $this->assertSame($first->id, $duplicate->id);
        $this->assertSame(10, $duplicate->stars);
        $this->assertSame(10, $rewards->totalBonus($user));
        $this->assertSame(20, $rewards->totalBonus($other));
        $this->assertDatabaseCount('star_rewards', 2);
        $this->assertDatabaseCount('lesson_progress', 0);
        $this->assertSame(0, app(LearningProgress::class)->summary($user)['xp']);
    }

    public function test_first_completion_and_milestone_reuse_original_stars_and_replay_preserves_xp(): void
    {
        config()->set('progression.stars.milestone_lessons', 2);
        config()->set('learning.levels.xp_per_level', 200);
        $user = User::factory()->create();
        $other = User::factory()->create();
        $first = $this->createContentLesson();
        $second = $this->createContentLesson();
        $progress = app(LearningProgress::class);

        $progress->complete($user, $first, 180, 1);
        $this->assertSame(1, $progress->summary($user)['stars']);
        $this->assertDatabaseCount('star_rewards', 0);

        $progress->complete($user, $second, 30, 3);
        $this->assertSame(14, $progress->summary($user)['stars']);
        $this->assertSame(210, $progress->summary($user)['xp']);
        $this->assertSame(2, app(PlayerLevelCalculator::class)->calculate(210)['level']);
        $this->assertSame(10, app(StarRewardService::class)->totalBonus($user));

        $progress->complete($user, $first, 9999, 99);
        $progress->complete($user, $second, 9999, 99);
        app(StarRewardService::class)->milestone($user);

        $this->assertSame(14, $progress->summary($user)['stars']);
        $this->assertSame(210, $progress->summary($user)['xp']);
        $this->assertSame(1, LessonProgress::where('user_id', $user->id)->where('lesson_id', $first->id)->sole()->stars);
        $this->assertDatabaseCount('star_rewards', 1);
        $this->assertSame(0, app(StarRewardService::class)->totalBonus($other));
    }

    #[DataProvider('invalidRewards')]
    public function test_invalid_bonus_rewards_do_not_write_rows(string $key, int $stars): void
    {
        $user = User::factory()->create();

        try {
            app(StarRewardService::class)->add($user, $key, $stars);
            $this->fail('Invalid rewards must be rejected.');
        } catch (InvalidArgumentException) {
            $this->assertDatabaseCount('star_rewards', 0);
        }
    }

    public static function invalidRewards(): array
    {
        return [['valid', -1], ['valid', 0], ['', 1], [str_repeat('x', 192), 1]];
    }

    public function test_bonus_summary_is_safe_before_the_reward_migration_is_available(): void
    {
        $user = User::factory()->create();
        Schema::drop('star_rewards');

        $this->assertSame(0, app(StarRewardService::class)->totalBonus($user));
    }

    public function test_streak_starts_once_and_consecutive_days_increment(): void
    {
        $user = User::factory()->create();
        $streak = app(StreakService::class);

        $streak->record($user, now());
        $streak->record($user, now());
        $this->assertSame(1, $user->fresh()->current_streak);
        $this->assertSame(1, $user->fresh()->longest_streak);
        $this->assertSame('2026-10-11', $user->fresh()->last_active_date->toDateString());

        $this->travel(1)->days();
        $streak->record($user, now());
        $streak->record($user, now()->subHours(3));
        $this->assertSame(2, $user->fresh()->current_streak);
        $this->assertSame(2, $user->fresh()->longest_streak);
    }

    public function test_gap_resets_current_streak_and_keeps_longest_and_older_events_are_ignored(): void
    {
        $user = User::factory()->create();
        $streak = app(StreakService::class);
        $streak->record($user, now());
        $this->travel(1)->days();
        $streak->record($user, now());
        $this->travel(2)->days();
        $streak->record($user, now());
        $streak->record($user, now()->subDays(3));

        $this->assertSame(1, $user->fresh()->current_streak);
        $this->assertSame(2, $user->fresh()->longest_streak);
        $this->assertSame('2026-10-14', $user->fresh()->last_active_date->toDateString());
    }

    public function test_streak_uses_application_timezone_at_midnight(): void
    {
        config()->set('app.timezone', 'Asia/Bangkok');
        $user = User::factory()->create();
        $this->travelTo(Carbon::parse('2026-10-11 17:15:00', 'UTC'));

        app(StreakService::class)->record($user, Carbon::parse('2026-10-11 16:59:00', 'UTC'));
        app(StreakService::class)->record($user, Carbon::parse('2026-10-11 17:01:00', 'UTC'));

        $this->assertSame(2, $user->fresh()->current_streak);
        $this->assertSame('2026-10-12', $user->fresh()->last_active_date->toDateString());
    }

    public function test_future_streak_events_are_rejected_without_changing_user(): void
    {
        $user = User::factory()->create();

        try {
            app(StreakService::class)->record($user, now()->addDay());
            $this->fail('Future activity must be rejected.');
        } catch (InvalidArgumentException) {
            $this->assertSame(0, $user->fresh()->current_streak);
            $this->assertNull($user->fresh()->last_active_date);
        }
    }

    #[DataProvider('nonLearnerRoles')]
    public function test_admin_and_teacher_activity_does_not_change_progression(string $role): void
    {
        $user = User::factory()->create(['role' => $role]);
        app(StreakService::class)->record($user, now());
        app(DailyQuestService::class)->recordLesson($user, 1, now());
        $this->assertNull(app(StarRewardService::class)->milestone($user));
        $this->assertSame(0, $user->fresh()->current_streak);
        $this->assertDatabaseCount('daily_quests', 0);
        $this->assertDatabaseCount('star_rewards', 0);

        $this->expectException(AuthorizationException::class);
        app(StarRewardService::class)->add($user, 'test', 1);
    }

    public static function nonLearnerRoles(): array
    {
        return [['admin'], ['teacher']];
    }

    public function test_explicit_learner_role_can_receive_bonus_rewards(): void
    {
        $user = User::factory()->create(['role' => 'learner']);
        app(StarRewardService::class)->add($user, 'test', 2);

        $this->assertSame(2, StarReward::where('user_id', $user->id)->sum('stars'));
    }
}
