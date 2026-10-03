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

    public function learn(Request $request, Lesson $lesson)
    {
        $flow = $this->buildLessonFlow($lesson);

        // เริ่มเรียนใหม่และล้างผลของรอบเดิม
        $this->getRuntime($request, $lesson, $flow, true);

        return redirect()->route('lessons.learn.step', [
            'lesson' => $lesson->id,
            'step' => 1,
        ]);
    }

    public function learnStep(Request $request, Lesson $lesson, int $step)
    {
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
            // เปิด URL ท้ายบทอย่างเดียวไม่ถือว่าเรียนจบ
            if ($runtime['finished_at'] === null) {
                return redirect()->route('lessons.learn.step', [
                    'lesson' => $lesson->id,
                    'step' => $runtime['next_step'],
                ]);
            }

            return redirect()->route('lessons.learn.summary', [
                'lesson' => $lesson->id,
            ]);
        }

        $current = $flow[$step - 1];

        $result = $current['type'] === 'review'
            ? ($runtime['results'][$current['question']->id] ?? null)
            : null;

        $reviewResult = $result
            ? ($result['solved'] ? 'correct' : 'wrong')
            : null;

        $selectedAnswer = $result['selected_answer'] ?? null;

        return view('frontend.learn-step', [
            'lesson' => $lesson,
            'current' => $current,
            'step' => $step,
            'total' => $total,
            'reviewResult' => $reviewResult,
            'selectedAnswer' => $selectedAnswer,
        ]);
    }

    public function submitLearnStep(
        Request $request,
        Lesson $lesson,
        int $step,
        QuizAnswerChecker $checker
    ) {
        $flow = $this->buildLessonFlow($lesson);

        if (count($flow) === 0) {
            return redirect()->route('units.lessons', [
                'unit' => $lesson->unit_id,
            ]);
        }

        $runtime = $this->getRuntime($request, $lesson, $flow);
        $current = $flow[$step - 1] ?? null;

        // รับคำตอบเฉพาะ step ที่ยังต้องทำ
        // ส่งข้อเดิมซ้ำจะไม่เพิ่ม attempts หรือเปลี่ยนผลครั้งแรก
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

        // ยืนยันว่าเรียนคำศัพท์นี้แล้วด้วย POST
        if ($current['type'] === 'vocabulary') {
            $runtime['next_step']++;

            $this->saveRuntime($request, $lesson, $runtime);

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

        // ตัวเลือกต้องเป็นของคำถามปัจจุบัน
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

        // บันทึกผลครั้งแรกเพียงครั้งเดียว
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

        // ตอบผิดอยู่ข้อเดิม ตอบถูกจึงทำข้อถัดไปได้
        if ($correct) {
            $runtime['next_step']++;
        }

        $this->saveRuntime($request, $lesson, $runtime);

        // แสดง feedback ก่อนกดถัดไป
        return redirect()->route('lessons.learn.step', [
            'lesson' => $lesson->id,
            'step' => $step,
        ]);
    }

    public function lessonSummary(Request $request, Lesson $lesson)
    {
        $flow = $this->buildLessonFlow($lesson);
        $total = count($flow);

        if ($total === 0) {
            return redirect()->route('units.lessons', [
                'unit' => $lesson->unit_id,
            ]);
        }

        $runtime = $this->getRuntime($request, $lesson, $flow);

        $isComplete = $runtime['finished_at'] !== null
            && $runtime['next_step'] === $total + 1
            && count($runtime['results']) === $runtime['question_count'];

        // ตรวจว่าทุกคำถามผ่านแล้วและมีผลครั้งแรก
        foreach ($flow as $item) {
            if ($item['type'] !== 'review') {
                continue;
            }

            $result = $runtime['results'][$item['question']->id] ?? [];

            if (
                ($result['solved'] ?? false) !== true
                || !is_bool($result['first_correct'] ?? null)
            ) {
                $isComplete = false;
            }
        }

        if (!$isComplete) {
            return redirect()->route('lessons.learn.step', [
                'lesson' => $lesson->id,
                'step' => min($runtime['next_step'], $total),
            ]);
        }

        $summary = $this->buildLessonSummary($runtime);

        // หาบทถัดไปใน Unit เดิมตามลำดับ
        $nextLesson = $lesson->nextLesson();

        // เมื่อหมด Unit ให้หาบทแรกของ Unit ถัดไปใน Course เดิม
        if (!$nextLesson) {
            $nextUnit = $lesson->unit?->nextUnit();

            while (!$nextLesson && $nextUnit) {
                $nextLesson = $nextUnit->lessons()->first();

                if (!$nextLesson) {
                    $nextUnit = $nextUnit->nextUnit();
                }
            }
        }

        return view('frontend.lesson-summary', [
            'lesson' => $lesson,
            'summary' => $summary,
            'nextLesson' => $nextLesson,
        ]);
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

        $wrongCount = $questionCount - $correctCount;

        $elapsedSeconds = max(
            0,
            $runtime['finished_at'] - $runtime['started_at']
        );

        return [
            'vocabulary_count' => $runtime['vocabulary_count'],
            'question_count' => $questionCount,
            'content_count' => $runtime['total_steps'],
            'correct_count' => $correctCount,
            'wrong_count' => $wrongCount,
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

        // เมื่อรายการ step เปลี่ยน ให้เริ่มรอบใหม่
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
            ];

            $request->session()->put($key, $runtime);
        }

        return $runtime;
    }

    private function saveRuntime(
        Request $request,
        Lesson $lesson,
        array $runtime
    ): void {
        if (
            $runtime['total_steps'] > 0
            && $runtime['next_step'] > $runtime['total_steps']
            && $runtime['finished_at'] === null
        ) {
            // จับเวลาจบครั้งเดียว เมื่อยืนยันครบทุก step
            $runtime['finished_at'] = now()->timestamp;
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

        // เพิ่มทุกคำถามจากทุก Exercise ของประเภทที่กำหนด
        $appendReviews = function (string $type) use (
            &$flow,
            $reviewsByType
        ): void {
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

            // ป้องกันการเพิ่มกลุ่มเดิมซ้ำ
            $reviewsByType->forget($type);
        };

        $vocabularies = $lesson->vocabularies
            ->sortBy('id')
            ->values();

        foreach ($vocabularies as $index => $vocabulary) {
            $flow[] = [
                'type' => 'vocabulary',
                'vocabulary' => $vocabulary,
            ];

            if ($index === 1) {
                $appendReviews('multiple_choice');
            }

            if ($index === 3) {
                $appendReviews('fill_blank');
            }
        }

        // เพิ่มประเภทที่เหลือ รวมถึงบทที่มีคำศัพท์น้อยหรือไม่มีเลย
        foreach ($supportedTypes as $type) {
            $appendReviews($type);
        }

        return $flow;
    }
}