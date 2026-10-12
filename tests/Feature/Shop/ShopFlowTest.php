<?php

namespace Tests\Feature\Shop;

use App\Models\LessonProgress;
use App\Models\Purchase;
use App\Models\ShopItem;
use App\Models\User;
use App\Models\UserItem;
use App\Services\Progress\LearningProgress;
use App\Services\Progress\StarRewardService;
use App\Services\Shop\ShopService;
use App\Services\Shop\StarWallet;
use Database\Seeders\ShopItemSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use RuntimeException;
use Tests\Support\CreatesContentLesson;
use Tests\TestCase;

class ShopFlowTest extends TestCase
{
    use CreatesContentLesson, RefreshDatabase;

    private function item(string $type = 'frame', string $style = 'gold', int $price = 30): ShopItem
    {
        return ShopItem::create(['sku' => $type.'-'.$style, 'name' => 'Demo '.$style, 'type' => $type,
            'style_key' => $style, 'price_stars' => $price, 'is_active' => true]);
    }

    public function test_buy_uses_real_earned_stars_ignores_forged_price_and_preserves_progress(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create();
        $lesson = $this->createContentLesson();
        app(LearningProgress::class)->complete($user, $lesson, 100, 20);
        app(StarRewardService::class)->add($user, 'fixture:bonus', 20);
        $item = $this->item();
        $this->actingAs($user)->post(route('shop.purchase', $item->id), [
            'price_stars' => 0, 'user_id' => $other->id, 'balance' => 9999,
        ])->assertRedirect(route('inventory.index'))->assertSessionHasNoErrors();
        $this->assertSame(['earned' => 40, 'spent' => 30, 'balance' => 10], app(StarWallet::class)->summary($user));
        $this->assertDatabaseHas('purchases', ['user_id' => $user->id, 'price_stars' => 30]);
        $this->assertDatabaseHas('lesson_progress', ['user_id' => $user->id, 'xp' => 100, 'stars' => 20]);
        $this->assertDatabaseMissing('user_items', ['user_id' => $other->id]);
        $this->get(route('profile'))->assertOk()->assertSee('พร้อมใช้ 10 ดวง')->assertSee('ใช้ไป 30');
    }

    public function test_double_submit_is_idempotent_and_new_item_cannot_overdraw(): void
    {
        $user = User::factory()->create();
        app(StarRewardService::class)->add($user, 'fixture', 40);
        $first = $this->item();
        $second = $this->item('frame', 'ocean');
        $this->actingAs($user);
        $this->post(route('shop.purchase', $first->id))->assertSessionHasNoErrors();
        $this->post(route('shop.purchase', $first->id))->assertSessionHasNoErrors();
        $this->post(route('shop.purchase', $second->id))->assertSessionHasErrors('item');
        $this->assertDatabaseCount('purchases', 1);
        $this->assertDatabaseCount('user_items', 1);
        $this->assertSame(10, app(StarWallet::class)->summary($user)['balance']);
    }

    public function test_inventory_failure_rolls_back_the_debit(): void
    {
        $user = User::factory()->create();
        app(StarRewardService::class)->add($user, 'fixture', 40);
        $item = $this->item();
        UserItem::creating(fn () => throw new RuntimeException('Inventory unavailable'));
        try {
            app(ShopService::class)->purchase($user, $item->id);
            $this->fail('Expected inventory failure');
        } catch (RuntimeException $exception) {
            $this->assertSame('Inventory unavailable', $exception->getMessage());
        } finally {
            UserItem::flushEventListeners();
        }
        $this->assertDatabaseCount('purchases', 0);
        $this->assertDatabaseCount('user_items', 0);
        $this->assertSame(40, app(StarWallet::class)->summary($user)['balance']);
    }

    public function test_guest_inactive_invalid_and_unaffordable_items_are_rejected(): void
    {
        $item = $this->item();
        $this->get(route('shop.index'))->assertRedirect(route('login'));
        $this->post(route('shop.purchase', $item->id))->assertRedirect(route('login'));
        $this->actingAs(User::factory()->create());
        $this->post(route('shop.purchase', $item->id))->assertSessionHasErrors('item');
        $item->update(['is_active' => false, 'price_stars' => 0]);
        $this->post(route('shop.purchase', $item->id))->assertSessionHasErrors('item');
        $item->update(['is_active' => true, 'style_key' => 'unknown']);
        $this->post(route('shop.purchase', $item->id))->assertSessionHasErrors('item');
        $this->post(route('shop.purchase', 999999))->assertNotFound();
        $this->assertDatabaseCount('purchases', 0);
    }

    public function test_all_five_cosmetics_render_and_can_be_swapped_and_unequipped(): void
    {
        $this->seed(ShopItemSeeder::class);
        $user = User::factory()->create();
        app(StarRewardService::class)->add($user, 'fixture', 500);
        $this->actingAs($user);
        foreach (['frame-gold', 'background-sunrise', 'name_color-ruby', 'streak_style-flame', 'sticker-panda'] as $sku) {
            $item = ShopItem::where('sku', $sku)->sole();
            $this->post(route('shop.purchase', $item->id))->assertSessionHasNoErrors();
            $owned = UserItem::where('shop_item_id', $item->id)->sole();
            $this->put(route('inventory.equip', $owned->id))->assertSessionHasNoErrors();
        }
        $this->get(route('profile'))->assertOk()->assertSee('cosmetic-frame-gold')->assertSee('cosmetic-background-sunrise')
            ->assertSee('cosmetic-name-ruby')->assertSee('cosmetic-streak-flame')->assertSee('🐼');
        $this->assertDatabaseCount('user_equipped_items', 5);
        $item = ShopItem::where('sku', 'frame-ocean')->sole();
        $owned = app(ShopService::class)->purchase($user, $item->id);
        $this->put(route('inventory.equip', $owned->id))->assertSessionHasNoErrors();
        $this->assertDatabaseCount('user_equipped_items', 5);
        $this->get(route('profile'))->assertSee('cosmetic-frame-ocean')->assertDontSee('cosmetic-frame-gold');
        $this->delete(route('inventory.unequip', 'frame'))->assertRedirect();
        $this->get(route('profile'))->assertDontSee('cosmetic-frame-ocean');
    }

    public function test_other_users_inventory_is_private_and_unlisted_owned_items_stay_usable(): void
    {
        $owner = User::factory()->create();
        app(StarRewardService::class)->add($owner, 'fixture', 30);
        $item = $this->item();
        $owned = app(ShopService::class)->purchase($owner, $item->id);
        $item->update(['is_active' => false, 'price_stars' => 999]);
        $this->actingAs(User::factory()->create())->put(route('inventory.equip', $owned->id))->assertNotFound();
        $this->get(route('inventory.index'))->assertDontSee('Demo gold');
        $this->actingAs($owner)->put(route('inventory.equip', $owned->id))->assertSessionHasNoErrors();
        $this->assertSame(30, Purchase::sole()->price_stars);
        $this->assertSame(0, app(StarWallet::class)->summary($owner)['balance']);
        $this->get(route('inventory.index'))->assertOk()->assertSee('Demo gold')->assertSee('30 ★');
    }

    public function test_uncompleted_lesson_stars_are_not_spendable_and_seed_is_repeatable(): void
    {
        $user = User::factory()->create();
        LessonProgress::create(['user_id' => $user->id, 'lesson_id' => $this->createContentLesson()->id, 'stars' => 100]);
        $this->assertSame(0, app(StarWallet::class)->summary($user)['balance']);
        $this->seed(ShopItemSeeder::class);
        ShopItem::first()->update(['price_stars' => 55, 'is_active' => false]);
        $this->seed(ShopItemSeeder::class);
        $this->assertDatabaseCount('shop_items', 10);
        $this->assertSame(55, ShopItem::first()->price_stars);
        $this->assertFalse(ShopItem::first()->is_active);
    }
}
