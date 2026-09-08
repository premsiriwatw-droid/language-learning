<?php

namespace App\Services\Quiz;

use App\Models\Question;

class QuizAnswerChecker
{
    public function check(Question $question, string|int|null $submittedAnswer): bool
    {
        return match ($question->exercise?->type) {
            'fill_blank', 'listening' => $this->checkTextAnswer($question, (string) ($submittedAnswer ?? '')),
            'multiple_choice', 'image_choice' => $this->checkChoice($question, $submittedAnswer),
            default => false,
        };
    }

    private function checkTextAnswer(Question $question, string $submittedAnswer): bool
    {
        $submitted = $this->normalize($submittedAnswer);

        if ($submitted === '') {
            return false;
        }

        return $question->answers
            ->where('is_correct', true)
            ->contains(fn ($answer) => $this->normalize((string) $answer->answer) === $submitted);
    }

    private function checkChoice(Question $question, string|int|null $submittedAnswer): bool
    {
        if ($submittedAnswer === null || $submittedAnswer === '') {
            return false;
        }

        return $question->answers
            ->where('id', (int) $submittedAnswer)
            ->contains(fn ($answer) => (bool) $answer->is_correct);
    }

    private function normalize(string $value): string
    {
        return preg_replace('/\s+/u', ' ', trim($value)) ?? trim($value);
    }
}
