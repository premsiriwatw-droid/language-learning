<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CourseConversation extends Model
{
    protected $fillable = ['teacher_course_id', 'student_id'];
}
