<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserEquippedItem extends Model
{
    protected $fillable = ['user_id', 'user_item_id', 'type'];

    public function userItem(): BelongsTo
    {
        return $this->belongsTo(UserItem::class);
    }
}
