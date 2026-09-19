<?php

namespace App\Http\Controllers;

use App\Models\Answer;
use App\Models\Exercise;
use App\Models\Lesson;
use App\Models\Question;
use App\Models\Vocabulary;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ContentManagementController extends Controller
{
    /**
     * แสดง Content ทั้งหมดของ Lesson
     */
    public function index(Lesson $lesson): View
    {
        $lesson->load([
            'vocabularies',
            'exercises.questions.answers',
        ]);

        return view('content.index', compact('lesson'));
    }

    /**
     * เพิ่มคำศัพท์
     */
    public function storeVocabulary(
        Request $request,
        Lesson $lesson
    ): RedirectResponse {
        $validated = $request->validate([
            'word' => ['required', 'string', 'max:255'],
            'pinyin' => ['nullable', 'string', 'max:255'],
            'meaning' => ['required', 'string'],
            'example_sentence' => ['nullable', 'string'],
            'example_pinyin' => ['nullable', 'string'],
            'example_meaning' => ['nullable', 'string'],
        ]);

        $lesson->vocabularies()->create($validated);

        return back()->with('success', 'เพิ่มคำศัพท์เรียบร้อยแล้ว');
    }

    /**
     * แก้ไขคำศัพท์
     */
    public function updateVocabulary(
        Request $request,
        Vocabulary $vocabulary
    ): RedirectResponse {
        $validated = $request->validate([
            'word' => ['required', 'string', 'max:255'],
            'pinyin' => ['nullable', 'string', 'max:255'],
            'meaning' => ['required', 'string'],
            'example_sentence' => ['nullable', 'string'],
            'example_pinyin' => ['nullable', 'string'],
            'example_meaning' => ['nullable', 'string'],
        ]);

        $vocabulary->update($validated);

        return back()->with('success', 'แก้ไขคำศัพท์เรียบร้อยแล้ว');
    }

    /**
     * ลบคำศัพท์
     */
    public function destroyVocabulary(
        Vocabulary $vocabulary
    ): RedirectResponse {
        $vocabulary->delete();

        return back()->with('success', 'ลบคำศัพท์เรียบร้อยแล้ว');
    }

    /**
     * เพิ่มแบบฝึกหัด
     */
    public function storeExercise(
        Request $request,
        Lesson $lesson
    ): RedirectResponse {
        $validated = $request->validate([
            'type' => [
                'required',
                'string',
                'in:multiple_choice,fill_blank,translation,arrange_words,listening,image_choice,custom',
            ],
            'title' => ['required', 'string', 'max:255'],
        ]);

        $lesson->exercises()->create($validated);

        return back()->with('success', 'เพิ่มแบบฝึกหัดเรียบร้อยแล้ว');
    }

    /**
     * แก้ไขแบบฝึกหัด
     */
    public function updateExercise(
        Request $request,
        Exercise $exercise
    ): RedirectResponse {
        $validated = $request->validate([
            'type' => [
                'required',
                'string',
                'in:multiple_choice,fill_blank,translation,arrange_words,listening,image_choice,custom',
            ],
            'title' => ['required', 'string', 'max:255'],
        ]);

        $exercise->update($validated);

        return back()->with('success', 'แก้ไขแบบฝึกหัดเรียบร้อยแล้ว');
    }

    /**
     * ลบแบบฝึกหัด
     */
    public function destroyExercise(
        Exercise $exercise
    ): RedirectResponse {
        $exercise->delete();

        return back()->with('success', 'ลบแบบฝึกหัดเรียบร้อยแล้ว');
    }

    /**
     * เพิ่มคำถามใน Exercise
     */
    public function storeQuestion(
        Request $request,
        Exercise $exercise
    ): RedirectResponse {
        $validated = $request->validate([
            'question' => ['required', 'string'],
            'explanation' => ['nullable', 'string'],
            'audio_path' => ['nullable', 'string', 'max:255'],
            'image_path' => ['nullable', 'string', 'max:255'],
        ]);

        $exercise->questions()->create($validated);

        return back()->with('success', 'เพิ่มคำถามเรียบร้อยแล้ว');
    }

    /**
     * แก้ไขคำถาม
     */
    public function updateQuestion(
        Request $request,
        Question $question
    ): RedirectResponse {
        $validated = $request->validate([
            'question' => ['required', 'string'],
            'explanation' => ['nullable', 'string'],
            'audio_path' => ['nullable', 'string', 'max:255'],
            'image_path' => ['nullable', 'string', 'max:255'],
        ]);

        $question->update($validated);

        return back()->with('success', 'แก้ไขคำถามเรียบร้อยแล้ว');
    }

    /**
     * ลบคำถาม
     */
    public function destroyQuestion(
        Question $question
    ): RedirectResponse {
        $question->delete();

        return back()->with('success', 'ลบคำถามเรียบร้อยแล้ว');
    }

    /**
     * เพิ่มตัวเลือกคำตอบ
     */
    public function storeAnswer(
        Request $request,
        Question $question
    ): RedirectResponse {
        $validated = $request->validate([
            'answer' => ['required', 'string'],
            'is_correct' => ['nullable', 'boolean'],
        ]);

        $validated['is_correct'] = $request->boolean('is_correct');

        $question->answers()->create($validated);

        return back()->with('success', 'เพิ่มคำตอบเรียบร้อยแล้ว');
    }

    /**
     * แก้ไขตัวเลือกคำตอบ
     */
    public function updateAnswer(
        Request $request,
        Answer $answer
    ): RedirectResponse {
        $validated = $request->validate([
            'answer' => ['required', 'string'],
            'is_correct' => ['nullable', 'boolean'],
        ]);

        $validated['is_correct'] = $request->boolean('is_correct');

        $answer->update($validated);

        return back()->with('success', 'แก้ไขคำตอบเรียบร้อยแล้ว');
    }

    /**
     * ลบตัวเลือกคำตอบ
     */
    public function destroyAnswer(
        Answer $answer
    ): RedirectResponse {
        $answer->delete();

        return back()->with('success', 'ลบคำตอบเรียบร้อยแล้ว');
    }
}
