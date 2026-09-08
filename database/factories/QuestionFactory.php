<?php

namespace Database\Factories;

use App\Models\Exercise;
use App\Models\Question;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Question> */
class QuestionFactory extends Factory
{
    public function definition(): array
    {
        return [
            'exercise_id' => Exercise::factory(),
            'question' => fake()->sentence(),
            'explanation' => null,
            'audio_path' => null,
            'image_path' => null,
        ];
    }

    public function chinese(string $question, ?string $explanation = null): static
    {
        return $this->state(fn () => [
            'question' => $question,
            'explanation' => $explanation,
        ]);
    }
}
