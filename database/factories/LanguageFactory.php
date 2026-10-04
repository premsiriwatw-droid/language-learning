<?php

namespace Database\Factories;

use App\Models\Language;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Language>
 */
class LanguageFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => fake()->unique()->randomElement([
                'Chinese', 'Japanese', 'Korean', 'Vietnamese',
                'Thai', 'Spanish', 'French', 'German',
                'Italian', 'Portuguese', 'Russian', 'Arabic',
            ]),
        ];
    }
}
