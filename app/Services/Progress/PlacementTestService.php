<?php

namespace App\Services\Progress;

use App\Enums\LearnerProficiency;
use App\Models\Language;
use App\Models\Question;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;

class PlacementTestService
{
    public function questionCount(): int
    {
        return max(10, min(15, (int) config('progression.placement.question_count', 12)));
    }

    public function availableLanguages(): Collection
    {
        return Language::orderBy('id')->get()->filter(
            fn (Language $language) => $this->eligibleQuestions($language->id)->count() >= $this->questionCount()
        )->values();
    }

    public function createTest(int $languageId): Collection
    {
        $questions = $this->eligibleQuestions($languageId)
            ->with(['answers', 'exercise.lesson.unit'])
            ->get()
            ->sortBy(fn (Question $question) => [
                $question->exercise->lesson->unit->course_id,
                $question->exercise->lesson->unit->position,
                $question->exercise->lesson->position,
                $question->id,
            ])->values();

        if ($questions->count() < $this->questionCount()) {
            return collect();
        }

        $lessons = $questions->groupBy('exercise.lesson.id')->values();
        $selected = collect();
        $round = 0;

        // Spread each round over the available lesson positions, without editing content.
        while ($selected->count() < $this->questionCount()) {
            $available = $lessons->filter(fn (Collection $items) => $items->count() > $round)->values();
            $take = min($this->questionCount() - $selected->count(), $available->count());

            for ($index = 0; $index < $take; $index++) {
                $lessonIndex = $take === 1 ? intdiv($available->count(), 2)
                    : (int) round($index * ($available->count() - 1) / ($take - 1));
                $selected->push($available[$lessonIndex][$round]);
            }

            $round++;
        }

        return $selected;
    }

    public function questionsForTest(array $questionIds, int $languageId): Collection
    {
        $questions = $this->eligibleQuestions($languageId)->with('answers')
            ->whereIn('id', $questionIds)->get()->keyBy('id');

        return collect($questionIds)->map(fn ($id) => $questions->get($id))->filter()->values();
    }

    public function presentQuestions(Collection $questions, string $token): array
    {
        return $questions->map(fn (Question $question) => [
            'id' => $question->id,
            'question' => $question->question,
            'answers' => $question->answers->sortBy(fn ($answer) => hash_hmac(
                'sha256', $question->id.':'.$answer->id, $token
            ))->values()->map(fn ($answer) => [
                'id' => $answer->id,
                'answer' => $answer->answer,
            ])->all(),
        ])->all();
    }

    public function grade(array $questionIds, int $languageId, array $answers): array
    {
        $expected = array_map('strval', $questionIds);
        $submitted = array_map('strval', array_keys($answers));
        sort($expected);
        sort($submitted);
        $questions = $this->questionsForTest($questionIds, $languageId);

        if ($expected !== $submitted || count($questionIds) !== $this->questionCount()
            || $questions->count() !== count($questionIds)) {
            throw ValidationException::withMessages(['answers' => 'กรุณาตอบเฉพาะคำถามทั้งหมดในแบบทดสอบนี้']);
        }

        $correct = 0;
        foreach ($questions as $question) {
            $answer = $question->answers->firstWhere('id', (int) $answers[$question->id]);
            if (! $answer) {
                throw ValidationException::withMessages(['answers' => 'กรุณาเลือกคำตอบที่อยู่ในคำถามนี้']);
            }
            $correct += $answer->is_correct ? 1 : 0;
        }

        $percent = $correct / $questions->count() * 100;

        return [
            'correct' => $correct,
            'total' => $questions->count(),
            'percent' => round($percent, 2),
            'proficiency' => $this->recommendation($percent),
        ];
    }

    public function recommendation(float $percent): LearnerProficiency
    {
        if ($percent >= (float) config('progression.placement.advanced_percent', 80)) {
            return LearnerProficiency::Advanced;
        }

        if ($percent >= (float) config('progression.placement.intermediate_percent', 50)) {
            return LearnerProficiency::Intermediate;
        }

        return LearnerProficiency::Beginner;
    }

    private function eligibleQuestions(int $languageId): Builder
    {
        return Question::query()
            ->whereHas('exercise', fn (Builder $query) => $query->where('type', 'multiple_choice'))
            ->whereHas('exercise.lesson.unit.course', fn (Builder $query) => $query->where('language_id', $languageId))
            ->has('answers', '>=', 2)
            ->whereHas('answers', fn (Builder $query) => $query->where('is_correct', true), '=', 1);
    }
}
