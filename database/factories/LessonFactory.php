<?php

namespace Database\Factories;

use App\Models\Lesson;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Lesson>
 */
class LessonFactory extends Factory
{
    public function definition(): array
    {
        return [
            // Supply a parent Unit with for($unit) or unit_id.
            'unit_id' => null,
            'title' => fake()->sentence(3),
            'content' => null,
            // Leave 'position' unset to let the model auto-assign the
            // next slot in the Unit. Override explicitly in tests that
            // need a specific order.
        ];
    }
}
