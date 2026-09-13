<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Exercise;
use App\Models\Language;
use App\Models\Lesson;
use App\Models\Unit;
use App\Services\Quiz\QuizAnswerChecker;
use Illuminate\Http\Request;

class LearningController extends Controller
{
    public function indexLanguages()
    {
        $languages = Language::with('courses')->get();

        return view('frontend.languages', compact('languages'));
    }

    public function showUnits(Course $course)
    {
        $course->load('units');

        return view('frontend.units', compact('course'));
    }

    public function showLessons(Unit $unit)
    {
        $unit->load('lessons');

        return view('frontend.lessons', compact('unit'));
    }

    public function showLessonContent(Lesson $lesson)
    {
        $lesson->load([
            'vocabularies',
            'exercises',
        ]);

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
        $flow = $this->buildLessonFlow($lesson);

        $total = count($flow);

        if ($step < 1) {
            return redirect()->route('lessons.learn.step', [
                'lesson' => $lesson->id,
                'step' => 1,
            ]);
        }

        /*
         * เมื่อเรียนครบทุก Step แล้ว
         * กลับไปหน้ารวมบทเรียนของ Unit
         */
        if ($step > $total) {
            return redirect('/units/' . $lesson->unit_id . '/lessons');
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
        $flow = $this->buildLessonFlow($lesson);

        $current = $flow[$step - 1] ?? null;

        if (!$current) {
            return redirect()->route('lessons.learn', [
                'lesson' => $lesson->id,
            ]);
        }

        if ($current['type'] !== 'review') {
            return redirect()->route('lessons.learn.step', [
                'lesson' => $lesson->id,
                'step' => $step + 1,
            ]);
        }

        $question = $current['question'];
        $submitted = $request->input('answer');

        $correct = $checker->check(
            $question,
            $submitted
        );

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

    private function buildLessonFlow(Lesson $lesson): array
    {
        $lesson->load('vocabularies');

        $vocabularies = $lesson
            ->vocabularies
            ->values();

        $reviewExercises = Exercise::with([
            'questions.answers',
        ])
            ->where('lesson_id', $lesson->id)
            ->whereIn('type', [
                'multiple_choice',
                'fill_blank',
                'listening',
                'image_choice',
            ])
            ->orderBy('id')
            ->get();

        $multipleChoice = $reviewExercises
            ->firstWhere(
                'type',
                'multiple_choice'
            );

        $fillBlank = $reviewExercises
            ->firstWhere(
                'type',
                'fill_blank'
            );

        $listening = $reviewExercises
            ->firstWhere(
                'type',
                'listening'
            );

        $imageChoice = $reviewExercises
            ->firstWhere(
                'type',
                'image_choice'
            );

        $flow = [];

        foreach ($vocabularies as $index => $vocabulary) {
            $flow[] = [
                'type' => 'vocabulary',
                'vocabulary' => $vocabulary,
            ];

            /*
             * หลังคำศัพท์ตัวที่ 2
             * แทรก Multiple Choice
             */
            if (
                $index === 1 &&
                $multipleChoice?->questions->first()
            ) {
                $flow[] = [
                    'type' => 'review',
                    'exercise_type' => $multipleChoice->type,
                    'exercise' => $multipleChoice,
                    'question' => $multipleChoice->questions->first(),
                ];
            }

            /*
             * หลังคำศัพท์ตัวที่ 4
             * แทรก Fill Blank / Word Bank
             */
            if (
                $index === 3 &&
                $fillBlank?->questions->first()
            ) {
                $flow[] = [
                    'type' => 'review',
                    'exercise_type' => $fillBlank->type,
                    'exercise' => $fillBlank,
                    'question' => $fillBlank->questions->first(),
                ];
            }
        }

        /*
         * หลังเรียนคำศัพท์ครบทั้งหมด
         * แทรก Listening
         */
        if ($listening?->questions->first()) {
            $flow[] = [
                'type' => 'review',
                'exercise_type' => $listening->type,
                'exercise' => $listening,
                'question' => $listening->questions->first(),
            ];
        }

        /*
         * ปิดท้ายด้วย Image Choice
         */
        if ($imageChoice?->questions->first()) {
            $flow[] = [
                'type' => 'review',
                'exercise_type' => $imageChoice->type,
                'exercise' => $imageChoice,
                'question' => $imageChoice->questions->first(),
            ];
        }

        return $flow;
    }
}