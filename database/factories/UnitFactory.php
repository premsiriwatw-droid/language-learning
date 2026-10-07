<?php

namespace Database\Factories;

use App\Models\Unit;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Unit>
 */
class UnitFactory extends Factory
{
    public function definition(): array
    {
        return [
            // Supply a parent Course with for($course) or course_id.
            'course_id' => null,
            'title' => 'Unit '.fake()->unique()->numberBetween(1, 1000).': '.fake()->words(2, true),
            // Leave 'position' unset to let the model auto-assign the
            // next slot in the Course. Override explicitly in tests that
            // need a specific order.
        ];
    }
}
