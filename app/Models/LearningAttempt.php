<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LearningAttempt extends Model
{
    protected $fillable = [
        'user_id', 'lesson_id', 'run_key', 'lesson_title', 'language_name',
        'vocabulary_count', 'question_count', 'correct_count', 'wrong_count',
        'wrong_attempts', 'score_percent', 'elapsed_seconds', 'started_at',
        'completed_at', 'results',
    ];

    protected function casts(): array
    {
        return [
            'vocabulary_count' => 'integer', 'question_count' => 'integer',
            'correct_count' => 'integer', 'wrong_count' => 'integer',
            'wrong_attempts' => 'integer', 'elapsed_seconds' => 'integer',
            'score_percent' => 'float', 'results' => 'array',
            'started_at' => 'datetime', 'completed_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function lesson(): BelongsTo
    {
        return $this->belongsTo(Lesson::class);
    }
}
