<?php

namespace Tests\Support;

use App\Models\Course;
use App\Models\Language;
use App\Models\Lesson;
use App\Models\Unit;

trait CreatesLearningStructure
{
    private function createLanguageWithCourse(): Course
    {
        $language = Language::factory()->create();

        return Course::factory()->for($language)->create();
    }

    private function createCourseWithUnit(): Unit
    {
        $course = $this->createLanguageWithCourse();

        return Unit::factory()->for($course)->create();
    }

    private function createUnitWithLesson(): Lesson
    {
        $unit = $this->createCourseWithUnit();

        return Lesson::factory()->for($unit)->create();
    }
}
