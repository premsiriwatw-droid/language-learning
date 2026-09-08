<?php

namespace App\Http\Controllers;

use App\Models\Exercise;
use App\Models\Question;
use App\Services\Quiz\QuizAnswerChecker;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class QuizController extends Controller
{
    public function __construct(private readonly QuizAnswerChecker $checker) {}

    public function show(Exercise $exercise): View
    {
        abort_unless($this->isSupportedType($exercise->type), 404);

        $exercise->load(['questions.answers']);

        return view('quiz.show', [
            'exercise' => $exercise,
            'questions' => $exercise->questions,
        ]);
    }

    public function submit(Request $request, Exercise $exercise): View|RedirectResponse
    {
        abort_unless($this->isSupportedType($exercise->type), 404);

        $exercise->load(['questions.answers']);
        $answers = $request->input('answers', []);

        $results = $exercise->questions->mapWithKeys(function (Question $question) use ($answers) {
            $submitted = $answers[$question->id] ?? null;

            return [$question->id => [
                'submitted' => $submitted,
                'correct' => $this->checker->check($question, $submitted),
            ]];
        });

        return view('quiz.show', [
            'exercise' => $exercise,
            'questions' => $exercise->questions,
            'results' => $results,
            'submitted' => true,
        ]);
    }

    private function isSupportedType(string $type): bool
    {
        return in_array($type, ['fill_blank', 'listening', 'multiple_choice', 'image_choice'], true);
    }
}
