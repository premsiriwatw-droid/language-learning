<?php

namespace App\Http\Controllers;

use App\Models\Answer;
use App\Models\Exercise;
use App\Models\Lesson;
use App\Models\Question;
use App\Models\Vocabulary;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class ContentManagementController extends Controller
{
    public function index(Lesson $lesson): View
    {
        $lesson->load([
            'vocabularies',
            'exercises.questions.answers',
            'exercises.questions.vocabularies',
        ]);

        return view('content.index', compact('lesson'));
    }

    public function storeVocabulary(
        Request $request,
        Lesson $lesson
    ): RedirectResponse {
        $lesson->vocabularies()->create(
            $this->validateVocabulary($request)
        );

        return back()->with('success', 'เพิ่มคำศัพท์เรียบร้อยแล้ว');
    }

    public function updateVocabulary(
        Request $request,
        Vocabulary $vocabulary
    ): RedirectResponse {
        $vocabulary->update(
            $this->validateVocabulary($request)
        );

        return back()->with('success', 'แก้ไขคำศัพท์เรียบร้อยแล้ว');
    }

    public function destroyVocabulary(
        Vocabulary $vocabulary
    ): RedirectResponse {
        // ป้องกันการลบศัพท์แล้วทำให้ prerequisite ของคำถามหาย
        if (
            DB::table('question_vocabulary')
                ->where('vocabulary_id', $vocabulary->id)
                ->exists()
        ) {
            return back()->withErrors([
                'vocabulary' =>
                    'คำศัพท์นี้มีคำถามใช้อยู่ กรุณาแก้การผูกคำศัพท์'
                    . 'ของคำถามเหล่านั้นก่อนลบ',
            ]);
        }

        $vocabulary->delete();

        return back()->with('success', 'ลบคำศัพท์เรียบร้อยแล้ว');
    }

    public function storeExercise(
        Request $request,
        Lesson $lesson
    ): RedirectResponse {
        $lesson->exercises()->create(
            $this->validateExercise($request)
        );

        return back()->with('success', 'เพิ่มแบบฝึกหัดเรียบร้อยแล้ว');
    }

    public function updateExercise(
        Request $request,
        Exercise $exercise
    ): RedirectResponse {
        $exercise->update(
            $this->validateExercise($request)
        );

        return back()->with('success', 'แก้ไขแบบฝึกหัดเรียบร้อยแล้ว');
    }

    public function destroyExercise(
        Exercise $exercise
    ): RedirectResponse {
        $exercise->delete();

        return back()->with('success', 'ลบแบบฝึกหัดเรียบร้อยแล้ว');
    }

    public function storeQuestion(
        Request $request,
        Exercise $exercise
    ): RedirectResponse {
        abort_if(
            $exercise->lesson_id === null,
            422,
            'แบบฝึกหัดต้องอยู่ในบทเรียนก่อนเพิ่มคำถาม'
        );

        $validated = $this->validateQuestion(
            $request,
            (int) $exercise->lesson_id
        );

        $vocabularyIds = $validated['vocabulary_ids'] ?? [];
        unset($validated['vocabulary_ids']);

        DB::transaction(function () use (
            $exercise,
            $validated,
            $vocabularyIds
        ) {
            $question = $exercise->questions()->create($validated);

            $question->vocabularies()->sync(
                $validated['vocabulary_mode'] === 'after_vocabulary'
                    ? $vocabularyIds
                    : []
            );
        });

        return back()->with('success', 'เพิ่มคำถามเรียบร้อยแล้ว');
    }

    public function updateQuestion(
        Request $request,
        Question $question
    ): RedirectResponse {
        $exercise = $question->exercise;

        abort_if(
            $exercise === null || $exercise->lesson_id === null,
            422,
            'คำถามต้องอยู่ในแบบฝึกหัดของบทเรียน'
        );

        $validated = $this->validateQuestion(
            $request,
            (int) $exercise->lesson_id
        );

        $vocabularyIds = $validated['vocabulary_ids'] ?? [];
        unset($validated['vocabulary_ids']);

        DB::transaction(function () use (
            $question,
            $validated,
            $vocabularyIds
        ) {
            $question->update($validated);

            // เปลี่ยนเป็นท้ายบทจะล้าง mapping เดิมด้วย
            $question->vocabularies()->sync(
                $validated['vocabulary_mode'] === 'after_vocabulary'
                    ? $vocabularyIds
                    : []
            );
        });

        return back()->with('success', 'แก้ไขคำถามเรียบร้อยแล้ว');
    }

    public function destroyQuestion(
        Question $question
    ): RedirectResponse {
        DB::transaction(function () use ($question) {
            $question->vocabularies()->detach();
            $question->delete();
        });

        return back()->with('success', 'ลบคำถามเรียบร้อยแล้ว');
    }

    public function storeAnswer(
        Request $request,
        Question $question
    ): RedirectResponse {
        $question->answers()->create(
            $this->validateAnswer($request)
        );

        return back()->with('success', 'เพิ่มคำตอบเรียบร้อยแล้ว');
    }

    public function updateAnswer(
        Request $request,
        Answer $answer
    ): RedirectResponse {
        $answer->update(
            $this->validateAnswer($request)
        );

        return back()->with('success', 'แก้ไขคำตอบเรียบร้อยแล้ว');
    }

    public function destroyAnswer(
        Answer $answer
    ): RedirectResponse {
        $answer->delete();

        return back()->with('success', 'ลบคำตอบเรียบร้อยแล้ว');
    }

    private function validateVocabulary(Request $request): array
    {
        return $request->validate([
            'word' => ['required', 'string', 'max:255'],
            'pinyin' => ['nullable', 'string', 'max:255'],
            'meaning' => ['required', 'string'],
            'example_sentence' => ['nullable', 'string'],
            'example_pinyin' => ['nullable', 'string'],
            'example_meaning' => ['nullable', 'string'],
        ]);
    }

    private function validateExercise(Request $request): array
    {
        return $request->validate([
            'type' => [
                'required',
                'string',
                'in:multiple_choice,fill_blank,translation,arrange_words,listening,image_choice,custom',
            ],
            'title' => ['required', 'string', 'max:255'],
        ]);
    }

    private function validateQuestion(
        Request $request,
        int $lessonId
    ): array {
        $validated = $request->validate([
            'question' => ['required', 'string'],
            'explanation' => ['nullable', 'string'],
            'audio_path' => ['nullable', 'string', 'max:255'],
            'image_path' => ['nullable', 'string', 'max:255'],

            'vocabulary_mode' => [
                'required',
                Rule::in(['after_vocabulary', 'lesson_end']),
            ],

            'vocabulary_ids' => [
                'required_if:vocabulary_mode,after_vocabulary',
                'array',
            ],

            'vocabulary_ids.*' => [
                'required',
                'integer',
                'distinct',
                Rule::exists('vocabularies', 'id')
                    ->where('lesson_id', $lessonId),
            ],
        ], [
            'vocabulary_mode.required' =>
                'กรุณาเลือกว่าคำถามออกหลังคำศัพท์หรือท้ายบท',

            'vocabulary_mode.in' =>
                'รูปแบบการจัดคำถามไม่ถูกต้อง',

            'vocabulary_ids.required_if' =>
                'กรุณาเลือกคำศัพท์ที่ต้องเรียนก่อนอย่างน้อยหนึ่งคำ',

            'vocabulary_ids.array' =>
                'รายการคำศัพท์ไม่ถูกต้อง',

            'vocabulary_ids.*.exists' =>
                'เลือกได้เฉพาะคำศัพท์ที่อยู่ในบทเดียวกับคำถาม',

            'vocabulary_ids.*.distinct' =>
                'ไม่สามารถเลือกคำศัพท์ซ้ำได้',
        ]);

        $vocabularyIds = $validated['vocabulary_ids'] ?? [];

        if (
            $validated['vocabulary_mode'] === 'after_vocabulary'
            && count($vocabularyIds) === 0
        ) {
            throw ValidationException::withMessages([
                'vocabulary_ids' =>
                    'กรุณาเลือกคำศัพท์ที่ต้องเรียนก่อนอย่างน้อยหนึ่งคำ',
            ]);
        }

        if (
            $validated['vocabulary_mode'] === 'lesson_end'
            && count($vocabularyIds) > 0
        ) {
            throw ValidationException::withMessages([
                'vocabulary_ids' =>
                    'คำถามทบทวนท้ายบทต้องไม่เลือกคำศัพท์ที่ต้องเรียนก่อน',
            ]);
        }

        return $validated;
    }

    private function validateAnswer(Request $request): array
    {
        $validated = $request->validate([
            'answer' => ['required', 'string'],
            'is_correct' => ['nullable', 'boolean'],
        ]);

        $validated['is_correct'] = $request->boolean('is_correct');

        return $validated;
    }
}