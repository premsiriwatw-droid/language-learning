<?php

namespace Tests\Feature\Notifications;

use App\Enums\NotificationTopic;
use App\Events\Notifications\UserNotificationRequested;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use Tests\TestCase;

class NotificationHubTest extends TestCase
{
    use RefreshDatabase;

    private function send(User $user, string $key = 'test', NotificationTopic $topic = NotificationTopic::Achievement): void
    {
        UserNotificationRequested::dispatch($user->id, $topic, $key, 'แจ้งเตือนทดสอบ', 'ข้อความ <script>alert(1)</script>', '/profile');
    }

    public function test_every_topic_is_supported_and_replayed_events_preserve_read_state(): void
    {
        $user = User::factory()->create();
        foreach (NotificationTopic::cases() as $topic) {
            $this->send($user, 'source:42', $topic);
        }
        $this->assertSame(count(NotificationTopic::cases()), $user->notifications()->count());
        $notification = $user->notifications()->where('data->topic', 'achievement')->sole();
        $notification->markAsRead();
        $this->send($user, 'source:42');
        $this->assertSame(count(NotificationTopic::cases()), $user->notifications()->count());
        $this->assertNotNull($notification->fresh()->read_at);
    }

    public function test_inbox_is_private_and_read_unread_all_and_filter_work(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();
        $this->send($owner);
        $this->send($other);
        $id = $owner->notifications()->sole()->id;
        $this->get(route('notifications.index'))->assertRedirect(route('login'));
        $this->actingAs($other)->patch(route('notifications.read', $id))->assertNotFound();
        $this->patch(route('notifications.unread', $id))->assertNotFound();
        $this->actingAs($owner)->get(route('notifications.index'))->assertOk()
            ->assertSee('&lt;script&gt;alert(1)&lt;/script&gt;', false)->assertDontSee('<script>alert(1)</script>', false);
        $this->assertNull($owner->notifications()->sole()->read_at);
        $this->patch(route('notifications.read', $id))->assertRedirect();
        $this->get(route('notifications.index', ['filter' => 'unread']))->assertDontSee('แจ้งเตือนทดสอบ');
        $this->patch(route('notifications.unread', $id))->assertRedirect();
        $this->get(route('notifications.index', ['filter' => 'unread']))->assertSee('แจ้งเตือนทดสอบ');
        $this->patch(route('notifications.read-all'))->assertRedirect();
        $this->assertSame(0, $owner->unreadNotifications()->count());
        $this->assertSame(1, $other->unreadNotifications()->count());
    }

    public function test_rolled_back_events_do_not_create_ghost_notifications(): void
    {
        $user = User::factory()->create();
        DB::beginTransaction();
        $this->send($user);
        $this->assertDatabaseCount('notifications', 0);
        DB::rollBack();
        $this->assertDatabaseCount('notifications', 0);
        DB::transaction(fn () => $this->send($user));
        $this->assertDatabaseCount('notifications', 1);
    }

    public function test_external_and_unsafe_destinations_are_rejected(): void
    {
        foreach (['https://example.com', '//example.com', '/\\example.com', "/path\nheader"] as $path) {
            try {
                new UserNotificationRequested(1, NotificationTopic::TeacherReply, 'key', 'title', 'body', $path);
                $this->fail('Unsafe destination accepted');
            } catch (InvalidArgumentException) {
                $this->assertTrue(true);
            }
        }
    }
}
