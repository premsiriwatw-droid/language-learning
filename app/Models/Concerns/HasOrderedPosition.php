<?php

namespace App\Models\Concerns;

/**
 * Gives a model a `position` column that is always a clean, sequential
 * ordering (1, 2, 3, ...) within whatever "parent" it belongs to
 * (e.g. a Unit's position within its Course, a Lesson's position within
 * its Unit) - so the app never has to fall back to ordering by id.
 *
 * A model using this trait must implement positionGroupColumn() to say
 * which foreign key column defines its siblings.
 */
trait HasOrderedPosition
{
    protected static function bootHasOrderedPosition(): void
    {
        static::creating(function (self $model): void {
            if (is_null($model->position)) {
                $column = $model->positionGroupColumn();

                $max = static::query()
                    ->where($column, $model->{$column})
                    ->max('position');

                $model->position = ($max ?? 0) + 1;
            }
        });
    }

    /**
     * Order a query by position. Relationships that should always come
     * back in lesson/unit order can just add ->ordered() (or define the
     * relation with it baked in, as Course::units() and Unit::lessons() do).
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('position');
    }

    /**
     * Re-number every sibling under $parentId to a clean 1..N sequence,
     * in their current position order. Safe to call after a delete (which
     * leaves a gap) or after a manual reorder.
     *
     * Uses query-builder updates (not Eloquent's mass-assignment-guarded
     * update()) since position is intentionally not fillable - it should
     * only ever move through this method or an explicit reorder action,
     * never through a generic create/update form.
     */
    public static function resequence(int $parentId): void
    {
        $column = (new static)->positionGroupColumn();

        static::query()
            ->where($column, $parentId)
            ->orderBy('position')
            ->orderBy('id')
            ->pluck('id')
            ->values()
            ->each(function ($id, $index): void {
                static::query()->whereKey($id)->update(['position' => $index + 1]);
            });
    }

    /**
     * The column that scopes "siblings" for this model, e.g. 'course_id'
     * for Unit, 'unit_id' for Lesson.
     */
    abstract protected function positionGroupColumn(): string;
}
