<?php

namespace Database\Factories;

use App\Models\Course;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Course>
 */
class CourseFactory extends Factory
{
    public function definition(): array
    {
        return [
            // Supply a parent Language with for($language) or language_id.
            'language_id' => null,
            'title' => fake()->words(2, true).' Beginner',
        ];
    }
}
