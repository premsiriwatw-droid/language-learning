<?php

namespace App\Models;

use App\Models\Concerns\HasOrderedPosition;
use Database\Factories\LessonFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Lesson extends Model
{
    /** @use HasFactory<LessonFactory> */
    use HasFactory;
    use HasOrderedPosition;

    protected $fillable = ['title', 'content'];

    /** @return BelongsTo<Unit, $this> */
    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }

    /** @return HasMany<Vocabulary, $this> */
    public function vocabularies(): HasMany
    {
        return $this->hasMany(Vocabulary::class);
    }

    /** @return HasMany<Exercise, $this> */
    public function exercises(): HasMany
    {
        return $this->hasMany(Exercise::class);
    }

    protected function positionGroupColumn(): string
    {
        return 'unit_id';
    }

    /**
     * The next Lesson in this Unit, in lesson-plan order.
     */
    public function nextLesson(): ?self
    {
        return static::query()
            ->where('unit_id', $this->unit_id)
            ->where('position', '>', $this->position)
            ->orderBy('position')
            ->first();
    }

    /**
     * The Lesson immediately before this one in the same Unit, if any.
     */
    public function previousLesson(): ?self
    {
        return static::query()
            ->where('unit_id', $this->unit_id)
            ->where('position', '<', $this->position)
            ->orderByDesc('position')
            ->first();
    }
}
