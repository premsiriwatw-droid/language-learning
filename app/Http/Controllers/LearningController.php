<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Exercise;
use App\Models\Language;
use App\Models\Lesson;
use App\Models\Unit;
use App\Services\Progress\LearningProgress;
use App\Services\Progress\LearningRewardCalculator;
use App\Services\Progress\LessonAccess;
use App\Services\Quiz\QuizAnswerChecker;
use Illuminate\Http\Request;

class LearningController extends Controller
{
    public function __construct(
        private LearningProgress $progress,
        private LearningRewardCalculator $rewardCalculator
    ) {
    }

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

    public function showLessons(Request $request, Unit $unit)
    {
        $unit->load('lessons');

        $lessonStates = app(LessonAccess::class)->forCourse(
            (int) $unit->course_id,
            $request->user()
        );

        return view('frontend.lessons', [
            'unit' => $unit,
            'lessonStates' => $lessonStates,
        ]);
    }

    public function showLessonContent(Lesson $lesson)
    {
        $lesson->load(['vocabularies', 'exercises']);

        return view('frontend.lesson-content', compact('lesson'));
    }

    public function learn(Request $request, Lesson $lesson)
    {
        $this->ensureLessonAccessible($request, $lesson);

        $flow = $this->buildLessonFlow($lesson);

        // เริ่มรอบใหม่ แต่ไม่ลบ Progress ในฐานข้อมูล
        $this->getRuntime($request, $lesson, $flow, true);

        return redirect()->route('lessons.learn.step', [
            'lesson' => $lesson->id,
            'step' => 1,
        ]);
    }

    public function learnStep(Request $request, Lesson $lesson, int $step)
    {
        $this->ensureLessonAccessible($request, $lesson);

        $flow = $this->buildLessonFlow($lesson);
        $total = count($flow);

        if ($total === 0) {
            return redirect()->route('units.lessons', [
                'unit' => $lesson->unit_id,
            ]);
        }

        $runtime = $this->getRuntime($request, $lesson, $flow);

        if ($step < 1) {
            return redirect()->route('lessons.learn.step', [
                'lesson' => $lesson->id,
                'step' => 1,
            ]);
        }

        if ($step > $total) {
            if (!$this->runtimeIsComplete($runtime, $flow)) {
                return redirect()->route('lessons.learn.step', [
                    'lesson' => $lesson->id,
                    'step' => min($runtime['next_step'], $total),
                ]);
            }

            return redirect()->route('lessons.learn.summary', [
                'lesson' => $lesson->id,
            ]);
        }

        // เปิดได้เฉพาะขั้นปัจจุบันหรือขั้นที่ผ่านมาแล้ว
        // ป้องกันเปิด URL ไปคำถามก่อนเรียนถึงขั้นนั้น
        if ($step > $runtime['next_step']) {
            return redirect()->route('lessons.learn.step', [
                'lesson' => $lesson->id,
                'step' => $runtime['next_step'],
            ]);
        }

        $current = $flow[$step - 1];

        $result = $current['type'] === 'review'
            ? ($runtime['results'][$current['question']->id] ?? null)
            : null;

        $reviewResult = $result
            ? ($result['solved'] ? 'correct' : 'wrong')
            : null;

        return view('frontend.learn-step', [
            'lesson' => $lesson,
            'current' => $current,
            'step' => $step,
            'total' => $total,
            'reviewResult' => $reviewResult,
            'selectedAnswer' => $result['selected_answer'] ?? null,
        ]);
    }

    public function submitLearnStep(
        Request $request,
        Lesson $lesson,
        int $step,
        QuizAnswerChecker $checker
    ) {
        $this->ensureLessonAccessible($request, $lesson);

        $flow = $this->buildLessonFlow($lesson);

        if (count($flow) === 0) {
            return redirect()->route('units.lessons', [
                'unit' => $lesson->unit_id,
            ]);
        }

        $runtime = $this->getRuntime($request, $lesson, $flow);
        $current = $flow[$step - 1] ?? null;

        // รับเฉพาะขั้นที่ต้องทำ ป้องกันข้ามข้อหรือส่งข้อเดิมซ้ำ
        if (!$current || $step !== $runtime['next_step']) {
            $targetStep = $runtime['next_step'];

            if (
                $current
                && $step < $runtime['next_step']
                && $current['type'] === 'review'
            ) {
                $targetStep = $step;
            }

            return redirect()->route('lessons.learn.step', [
                'lesson' => $lesson->id,
                'step' => $targetStep,
            ]);
        }

        if ($current['type'] === 'vocabulary') {
            $runtime['next_step']++;

            $this->saveRuntime($request, $lesson, $runtime, $flow);

            return redirect()->route('lessons.learn.step', [
                'lesson' => $lesson->id,
                'step' => $runtime['next_step'],
            ]);
        }

        $question = $current['question'];

        $isChoice = in_array(
            $current['exercise_type'],
            ['multiple_choice', 'image_choice'],
            true
        );

        $validated = $request->validate([
            'answer' => $isChoice
                ? ['required', 'integer']
                : ['required', 'string', 'max:1000'],
        ]);

        $submitted = $validated['answer'];

        if (
            $isChoice
            && !$question->answers->contains('id', (int) $submitted)
        ) {
            return redirect()->route('lessons.learn.step', [
                'lesson' => $lesson->id,
                'step' => $step,
            ])->withErrors([
                'answer' => 'กรุณาเลือกคำตอบของคำถามนี้',
            ]);
        }

        $correct = $checker->check($question, $submitted);

        $result = $runtime['results'][$question->id] ?? [
            'attempts' => 0,
            'wrong_attempts' => 0,
            'first_correct' => null,
            'solved' => false,
            'selected_answer' => null,
        ];

        if ($result['attempts'] === 0) {
            $result['first_correct'] = $correct;
        }

        $result['attempts']++;
        $result['selected_answer'] = $submitted;
        $result['solved'] = $correct;

        if (!$correct) {
            $result['wrong_attempts']++;
        }

        $runtime['results'][$question->id] = $result;

        if ($correct) {
            $runtime['next_step']++;
        }

        $this->saveRuntime($request, $lesson, $runtime, $flow);

        // แสดง feedback ก่อนกดถัดไป
        return redirect()->route('lessons.learn.step', [
            'lesson' => $lesson->id,
            'step' => $step,
        ]);
    }

    public function lessonSummary(Request $request, Lesson $lesson)
    {
        $this->ensureLessonAccessible($request, $lesson);

        $flow = $this->buildLessonFlow($lesson);
        $total = count($flow);

        if ($total === 0) {
            return redirect()->route('units.lessons', [
                'unit' => $lesson->unit_id,
            ]);
        }

        $runtime = $this->getRuntime($request, $lesson, $flow);

        if (!$this->runtimeIsComplete($runtime, $flow)) {
            return redirect()->route('lessons.learn.step', [
                'lesson' => $lesson->id,
                'step' => min($runtime['next_step'], $total),
            ]);
        }

        $summary = $this->buildLessonSummary($runtime);

        $rewards = $this->rewardCalculator->calculate(
            $summary['question_count'],
            $summary['correct_count']
        );

        $summary['calculated_xp'] = $rewards['xp'];
        $summary['calculated_stars'] = $rewards['stars'];
        $summary['progress_saved'] = $runtime['progress_saved'] ?? false;
        $summary['saved_xp'] = $runtime['saved_xp'] ?? null;
        $summary['saved_stars'] = $runtime['saved_stars'] ?? null;

        $nextLesson = null;
        $courseId = $lesson->unit?->course_id;

        if ($courseId !== null) {
            $lessonStates = app(LessonAccess::class)->forCourse(
                (int) $courseId,
                $request->user()
            );

            $passedCurrentLesson = false;

            foreach ($lessonStates as $lessonId => $state) {
                if ((int) $lessonId === (int) $lesson->id) {
                    $passedCurrentLesson = true;
                    continue;
                }

                if (!$passedCurrentLesson || !$state['available']) {
                    continue;
                }

                $nextLesson = Lesson::find($lessonId);

                if ($nextLesson !== null) {
                    break;
                }
            }
        }

        return view('frontend.lesson-summary', [
            'lesson' => $lesson,
            'summary' => $summary,
            'nextLesson' => $nextLesson,
        ]);
    }

    private function runtimeIsComplete(array $runtime, array $flow): bool
    {
        $total = count($flow);

        if (
            $total === 0
            || ($runtime['finished_at'] ?? null) === null
            || ($runtime['total_steps'] ?? null) !== $total
            || ($runtime['next_step'] ?? null) !== $total + 1
        ) {
            return false;
        }

        $questionCount = 0;

        foreach ($flow as $item) {
            if ($item['type'] !== 'review') {
                continue;
            }

            $questionCount++;
            $result = $runtime['results'][$item['question']->id] ?? [];

            if (
                ($result['solved'] ?? false) !== true
                || !is_bool($result['first_correct'] ?? null)
                || ($result['attempts'] ?? 0) < 1
            ) {
                return false;
            }
        }

        return ($runtime['question_count'] ?? null) === $questionCount
            && count($runtime['results'] ?? []) === $questionCount;
    }

    private function buildLessonSummary(array $runtime): array
    {
        $results = collect($runtime['results']);
        $questionCount = $runtime['question_count'];

        $correctCount = $results
            ->filter(
                static fn (array $result): bool =>
                    $result['first_correct'] === true
            )
            ->count();

        $elapsedSeconds = max(
            0,
            $runtime['finished_at'] - $runtime['started_at']
        );

        return [
            'vocabulary_count' => $runtime['vocabulary_count'],
            'question_count' => $questionCount,
            'content_count' => $runtime['total_steps'],
            'correct_count' => $correctCount,
            'wrong_count' => $questionCount - $correctCount,
            'wrong_attempts' => (int) $results->sum('wrong_attempts'),
            'score_percent' => $questionCount > 0
                ? round(($correctCount / $questionCount) * 100, 2)
                : null,
            'elapsed_seconds' => $elapsedSeconds,
            'elapsed_display' => sprintf(
                '%d นาที %02d วินาที',
                intdiv($elapsedSeconds, 60),
                $elapsedSeconds % 60
            ),
            'completed_at' => $runtime['finished_at'],
        ];
    }

    private function runtimeKey(Request $request, Lesson $lesson): string
    {
        $owner = $request->user()?->id ?? 'guest';

        return 'learning_runtime.' . $owner . '.' . $lesson->id;
    }

    private function getRuntime(
        Request $request,
        Lesson $lesson,
        array $flow,
        bool $restart = false
    ): array {
        $key = $this->runtimeKey($request, $lesson);

        $stepKeys = array_map(
            static fn (array $item): string =>
                $item['type'] === 'vocabulary'
                    ? 'vocabulary:' . $item['vocabulary']->id
                    : 'review:' . $item['exercise']->id
                        . ':' . $item['question']->id
                        . ':' . $item['exercise_type'],
            $flow
        );

        $signature = hash('sha256', implode('|', $stepKeys));
        $runtime = $request->session()->get($key);

        if (
            $restart
            || !is_array($runtime)
            || ($runtime['signature'] ?? null) !== $signature
        ) {
            $questionCount = count(array_filter(
                $flow,
                static fn (array $item): bool =>
                    $item['type'] === 'review'
            ));

            $runtime = [
                'signature' => $signature,
                'started_at' => now()->timestamp,
                'finished_at' => null,
                'next_step' => 1,
                'total_steps' => count($flow),
                'question_count' => $questionCount,
                'vocabulary_count' => count($flow) - $questionCount,
                'results' => [],
                'progress_saved' => false,
                'saved_xp' => null,
                'saved_stars' => null,
            ];

            $request->session()->put($key, $runtime);
        }

        return $runtime;
    }

    private function saveRuntime(
        Request $request,
        Lesson $lesson,
        array $runtime,
        array $flow
    ): void {
        if (
            count($flow) > 0
            && $runtime['next_step'] === count($flow) + 1
            && $runtime['finished_at'] === null
        ) {
            $runtime['finished_at'] = now()->timestamp;
        }

        if ($this->runtimeIsComplete($runtime, $flow)) {
            $user = $request->user();

            if (
                $user
                && !($runtime['progress_saved'] ?? false)
                && $this->progress->available()
            ) {
                $summary = $this->buildLessonSummary($runtime);

                $rewards = $this->rewardCalculator->calculate(
                    $summary['question_count'],
                    $summary['correct_count']
                );

                $savedProgress = $this->progress->complete(
                    $user,
                    $lesson,
                    $rewards['xp'],
                    $rewards['stars']
                );

                $runtime['progress_saved'] =
                    $savedProgress->completed_at !== null;

                $runtime['saved_xp'] = (int) $savedProgress->xp;
                $runtime['saved_stars'] = (int) $savedProgress->stars;
            }
        }

        $request->session()->put(
            $this->runtimeKey($request, $lesson),
            $runtime
        );
    }

    private function buildLessonFlow(Lesson $lesson): array
    {
        $lesson->load('vocabularies');

        $supportedTypes = [
            'multiple_choice',
            'fill_blank',
            'listening',
            'image_choice',
        ];

        $reviewsByType = Exercise::with('questions.answers')
            ->where('lesson_id', $lesson->id)
            ->whereIn('type', $supportedTypes)
            ->orderBy('id')
            ->get()
            ->groupBy('type');

        $flow = [];

        // เรียนคำศัพท์ทั้งหมดก่อนเริ่มคำถาม
        foreach ($lesson->vocabularies->sortBy('id') as $vocabulary) {
            $flow[] = [
                'type' => 'vocabulary',
                'vocabulary' => $vocabulary,
            ];
        }

        // เล่นทุกคำถามของทุก Exercise ตามประเภท
        foreach ($supportedTypes as $type) {
            foreach ($reviewsByType->get($type, collect()) as $exercise) {
                foreach ($exercise->questions->sortBy('id') as $question) {
                    $flow[] = [
                        'type' => 'review',
                        'exercise_type' => $exercise->type,
                        'exercise' => $exercise,
                        'question' => $question,
                    ];
                }
            }
        }

        return $flow;
    }

    private function ensureLessonAccessible(
        Request $request,
        Lesson $lesson
    ): void {
        abort_unless(
            app(LessonAccess::class)->canLearn($request->user(), $lesson),
            403,
            'บทนี้ยังไม่เปิดให้เรียน กรุณาเรียนบทก่อนหน้าให้จบ'
        );
    }
}