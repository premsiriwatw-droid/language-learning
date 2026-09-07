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
            'lesson_id' => fake()->numberBetween(1, 1000),
            'type' => fake()->randomElement(['multiple_choice', 'fill_blank', 'translation', 'arrange_words']),
            'title' => fake()->sentence(),
        ];
    }
}
