<?php

namespace App\Http\Controllers;

use App\Services\Progress\PlacementTestService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class PlacementController extends Controller
{
    public function show(Request $request, PlacementTestService $placement): View
    {
        abort_unless($request->user()->isLearner(), 403);
        $validated = $request->validate(['language_id' => ['nullable', 'integer', Rule::exists('languages', 'id')]]);
        $languages = $placement->availableLanguages();
        $state = $request->session()->get('placement_test');
        $languageId = isset($validated['language_id']) ? (int) $validated['language_id']
            : (int) (($state['language_id'] ?? null) ?: $languages->first()?->id);
        $language = $languages->firstWhere('id', $languageId);
        $questions = collect();

        if (is_array($state) && ($state['user_id'] ?? null) === $request->user()->id
            && ($state['language_id'] ?? null) === $languageId) {
            $questions = $placement->questionsForTest($state['question_ids'], $languageId);
        }

        if ($questions->count() !== $placement->questionCount()) {
            $request->session()->forget('placement_test');
            $questions = $language ? $placement->createTest($languageId) : collect();
            $state = null;

            if ($questions->isNotEmpty()) {
                $state = [
                    'user_id' => $request->user()->id,
                    'language_id' => $languageId,
                    'question_ids' => $questions->pluck('id')->all(),
                    'token' => bin2hex(random_bytes(32)),
                ];
                $request->session()->put('placement_test', $state);
            }
        }

        return view('frontend.placement', [
            'languages' => $languages,
            'language' => $language,
            'questions' => $state ? $placement->presentQuestions($questions, $state['token']) : [],
            'token' => $state['token'] ?? null,
            'questionCount' => $placement->questionCount(),
        ]);
    }

    public function submit(Request $request, PlacementTestService $placement): RedirectResponse
    {
        abort_unless($request->user()->isLearner(), 403);
        $validated = $request->validate([
            'token' => ['required', 'string', 'size:64'],
            'answers' => ['required', 'array', 'size:'.$placement->questionCount()],
            'answers.*' => ['required', 'integer'],
        ]);
        $state = $request->session()->get('placement_test');

        if (! is_array($state) || ($state['user_id'] ?? null) !== $request->user()->id
            || ! hash_equals($state['token'] ?? '', $validated['token'])) {
            throw ValidationException::withMessages(['token' => 'แบบทดสอบนี้หมดอายุหรือส่งผลไปแล้ว กรุณาเปิดแบบทดสอบอีกครั้ง']);
        }

        $result = $placement->grade($state['question_ids'], $state['language_id'], $validated['answers']);
        $request->user()->update([
            'learner_proficiency' => $result['proficiency'],
            'placement_required' => false,
        ]);
        $request->session()->forget('placement_test');

        return redirect()->route('profile')->with('status',
            'Placement recommendation: '.$result['proficiency']->label().' ('.$result['percent'].'%)'
        );
    }
}
