<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StarReward extends Model
{
    protected $fillable = ['user_id', 'source_key', 'stars'];

    protected function casts(): array
    {
        return ['stars' => 'integer'];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
