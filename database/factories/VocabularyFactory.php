<?php

namespace Database\Factories;

use App\Models\Vocabulary;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Vocabulary>
 */
class VocabularyFactory extends Factory
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
            'word' => fake()->word(),
            'pinyin' => null,
            'meaning' => fake()->sentence(),
            'example_sentence' => null,
            'example_pinyin' => null,
            'example_meaning' => null,
        ];
    }
}
