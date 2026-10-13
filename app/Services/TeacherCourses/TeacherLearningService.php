<?php

namespace App\Services\TeacherCourses;

use App\Events\TeacherCourses\TeacherLearningActivity;
use App\Models\AssignmentStatus;
use App\Models\CourseAssignment;
use App\Models\CourseConversation;
use App\Models\CourseMessage;
use App\Models\LearningAttempt;
use App\Models\Lesson;
use App\Models\TeacherCourse;
use App\Models\TeacherRecommendation;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;

class TeacherLearningService
{
    public function teacherStudent(User $teacher, TeacherCourse $course, string|int $studentId): User
    {
        Gate::forUser($teacher)->authorize('update', $course);

        $enrollment = $course->enrollments()->learning()
            ->where('user_id', $studentId)->with('user')->firstOrFail();

        abort_if($enrollment->user === null, 404);

        return $enrollment->user;
    }

    public function learner(User $student, TeacherCourse $course): void
    {
        abort_unless(
            $course->enrollments()->learning()->where('user_id', $student->id)->exists(),
            404
        );
    }

    private function participant(User $actor, TeacherCourse $course, User $student): void
    {
        if ((int) $actor->id === (int) $course->teacher_id) {
            $this->teacherStudent($actor, $course, $student->id);
        } else {
            abort_unless((int) $actor->id === (int) $student->id, 404);
            $this->learner($actor, $course);
        }
    }

    public function panel(User $actor, TeacherCourse $course, User $student): array
    {
        $this->participant($actor, $course, $student);

        $lessons = $course->lessons()->with('unit:id,title')
            ->get(['lessons.id', 'lessons.title', 'lessons.unit_id']);

        $conversation = CourseConversation::query()
            ->where('teacher_course_id', $course->id)
            ->where('student_id', $student->id)->first(['id']);

        $assignments = AssignmentStatus::query()
            ->where('student_id', $student->id)
            ->whereHas('assignment', fn (Builder $query) => $query
                ->where('teacher_course_id', $course->id))
            ->with('assignment')->orderByDesc('id')
            ->paginate(10, ['*'], 'assignments_page')->withQueryString();
        $assignmentLessonIds = $assignments->getCollection()
            ->flatMap(fn (AssignmentStatus $status): array => $status->assignment->lesson_ids)
            ->unique()->values();

        return [
            'lessons' => $lessons,
            'units' => $lessons->map(fn (Lesson $lesson) => $lesson->unit)
                ->filter()->unique('id')->values(),
            'recommendations' => TeacherRecommendation::query()
                ->where('teacher_course_id', $course->id)
                ->where('student_id', $student->id)->with('lesson:id,title')
                ->orderByDesc('id')->paginate(10, ['*'], 'recommendations_page')
                ->withQueryString(),
            'assignments' => $assignments,
            'assignment_lessons' => Lesson::query()
                ->whereIn('id', $assignmentLessonIds)->pluck('title', 'id'),
            'messages' => CourseMessage::query()
                ->where('course_conversation_id', $conversation?->id ?? 0)
                ->with('sender:id,name')->orderByDesc('id')
                ->paginate(20, ['*'], 'messages_page')->withQueryString(),
        ];
    }

    public function recommend(
        User $teacher,
        TeacherCourse $course,
        string|int $studentId,
        int $lessonId,
        string $reason
    ): TeacherRecommendation {
        return DB::transaction(function () use ($teacher, $course, $studentId, $lessonId, $reason) {
            $student = $this->teacherStudent($teacher, $course, $studentId);
            $lesson = $course->lessons()->where('lessons.id', $lessonId)->firstOrFail();

            $recommendation = TeacherRecommendation::create([
                'teacher_course_id' => $course->id,
                'student_id' => $student->id,
                'lesson_id' => $lesson->id,
                'lesson_title' => $lesson->title,
                'reason' => $this->text($reason, 'reason'),
            ]);

            TeacherLearningActivity::dispatch(
                'recommendation.created', $course->id,
                $teacher->id, $student->id, $recommendation->id
            );

            return $recommendation;
        });
    }

    public function assign(
        User $teacher,
        TeacherCourse $course,
        string|int $studentId,
        array $data
    ): CourseAssignment {
        return DB::transaction(function () use ($teacher, $course, $studentId, $data) {
            $student = $this->teacherStudent($teacher, $course, $studentId);

            if (! preg_match('/^(lesson|unit):([1-9][0-9]*)$/', $data['target'] ?? '', $target)) {
                $this->fail('target', 'เลือกบทเรียนหรือ Unit ให้ถูกต้อง');
            }

            $kind = $target[1];
            $targetId = $target[2];

            // A Unit assignment includes only lessons in this teacher course.
            $lessons = $course->lessons()->with('unit')
                ->where($kind === 'lesson' ? 'lessons.id' : 'lessons.unit_id', $targetId)
                ->get();

            abort_if($lessons->isEmpty(), 404);

            $dueAt = Carbon::parse($data['due_at'], config('app.timezone'));
            if ($dueAt->lessThanOrEqualTo(now())) {
                $this->fail('due_at', 'กำหนดส่งต้องเป็นเวลาในอนาคต');
            }

            $assignment = CourseAssignment::create([
                'teacher_course_id' => $course->id,
                'title' => $this->text($data['title'], 'title', 160),
                'instructions' => isset($data['instructions'])
                    ? $this->text($data['instructions'], 'instructions') : null,
                'target_kind' => $kind,
                'lesson_id' => $kind === 'lesson' ? $lessons->first()->id : null,
                'unit_id' => $kind === 'unit' ? $lessons->first()->unit_id : null,
                'target_label' => $kind === 'lesson'
                    ? $lessons->first()->title
                    : ($lessons->first()->unit?->title ?? 'Unit'),
                'lesson_ids' => $lessons->modelKeys(),
                'due_at' => $dueAt,
            ]);

            AssignmentStatus::create([
                'course_assignment_id' => $assignment->id,
                'student_id' => $student->id,
                'status' => AssignmentStatus::PENDING,
                // An existing completion, even in the same second, cannot finish this task.
                'attempt_baseline' => (int) LearningAttempt::query()
                    ->where('user_id', $student->id)->max('id'),
            ]);

            TeacherLearningActivity::dispatch(
                'assignment.created', $course->id,
                $teacher->id, $student->id, $assignment->id
            );

            return $assignment;
        });
    }

    public function start(User $student, TeacherCourse $course, string|int $assignmentId): void
    {
        DB::transaction(function () use ($student, $course, $assignmentId): void {
            $status = $this->ownAssignment($student, $course, $assignmentId);

            if ($status->status === AssignmentStatus::PENDING) {
                $status->update(['status' => AssignmentStatus::IN_PROGRESS]);
            }
        });
    }

    public function complete(User $student, TeacherCourse $course, string|int $assignmentId): void
    {
        DB::transaction(function () use ($student, $course, $assignmentId): void {
            $status = $this->ownAssignment($student, $course, $assignmentId);
            if ($status->status === AssignmentStatus::COMPLETED) {
                return;
            }

            $assignment = $status->assignment;
            $lessonIds = array_values(array_unique(array_map('intval', $assignment->lesson_ids ?? [])));

            if ($lessonIds === [] || Lesson::query()->whereIn('id', $lessonIds)->count() !== count($lessonIds)) {
                $this->fail('assignment', 'บทเรียนของงานนี้ไม่พร้อมใช้งาน กรุณาติดต่อผู้สอน');
            }

            $finished = LearningAttempt::query()
                ->where('user_id', $student->id)
                ->whereIn('lesson_id', $lessonIds)
                ->where('id', '>', $status->attempt_baseline)
                ->whereBetween('completed_at', [$assignment->created_at, now()])
                ->distinct()->pluck('lesson_id')->count();

            if ($finished !== count($lessonIds)) {
                $this->fail('assignment', 'ต้องเรียนบทในงานนี้ให้ครบหลังได้รับงานก่อนยืนยันว่าเสร็จ');
            }

            $status->update([
                'status' => AssignmentStatus::COMPLETED,
                'completed_at' => now(),
            ]);

            TeacherLearningActivity::dispatch(
                'assignment.completed', $course->id,
                $student->id, $course->teacher_id, $assignment->id
            );
        });
    }

    private function ownAssignment(
        User $student,
        TeacherCourse $course,
        string|int $assignmentId
    ): AssignmentStatus {
        $this->learner($student, $course);

        return AssignmentStatus::query()->where('student_id', $student->id)
            ->where('course_assignment_id', $assignmentId)
            ->whereHas('assignment', fn (Builder $query) => $query
                ->where('teacher_course_id', $course->id))
            ->with('assignment')->lockForUpdate()->firstOrFail();
    }

    public function message(
        User $actor,
        TeacherCourse $course,
        User $student,
        string $body
    ): CourseMessage {
        return DB::transaction(function () use ($actor, $course, $student, $body) {
            $this->participant($actor, $course, $student);

            if ((int) $actor->id !== (int) $course->teacher_id) {
                abort_unless($course->teacher?->isApprovedTeacher(), 403);
            }

            $conversation = CourseConversation::firstOrCreate([
                'teacher_course_id' => $course->id,
                'student_id' => $student->id,
            ]);

            $message = CourseMessage::create([
                'course_conversation_id' => $conversation->id,
                'sender_id' => $actor->id,
                'body' => $this->text($body, 'body'),
            ]);

            $recipient = (int) $actor->id === (int) $course->teacher_id
                ? $student->id : $course->teacher_id;

            TeacherLearningActivity::dispatch(
                'message.sent', $course->id, $actor->id, $recipient, $message->id
            );

            return $message;
        });
    }

    private function text(string $value, string $field, int $max = 2000): string
    {
        $value = trim($value);
        if ($value === '' || mb_strlen($value) > $max) {
            $this->fail($field, "กรุณากรอกข้อความไม่เกิน {$max} ตัวอักษร");
        }

        return $value;
    }

    private function fail(string $field, string $message): never
    {
        throw ValidationException::withMessages([$field => $message]);
    }
}
