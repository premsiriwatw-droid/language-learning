<?php

namespace App\Models;

use App\Models\Concerns\HasOrderedPosition;
use Database\Factories\UnitFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Unit extends Model
{
    /** @use HasFactory<UnitFactory> */
    use HasFactory;
    use HasOrderedPosition;

    protected $fillable = ['title'];

    /** @return BelongsTo<Course, $this> */
    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    /**
     * Lessons are always returned in their lesson-plan order (position),
     * never raw id order, so nothing downstream has to remember to sort.
     *
     * @return HasMany<Lesson, $this>
     */
    public function lessons(): HasMany
    {
        return $this->hasMany(Lesson::class)->orderBy('position')->orderBy('id');
    }

    protected function positionGroupColumn(): string
    {
        return 'course_id';
    }

    /**
     * The next Unit in this Course, in lesson-plan order. Progress/Unlock
     * (person 5) can use this to find what to unlock once the learner
     * clears the current Unit.
     */
    public function nextUnit(): ?self
    {
        return $this->nextSibling();
    }

    /**
     * The Unit immediately before this one in the same Course, if any.
     */
    public function previousUnit(): ?self
    {
        return $this->previousSibling();
    }
}
