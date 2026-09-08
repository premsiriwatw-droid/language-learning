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
            // Supply an existing Lesson with for($lesson) or lesson_id.
            'lesson_id' => null,
            'word' => fake()->word(),
            'pinyin' => null,
            'meaning' => fake()->sentence(),
            'example_sentence' => null,
            'example_pinyin' => null,
            'example_meaning' => null,
        ];
    }
}
