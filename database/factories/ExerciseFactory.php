<?php

namespace Database\Factories;

use App\Models\Exercise;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Exercise> */
class ExerciseFactory extends Factory
{
    public function definition(): array
    {
        return [
            'lesson_id' => fake()->numberBetween(1, 1000),
            'type' => fake()->randomElement(['multiple_choice', 'fill_blank', 'translation', 'arrange_words']),
            'title' => fake()->sentence(),
        ];
    }

    public function fillBlank(): static
    {
        return $this->state(fn () => [
            'type' => 'fill_blank',
            'title' => 'Fill in the Blank',
        ]);
    }

    public function listening(): static
    {
        return $this->state(fn () => [
            'type' => 'listening',
            'title' => 'Listening Practice',
        ]);
    }

    public function multipleChoice(): static
    {
        return $this->state(fn () => [
            'type' => 'multiple_choice',
            'title' => 'Multiple Choice',
        ]);
    }

    public function imageChoice(): static
    {
        return $this->state(fn () => [
            'type' => 'image_choice',
            'title' => 'Image Choice',
        ]);
    }
}
