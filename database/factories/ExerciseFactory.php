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
            // Supply an existing Lesson with for($lesson) or lesson_id.
            'lesson_id' => null,
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
