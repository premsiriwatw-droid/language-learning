<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ShopItem extends Model
{
    public const TYPES = ['frame', 'background', 'name_color', 'streak_style', 'sticker'];

    protected $fillable = ['sku', 'name', 'type', 'style_key', 'price_stars', 'is_active'];

    protected function casts(): array
    {
        return ['price_stars' => 'integer', 'is_active' => 'boolean'];
    }

    public function appearance(): string
    {
        return config('cosmetics.styles', [])[$this->type][$this->style_key] ?? '';
    }
}
