<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LessonProgress extends Model
{
    protected $table = 'lesson_progress';

    protected $fillable = ['user_id', 'lesson_id', 'last_step', 'last_visited_at', 'completed_at', 'xp', 'stars'];

    protected function casts(): array
    {
        return [
            'last_step' => 'integer',
            'last_visited_at' => 'datetime',
            'completed_at' => 'datetime',
            'xp' => 'integer',
            'stars' => 'integer',
        ];
    }

    public function lesson(): BelongsTo
    {
        return $this->belongsTo(Lesson::class);
    }
}
