<?php

namespace Tests\Support;

use App\Models\Course;
use App\Models\Language;
use App\Models\Lesson;
use App\Models\Unit;

trait CreatesContentLesson
{
    private function createContentLesson(): Lesson
    {
        $language = Language::create(['name' => 'Test language']);
        $course = Course::forceCreate(['language_id' => $language->id, 'title' => 'Test course']);
        $unit = Unit::forceCreate(['course_id' => $course->id, 'title' => 'Test unit']);

        return Lesson::forceCreate(['unit_id' => $unit->id, 'title' => 'Test lesson']);
    }
}
