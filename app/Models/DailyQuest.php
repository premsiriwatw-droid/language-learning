<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

class DailyQuest extends Model
{
    protected $fillable = [
        'user_id', 'quest_date', 'type', 'target', 'progress',
        'reward_stars', 'completed_at', 'claimed_at',
    ];

    protected function casts(): array
    {
        return [
            'quest_date' => 'date',
            'target' => 'integer',
            'progress' => 'integer',
            'reward_stars' => 'integer',
            'completed_at' => 'datetime',
            'claimed_at' => 'datetime',
        ];
    }

    protected function questDate(): Attribute
    {
        return Attribute::make(set: fn ($value) => Carbon::parse($value)->toDateString());
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
