<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TeacherRecommendation extends Model
{
    protected $fillable = [
        'teacher_course_id', 'student_id', 'lesson_id', 'lesson_title', 'reason',
    ];

    public function lesson(): BelongsTo
    {
        return $this->belongsTo(Lesson::class);
    }
}
