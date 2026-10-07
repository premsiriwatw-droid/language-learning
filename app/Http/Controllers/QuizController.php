<?php

namespace App\Http\Controllers;

use App\Models\Exercise;
use App\Models\Question;
use App\Services\Quiz\QuizAnswerChecker;
use App\Services\Progress\UnitAccess;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class QuizController extends Controller
{
    public function __construct(private readonly QuizAnswerChecker $checker) {}

    public function show(Request $request, Exercise $exercise): View
    {
        $this->ensureUnitAccessible($request, $exercise);
        abort_unless($this->isSupportedType($exercise->type), 404);

        $exercise->load(['questions.answers']);

        return view('quiz.show', [
            'exercise' => $exercise,
            'questions' => $exercise->questions,
        ]);
    }

    public function submit(Request $request, Exercise $exercise): View|RedirectResponse
    {
        $this->ensureUnitAccessible($request, $exercise);
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

    private function ensureUnitAccessible(Request $request, Exercise $exercise): void
    {
        $unit = $exercise->lesson?->unit;

        if ($unit) {
            abort_unless(
                app(UnitAccess::class)->canOpen($request->user(), $unit),
                403,
                'เรียนบทที่มีเนื้อหาใน Unit ก่อนหน้าให้ครบก่อน'
            );
        }
    }

    private function isSupportedType(string $type): bool
    {
        return in_array($type, ['fill_blank', 'listening', 'multiple_choice', 'image_choice'], true);
    }
}
