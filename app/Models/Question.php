<?php

namespace App\Models;

use Database\Factories\QuestionFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'exercise_id',
    'question',
    'explanation',
    'audio_path',
    'image_path',
])]
class Question extends Model
{
    /** @use HasFactory<QuestionFactory> */
    use HasFactory;

    public function exercise(): BelongsTo
    {
        return $this->belongsTo(Exercise::class);
    }

    public function answers(): HasMany
    {
        return $this->hasMany(Answer::class);
    }

    // คำศัพท์ที่ต้องเรียนก่อนทำคำถามนี้
    public function vocabularies(): BelongsToMany
    {
        return $this->belongsToMany(
            Vocabulary::class,
            'question_vocabulary'
        );
    }
}