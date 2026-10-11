<?php

namespace Tests\Support;

use App\Models\Course;
use App\Models\Language;
use App\Models\Lesson;
use App\Models\TeacherApplication;
use App\Models\TeacherCourse;
use App\Models\Unit;
use App\Models\User;

trait CreatesTeacherCourses
{
    /** A teacher that satisfies the shared contract User::isApprovedTeacher(). */
    private function approvedTeacher(array $attributes = []): User
    {
        $teacher = User::factory()->create(['role' => 'teacher'] + $attributes);

        TeacherApplication::forceCreate([
            'user_id' => $teacher->id, 'full_name' => $teacher->name.' (Verified)',
            'bio' => 'Teaching profile', 'teaching_experience' => 'Two years',
            'teaching_languages' => ['English'], 'status' => TeacherApplication::APPROVED,
            'submitted_at' => now(), 'approved_at' => now(),
        ]);

        return $teacher;
    }

    private function pendingTeacher(): User
    {
        $user = User::factory()->create(['role' => 'user']);

        TeacherApplication::forceCreate([
            'user_id' => $user->id, 'full_name' => $user->name,
            'bio' => 'Teaching profile', 'teaching_experience' => 'Two years',
            'teaching_languages' => ['English'], 'status' => TeacherApplication::PENDING,
            'submitted_at' => now(),
        ]);

        return $user;
    }

    private function learner(): User
    {
        return User::factory()->create(['role' => 'user']);
    }

    /** @return array{0: Language, 1: list<Lesson>} */
    private function languageWithLessons(string $name = 'English', int $count = 2): array
    {
        $language = Language::factory()->create(['name' => $name]);
        $course = Course::factory()->for($language)->create();
        $unit = Unit::factory()->for($course)->create();
        $lessons = Lesson::factory()->for($unit)->count($count)->create()->all();

        return [$language, $lessons];
    }

    private function teacherCourse(User $teacher, ?Language $language = null, array $attributes = [], ?callable $state = null): TeacherCourse
    {
        if ($language === null) {
            [$language, $lessons] = $this->languageWithLessons('Lang '.uniqid());
        } else {
            $lessons = Lesson::whereHas('unit.course', fn ($q) => $q->where('language_id', $language->id))->get()->all();
        }

        $factory = TeacherCourse::factory();
        if ($state) {
            $factory = $state($factory);
        }

        $course = $factory->create(['teacher_id' => $teacher->id, 'language_id' => $language->id] + $attributes);
        $course->lessons()->sync(collect($lessons)->values()->mapWithKeys(fn ($l, $i) => [$l->id => ['position' => $i + 1]])->all());

        return $course;
    }
}
