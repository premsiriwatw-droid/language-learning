<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserItem extends Model
{
    protected $fillable = ['user_id', 'shop_item_id'];

    public function shopItem(): BelongsTo
    {
        return $this->belongsTo(ShopItem::class);
    }
}
