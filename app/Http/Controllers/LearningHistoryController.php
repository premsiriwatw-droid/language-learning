<?php

namespace App\Http\Controllers;

use App\Models\LearningAttempt;
use App\Models\Question;
use App\Services\Quiz\QuizAnswerChecker;
use Illuminate\Http\Request;

class LearningHistoryController extends Controller
{
    public function index(Request $request)
    {
        $attempts = LearningAttempt::where('user_id', $request->user()->id)
            ->orderByDesc('completed_at')->orderByDesc('id')->paginate(10);
        return view('frontend.learning-history', compact('attempts'));
    }

    public function show(Request $request, LearningAttempt $attempt)
    {
        $this->authorizeOwner($request, $attempt);
        $wrongResults = collect($attempt->results)->where('first_correct', false)->values();
        return view('frontend.learning-history-detail', compact('attempt', 'wrongResults'));
    }

    private function authorizeOwner(Request $request, LearningAttempt $attempt): void
    {
        abort_unless((int) $attempt->user_id === (int) $request->user()->id, 404);
    }

    private function practiceKey(LearningAttempt $attempt): string
    {
        return 'history_review.'.$attempt->user_id.'.'.$attempt->id;
    }

    private function practice(Request $request, LearningAttempt $attempt): array
    {
        $this->authorizeOwner($request, $attempt);
        $wrongIds = collect($attempt->results)->where('first_correct', false)
            ->pluck('question_id')->unique()->values();
        $questions = Question::with(['exercise', 'answers'])
            ->whereIn('id', $wrongIds)
            ->whereHas('exercise', fn ($query) => $query
                ->where('lesson_id', $attempt->lesson_id)
                ->whereIn('type', ['multiple_choice', 'fill_blank', 'listening', 'image_choice']))
            ->get()->keyBy('id');
        $queue = $wrongIds->map(fn ($id) => $questions->get($id))
            ->filter(fn ($question) => $question && $question->answers->contains('is_correct', true))
            ->values();
        $signature = hash('sha256', $queue->map(fn ($question) => [
            $question->id, $question->question, $question->exercise->type,
            $question->audio_path, $question->image_path,
            $question->answers->sortBy('id')->map(fn ($answer) => [$answer->id, $answer->answer, $answer->is_correct])->values()->all(),
        ])->toJson());
        $key = $this->practiceKey($attempt);
        $state = $request->session()->get($key);
        if (!is_array($state) || ($state['signature'] ?? null) !== $signature) {
            $state = ['signature' => $signature, 'cursor' => 0, 'results' => [], 'seed' => bin2hex(random_bytes(16))];
            $request->session()->put($key, $state);
        }
        return [$queue, $state, $wrongIds->count() - $queue->count()];
    }

    public function review(Request $request, LearningAttempt $attempt)
    {
        [$queue, $state, $skippedCount] = $this->practice($request, $attempt);
        $question = $queue->get($state['cursor']);
        $result = $question ? ($state['results'][$question->id] ?? null) : null;
        $choices = $question ? $question->answers->sortBy(fn ($answer) => hash_hmac(
            'sha256', $question->id.':'.$answer->id, $state['seed']
        ))->values() : collect();
        return view('frontend.learning-history-review', [
            'attempt' => $attempt, 'question' => $question, 'result' => $result,
            'choices' => $choices, 'position' => $state['cursor'] + 1,
            'total' => $queue->count(), 'skippedCount' => $skippedCount,
        ]);
    }

    public function submitReview(Request $request, LearningAttempt $attempt, QuizAnswerChecker $checker)
    {
        [$queue, $state] = $this->practice($request, $attempt);
        $question = $queue->get($state['cursor']);
        $target = route('learning-history.review', $attempt);
        if (!$question) {
            return redirect($target);
        }
        $validated = $request->validate([
            'question_id' => ['required', 'integer'],
            'action' => ['required', 'in:answer,next'],
        ]);
        if ((int) $validated['question_id'] !== (int) $question->id) {
            return redirect($target)->withErrors(['answer' => 'ข้อนี้เปลี่ยนแล้ว กรุณาทำข้อปัจจุบัน']);
        }
        $result = $state['results'][$question->id] ?? null;
        if ($validated['action'] === 'next') {
            if ($result['solved'] ?? false) {
                $state['cursor']++;
                $request->session()->put($this->practiceKey($attempt), $state);
            }
            return redirect($target);
        }
        if ($result['solved'] ?? false) {
            return redirect($target);
        }
        $isChoice = in_array($question->exercise->type, ['multiple_choice', 'image_choice'], true);
        $answer = $request->validate(['answer' => $isChoice
            ? ['required', 'integer'] : ['required', 'string', 'max:1000']])['answer'];
        $validOption = $isChoice
            ? $question->answers->contains('id', (int) $answer)
            : $question->answers->contains('answer', $answer);
        if (!$validOption) {
            return redirect($target)->withErrors(['answer' => 'กรุณาเลือกคำตอบของข้อนี้']);
        }
        $state['results'][$question->id] = [
            'selected_answer' => $answer,
            'solved' => $checker->check($question, $answer),
        ];
        // Practice affects only its own session; original history and rewards are immutable.
        $request->session()->put($this->practiceKey($attempt), $state);
        return redirect($target);
    }
}
