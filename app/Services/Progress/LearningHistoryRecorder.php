<?php

namespace App\Services\Progress;

use App\Models\LearningAttempt;
use App\Models\Lesson;
use App\Models\User;
use Illuminate\Support\Carbon;

class LearningHistoryRecorder
{
    /** Called only after Runtime has verified completion of every step. */
    public function record(User $user, Lesson $lesson, array $runtime, array $flow, array $summary): LearningAttempt
    {
        $results = [];
        foreach ($flow as $item) {
            if ($item['type'] !== 'review') {
                continue;
            }
            $question = $item['question'];
            $result = $runtime['results'][$question->id];
            $isChoice = in_array($item['exercise_type'], ['multiple_choice', 'image_choice'], true);
            $selectionText = function ($selection) use ($question, $isChoice) {
                if ($selection === null) {
                    return null;
                }
                return $isChoice
                    ? $question->answers->firstWhere('id', (int) $selection)?->answer
                    : (string) $selection;
            };

            // Snapshot text keeps history readable after content is edited/deleted.
            $results[] = [
                'question_id' => $question->id,
                'exercise_type' => $item['exercise_type'],
                'question' => $question->question,
                'first_correct' => $result['first_correct'],
                'attempts' => $result['attempts'],
                'wrong_attempts' => $result['wrong_attempts'],
                'first_answer' => $selectionText($result['first_selected_answer'] ?? null),
                'last_answer' => $selectionText($result['selected_answer'] ?? null),
                'vocabulary' => $question->vocabularies->map(fn ($word) => [
                    'id' => $word->id, 'word' => $word->word, 'meaning' => $word->meaning,
                ])->values()->all(),
            ];
        }

        // Legacy rounds have no history_run_id; choice_seed persists across resumes.
        $runKey = hash('sha256', ($runtime['history_run_id'] ?? $runtime['choice_seed']
            ?? (string) $runtime['started_at']).':'.$runtime['signature']);

        return LearningAttempt::firstOrCreate(
            ['user_id' => $user->id, 'run_key' => $runKey],
            [
                'lesson_id' => $lesson->id,
                'lesson_title' => $lesson->title,
                'language_name' => $lesson->unit?->course?->language?->name,
                'vocabulary_count' => $summary['vocabulary_count'],
                'question_count' => $summary['question_count'],
                'correct_count' => $summary['correct_count'],
                'wrong_count' => $summary['wrong_count'],
                'wrong_attempts' => $summary['wrong_attempts'],
                'score_percent' => $summary['score_percent'],
                'elapsed_seconds' => $summary['elapsed_seconds'],
                'started_at' => Carbon::createFromTimestamp($runtime['started_at']),
                'completed_at' => Carbon::createFromTimestamp($runtime['finished_at']),
                'results' => $results,
            ]
        );
    }
}
