<?php

namespace Database\Factories;

use App\Models\TeacherCourse;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TeacherCourse>
 */
class TeacherCourseFactory extends Factory
{
    public function definition(): array
    {
        return [
            // Supply teacher_id and language_id explicitly.
            'teacher_id' => null,
            'language_id' => null,
            'title' => fake()->words(3, true),
            'description' => fake()->sentence(12),
            'level' => 'beginner',
            'price_type' => TeacherCourse::FREE,
            'price' => null,
            'status' => TeacherCourse::PUBLISHED,
            'enrollment_mode' => TeacherCourse::INSTANT,
            'published_at' => now(),
        ];
    }

    public function draft(): static
    {
        return $this->state(fn () => ['status' => TeacherCourse::DRAFT, 'published_at' => null]);
    }

    public function closed(): static
    {
        return $this->state(fn () => ['status' => TeacherCourse::CLOSED]);
    }

    public function paid(float $price = 299): static
    {
        return $this->state(fn () => ['price_type' => TeacherCourse::PAID, 'price' => $price]);
    }

    public function approvalRequired(): static
    {
        return $this->state(fn () => ['enrollment_mode' => TeacherCourse::APPROVAL_REQUIRED]);
    }
}
