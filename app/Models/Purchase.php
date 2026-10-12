<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Purchase extends Model
{
    protected $fillable = ['user_id', 'shop_item_id', 'price_stars'];

    protected function casts(): array
    {
        return ['price_stars' => 'integer'];
    }

    public function shopItem(): BelongsTo
    {
        return $this->belongsTo(ShopItem::class);
    }
}
