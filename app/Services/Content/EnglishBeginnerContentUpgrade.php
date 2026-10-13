<?php

namespace App\Services\Content;

use Illuminate\Support\Facades\DB;
use RuntimeException;
use stdClass;

class EnglishBeginnerContentUpgrade
{
    /** Upgrade this revision only, preserving question identities and Admin mappings. */
    public function apply(): void
    {
        $revisions = require database_path('seeders/data/english_beginner_revision.php');

        DB::transaction(function () use ($revisions): void {
            $hasBank = DB::table('questions as q')
                ->join('exercises as e', 'e.id', '=', 'q.exercise_id')
                ->join('lessons as l', 'l.id', '=', 'e.lesson_id')
                ->join('units as u', 'u.id', '=', 'l.unit_id')
                ->join('courses as c', 'c.id', '=', 'u.course_id')
                ->join('languages as g', 'g.id', '=', 'c.language_id')
                ->where('g.name', 'English')
                ->where('c.title', 'English Beginner')
                ->whereIn('e.title', [
                    'English MVP: multiple_choice',
                    'English MVP: fill_blank',
                    'English MVP: listening',
                    'English MVP: image_choice',
                ])
                ->exists();

            if (! $hasBank) {
                return;
            }

            $wordingUpdates = [];
            $reviewUpdates = [];

            foreach ($revisions as $revision) {
                $exercise = $this->exercise($revision);
                $matches = DB::table('questions')
                    ->where('exercise_id', $exercise->id)
                    ->whereIn('question', array_unique([
                        $revision['old_question'],
                        $revision['new_question'],
                    ]))
                    ->lockForUpdate()
                    ->get();

                if ($matches->count() !== 1) {
                    throw $this->conflict($revision, 'expected exactly one old or revised question');
                }

                $question = $matches->sole();

                if (! in_array($question->question, [$revision['old_question'], $revision['new_question']], true)) {
                    throw $this->conflict($revision, 'question text does not match the frozen revision');
                }

                $this->assertPayload($question, $revision);

                if ($revision['make_lesson_end']) {
                    $vocabularyId = $this->reviewVocabularyToRemove($question, $exercise, $revision);

                    if ($vocabularyId !== null) {
                        $reviewUpdates[] = [$question->id, $vocabularyId];
                    }
                } elseif ($question->question === $revision['old_question']) {
                    $wordingUpdates[] = [$question->id, $exercise->id, $revision];
                }
            }

            foreach ($wordingUpdates as [$questionId, $exerciseId, $revision]) {
                $changed = DB::table('questions')
                    ->where('id', $questionId)
                    ->where('exercise_id', $exerciseId)
                    ->where('question', $revision['old_question'])
                    ->update(['question' => $revision['new_question']]);

                if ($changed !== 1) {
                    throw $this->conflict($revision, 'question changed during the upgrade');
                }
            }

            foreach ($reviewUpdates as [$questionId, $vocabularyId]) {
                $removed = DB::table('question_vocabulary')
                    ->where('question_id', $questionId)
                    ->where('vocabulary_id', $vocabularyId)
                    ->delete();

                $changed = DB::table('questions')
                    ->where('id', $questionId)
                    ->where('vocabulary_mode', 'after_vocabulary')
                    ->update(['vocabulary_mode' => 'lesson_end']);

                if ($removed !== 1 || $changed !== 1) {
                    throw new RuntimeException('English beginner upgrade conflict: Review IC2 changed during the upgrade.');
                }
            }
        });
    }

    /** @param array<string, mixed> $revision */
    private function exercise(array $revision): stdClass
    {
        $lessons = DB::table('lessons as l')
            ->join('units as u', 'u.id', '=', 'l.unit_id')
            ->join('courses as c', 'c.id', '=', 'u.course_id')
            ->join('languages as g', 'g.id', '=', 'c.language_id')
            ->where('g.name', 'English')
            ->where('c.title', 'English Beginner')
            ->where('l.title', $revision['lesson'])
            ->select('l.id')
            ->lockForUpdate()
            ->get();

        if ($lessons->count() !== 1) {
            throw $this->conflict($revision, 'expected exactly one seeded English lesson');
        }

        $exercises = DB::table('exercises')
            ->where('lesson_id', $lessons->sole()->id)
            ->where('type', $revision['type'])
            ->where('title', 'English MVP: '.$revision['type'])
            ->lockForUpdate()
            ->get(['id', 'lesson_id']);

        if ($exercises->count() !== 1) {
            throw $this->conflict($revision, 'expected exactly one seeded English exercise');
        }

        return $exercises->sole();
    }

    /** @param array<string, mixed> $revision */
    private function assertPayload(stdClass $question, array $revision): void
    {
        foreach (['explanation', 'audio_path', 'image_path'] as $field) {
            if ($question->{$field} !== $revision[$field]) {
                throw $this->conflict($revision, 'existing '.$field.' differs from the revision');
            }
        }

        $answers = DB::table('answers')
            ->where('question_id', $question->id)
            ->get(['answer', 'is_correct'])
            ->map(fn (stdClass $answer): array => [$answer->answer, (bool) $answer->is_correct])
            ->all();
        $expected = $revision['answers'];
        $compare = fn (array $left, array $right): int => strcmp($left[0], $right[0]) ?: $left[1] <=> $right[1];
        usort($answers, $compare);
        usort($expected, $compare);

        if ($answers !== $expected) {
            throw $this->conflict($revision, 'existing answers differ from the revision');
        }
    }

    /** @param array<string, mixed> $revision */
    private function reviewVocabularyToRemove(stdClass $question, stdClass $exercise, array $revision): ?int
    {
        $mapped = DB::table('question_vocabulary as p')
            ->join('vocabularies as v', 'v.id', '=', 'p.vocabulary_id')
            ->where('p.question_id', $question->id)
            ->lockForUpdate()
            ->get(['v.id', 'v.word', 'v.lesson_id']);

        if ($question->vocabulary_mode === 'lesson_end' && $mapped->isEmpty()) {
            return null;
        }

        if (
            $question->vocabulary_mode !== 'after_vocabulary'
            || $mapped->count() !== 1
            || $mapped->sole()->word !== 'need'
            || (int) $mapped->sole()->lesson_id !== (int) $exercise->lesson_id
        ) {
            throw $this->conflict($revision, 'Review IC2 has an unexpected or Admin-modified mapping');
        }

        return (int) $mapped->sole()->id;
    }

    /** @param array<string, mixed> $revision */
    private function conflict(array $revision, string $reason): RuntimeException
    {
        return new RuntimeException(
            'English beginner upgrade conflict: '.$revision['lesson'].' '.$revision['type']
            .' #'.$revision['index'].': '.$reason.'. No target is replaced or recreated.'
        );
    }
}
