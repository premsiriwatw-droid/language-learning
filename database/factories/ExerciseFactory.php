<?php

namespace Database\Factories;

use App\Models\Exercise;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Exercise>
 */
class ExerciseFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            // Supply an existing Lesson with for($lesson) or lesson_id.
            'lesson_id' => null,
            'type' => fake()->randomElement(['multiple_choice', 'fill_blank', 'translation', 'arrange_words']),
            'title' => fake()->sentence(),
        ];
    }
}
