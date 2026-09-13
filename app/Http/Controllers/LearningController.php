<?php

namespace App\Http\Controllers;

use App\Models\Language;
use App\Models\Course;
use App\Models\Unit;
use App\Models\Lesson;
use Illuminate\Http\Request;
use App\Models\Exercise;
use App\Services\Quiz\QuizAnswerChecker;
use Illuminate\Http\RedirectResponse;

class LearningController extends Controller
{
    // 1. หน้าเลือกภาษา / คอร์ส
    public function indexLanguages()
    {
        // ดึงภาษาทั้งหมดพร้อม คอร์สของภาษานั้นๆ
        $languages = Language::with('courses')->get();

        return view('frontend.languages', compact('languages'));
    }

    // 2. หน้าเลือก Unit ของแต่ละ Course
    public function showUnits(Course $course)
    {
        // ใช้ Route Model Binding ดึง Unit ของ Course นั้นๆ
        $course->load('units');

        return view('frontend.units', compact('course'));
    }

    // 3. หน้าเลือก Lesson
    public function showLessons(Unit $unit)
    {
        // ดึง Lesson ทั้งหมดใน Unit นั้นๆ
        $unit->load('lessons');

        return view('frontend.lessons', compact('unit'));
    }

    // 4. หน้าแสดงเนื้อหาการเรียน (Vocabularies & Exercises)
    public function showLessonContent(Lesson $lesson)
    {
        // ดึงคำศัพท์และแบบฝึกหัดในบทเรียนนั้นมาแสดง
        $lesson->load(['vocabularies', 'exercises']);

        return view('frontend.lesson-content', compact('lesson'));
    }
    public function learn(Lesson $lesson)
{
    return redirect()->route('lessons.learn.step', [
        'lesson' => $lesson->id,
        'step' => 1,
    ]);
}

    public function learnStep(Lesson $lesson, int $step)
{
    $lesson->load('vocabularies');

    $vocabularies = $lesson->vocabularies->values();

    $reviewExercise = Exercise::with(['questions.answers'])
        ->where('lesson_id', $lesson->id)
        ->where('type', 'multiple_choice')
        ->first();

    $reviewQuestion = $reviewExercise?->questions->first();

    $flow = [];

    foreach ($vocabularies as $index => $vocabulary) {

        $flow[] = [
            'type' => 'vocabulary',
            'vocabulary' => $vocabulary,
        ];

        // หลังศัพท์ 2 คำแรก ใส่ Mini Review
        if ($index === 1 && $reviewQuestion) {
            $flow[] = [
                'type' => 'review',
                'question' => $reviewQuestion,
            ];
        }
    }

    $total = count($flow);

    if ($step < 1) {
        return redirect()->route('lessons.learn.step', [
            'lesson' => $lesson->id,
            'step' => 1,
        ]);
    }

    if ($step > $total) {
        return redirect('/lessons/' . $lesson->id);
    }

    $current = $flow[$step - 1];

    return view('frontend.learn-step', [
        'lesson' => $lesson,
        'current' => $current,
        'step' => $step,
        'total' => $total,
    ]);
}
    public function submitLearnStep(
        Request $request,
        Lesson $lesson,
        int $step,
        QuizAnswerChecker $checker
    ) {
        $lesson->load('vocabularies');

        $reviewExercise = Exercise::with(['questions.answers'])
            ->where('lesson_id', $lesson->id)
            ->where('type', 'multiple_choice')
            ->first();

        $question = $reviewExercise?->questions->first();

        if (!$question) {
            return redirect()->route('lessons.learn.step', [
                'lesson' => $lesson->id,
                'step' => $step + 1,
            ]);
        }

        $submitted = $request->input('answer');

        $correct = $checker->check($question, $submitted);

        if (!$correct) {
        return back()
            ->with('review_result', 'wrong')
            ->with('selected_answer', $submitted)
            ->withInput();
    }

        return back()
            ->with('review_result', 'correct')
            ->with('selected_answer', $submitted);
    }
}