<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Support\Carbon;

class DailyQuestEvent extends Model
{
    protected $fillable = ['user_id', 'quest_date', 'type', 'event_key'];

    protected function casts(): array
    {
        return ['quest_date' => 'date'];
    }

    protected function questDate(): Attribute
    {
        return Attribute::make(set: fn ($value) => Carbon::parse($value)->toDateString());
    }
}
