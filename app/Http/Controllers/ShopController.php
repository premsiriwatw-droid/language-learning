<?php

namespace App\Http\Controllers;

use App\Models\Purchase;
use App\Models\ShopItem;
use App\Models\UserEquippedItem;
use App\Models\UserItem;
use App\Services\Shop\ShopService;
use App\Services\Shop\StarWallet;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ShopController extends Controller
{
    public function index(Request $request, StarWallet $wallet): View
    {
        return view('shop.index', [
            'items' => ShopItem::where('is_active', true)->orderBy('type')->orderBy('price_stars')->get(),
            'ownedIds' => UserItem::where('user_id', $request->user()->id)->pluck('shop_item_id')->all(),
            'wallet' => $wallet->summary($request->user()),
        ]);
    }

    public function purchase(Request $request, int $item, ShopService $shop): RedirectResponse
    {
        $shop->purchase($request->user(), $item);

        return to_route('inventory.index')->with('status', 'ไอเทมอยู่ในคลังของคุณแล้ว เลือกใส่ได้เลย');
    }

    public function inventory(Request $request, StarWallet $wallet): View
    {
        return view('shop.inventory', [
            'items' => UserItem::with('shopItem')->where('user_id', $request->user()->id)->latest()->get(),
            'equippedIds' => UserEquippedItem::where('user_id', $request->user()->id)->pluck('user_item_id')->all(),
            'purchases' => Purchase::with('shopItem')->where('user_id', $request->user()->id)->latest('id')->paginate(15),
            'wallet' => $wallet->summary($request->user()),
        ]);
    }

    public function equip(Request $request, int $inventory, ShopService $shop): RedirectResponse
    {
        $shop->equip($request->user(), $inventory);

        return to_route('inventory.index')->with('status', 'อัปเดตไอเทมบนโปรไฟล์แล้ว');
    }

    public function unequip(Request $request, string $type, ShopService $shop): RedirectResponse
    {
        $shop->unequip($request->user(), $type);

        return to_route('inventory.index')->with('status', 'ถอดไอเทมแล้ว');
    }
}
