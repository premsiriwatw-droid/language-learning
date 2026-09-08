<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Lesson extends Model
{
    public function unit()
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
}
