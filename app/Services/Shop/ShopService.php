<?php

namespace App\Services\Shop;

use App\Models\Purchase;
use App\Models\ShopItem;
use App\Models\User;
use App\Models\UserEquippedItem;
use App\Models\UserItem;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ShopService
{
    public function __construct(private StarWallet $wallet) {}

    public function purchase(User $user, int $itemId): UserItem
    {
        return DB::transaction(function () use ($user, $itemId): UserItem {
            // Same account lock as lesson rewards and quest claims, before any balance read.
            User::whereKey($user->id)->lockForUpdate()->firstOrFail();
            $item = ShopItem::whereKey($itemId)->lockForUpdate()->firstOrFail();
            $owned = UserItem::where('user_id', $user->id)->where('shop_item_id', $item->id)->first();
            if ($owned) {
                return $owned;
            }
            if (! $item->is_active || ! in_array($item->type, ShopItem::TYPES, true) || $item->appearance() === '' || $item->price_stars < 0) {
                throw ValidationException::withMessages(['item' => 'สินค้านี้ยังไม่พร้อมจำหน่าย']);
            }
            if ($this->wallet->summary($user)['balance'] < $item->price_stars) {
                throw ValidationException::withMessages(['item' => 'ดาวไม่เพียงพอ ลองเรียนหรือทำภารกิจเพิ่มก่อนนะ']);
            }
            Purchase::create(['user_id' => $user->id, 'shop_item_id' => $item->id, 'price_stars' => $item->price_stars]);

            return UserItem::create(['user_id' => $user->id, 'shop_item_id' => $item->id]);
        }, 5);
    }

    public function equip(User $user, int $inventoryId): void
    {
        DB::transaction(function () use ($user, $inventoryId): void {
            User::whereKey($user->id)->lockForUpdate()->firstOrFail();
            $owned = UserItem::with('shopItem')->where('user_id', $user->id)->whereKey($inventoryId)->firstOrFail();
            $item = $owned->shopItem;
            abort_unless(in_array($item->type, ShopItem::TYPES, true) && $item->appearance() !== '', 422);
            UserEquippedItem::updateOrCreate(
                ['user_id' => $user->id, 'type' => $item->type],
                ['user_item_id' => $owned->id],
            );
        }, 5);
    }

    public function unequip(User $user, string $type): void
    {
        abort_unless(in_array($type, ShopItem::TYPES, true), 404);
        DB::transaction(function () use ($user, $type): void {
            User::whereKey($user->id)->lockForUpdate()->firstOrFail();
            UserEquippedItem::where('user_id', $user->id)->where('type', $type)->delete();
        }, 5);
    }

    /** @return array<string, string> */
    public function cosmetics(User $user): array
    {
        return UserEquippedItem::with('userItem.shopItem')->where('user_id', $user->id)->get()
            ->filter(fn (UserEquippedItem $slot) => $slot->userItem?->user_id === $user->id
                && $slot->userItem->shopItem?->type === $slot->type)
            ->mapWithKeys(fn (UserEquippedItem $slot) => [$slot->type => $slot->userItem->shopItem->appearance()])->all();
    }
}
