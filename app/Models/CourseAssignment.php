<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CourseAssignment extends Model
{
    protected $fillable = [
        'teacher_course_id', 'title', 'instructions', 'target_kind',
        'lesson_id', 'unit_id', 'target_label', 'lesson_ids', 'due_at',
    ];

    protected function casts(): array
    {
        return ['lesson_ids' => 'array', 'due_at' => 'datetime'];
    }
}
