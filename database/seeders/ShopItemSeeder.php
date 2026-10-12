<?php

namespace Database\Seeders;

use App\Models\ShopItem;
use Illuminate\Database\Seeder;

class ShopItemSeeder extends Seeder
{
    public function run(): void
    {
        $catalog = [
            ['frame', 'gold', 'กรอบแสงทอง', 30], ['frame', 'ocean', 'กรอบมหาสมุทร', 25],
            ['background', 'sunrise', 'พื้นหลังรุ่งอรุณ', 40], ['background', 'forest', 'พื้นหลังป่าเขียว', 40],
            ['name_color', 'ruby', 'ชื่อสีทับทิม', 15], ['name_color', 'indigo', 'ชื่อสีคราม', 15],
            ['streak_style', 'flame', 'Streak เปลวไฟ', 20], ['streak_style', 'sparkle', 'Streak ประกายม่วง', 20],
            ['sticker', 'star', 'สติกเกอร์ดาว', 10], ['sticker', 'panda', 'สติกเกอร์แพนด้า', 10],
        ];
        foreach ($catalog as [$type, $style, $name, $price]) {
            ShopItem::firstOrCreate(['sku' => $type.'-'.$style], [
                'type' => $type, 'style_key' => $style, 'name' => $name,
                'price_stars' => $price, 'is_active' => true,
            ]);
        }
    }
}
