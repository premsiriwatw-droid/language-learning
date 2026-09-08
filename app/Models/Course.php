<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Course extends Model
{
    public function language() {
    return $this->belongsTo(Language::class);
}
    public function units() {
    return $this->hasMany(Unit::class);
}
}
