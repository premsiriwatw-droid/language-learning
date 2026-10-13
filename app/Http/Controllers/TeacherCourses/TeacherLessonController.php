<?php

namespace App\Http\Controllers\TeacherCourses;

use App\Http\Controllers\Controller;
use App\Http\Requests\TeacherLessonRequest;
use App\Models\TeacherCourse;
use App\Models\TeacherLesson;
use App\Services\TeacherCourses\CourseReviewService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Throwable;

/** Approved teacher writes lessons inside their own course: text, one document and one video each. */
class TeacherLessonController extends Controller
{
    public function __construct(private readonly CourseReviewService $review)
    {
    }

    public function create(TeacherCourse $teacherCourse): View
    {
        Gate::authorize('update', $teacherCourse);

        return view('teacher-courses.manage.lesson-form', [
            'course' => $teacherCourse,
            'lesson' => new TeacherLesson(),
        ]);
    }

    public function store(TeacherLessonRequest $request, TeacherCourse $teacherCourse): RedirectResponse
    {
        $lesson = new TeacherLesson();

        $sentForReview = $this->save($request, $teacherCourse, $lesson, function (TeacherLesson $lesson) use ($teacherCourse) {
            $lesson->teacher_course_id = $teacherCourse->id;
            $lesson->position = (int) $teacherCourse->teacherLessons()->max('position') + 1;
        });

        return $this->backToCourse($teacherCourse, 'เพิ่มบทเรียน "'.$lesson->title.'" แล้ว', $sentForReview);
    }

    public function edit(TeacherCourse $teacherCourse, TeacherLesson $teacherLesson): View
    {
        Gate::authorize('update', $teacherCourse);

        return view('teacher-courses.manage.lesson-form', [
            'course' => $teacherCourse,
            'lesson' => $teacherLesson,
        ]);
    }

    public function update(TeacherLessonRequest $request, TeacherCourse $teacherCourse, TeacherLesson $teacherLesson): RedirectResponse
    {
        $sentForReview = $this->save($request, $teacherCourse, $teacherLesson);

        return $this->backToCourse($teacherCourse, 'บันทึกบทเรียน "'.$teacherLesson->title.'" แล้ว', $sentForReview);
    }

    public function destroy(TeacherCourse $teacherCourse, TeacherLesson $teacherLesson): RedirectResponse
    {
        Gate::authorize('update', $teacherCourse);

        $files = array_filter([$teacherLesson->document_path, $teacherLesson->video_path]);

        $sentForReview = DB::transaction(function () use ($teacherCourse, $teacherLesson) {
            // Decide on the row read under the lock: the course may have been submitted
            // (or published) after this request loaded it.
            $locked = TeacherCourse::query()->lockForUpdate()->findOrFail($teacherCourse->id);

            $remaining = $locked->teacherLessons()->whereKeyNot($teacherLesson->id)->count() + $locked->lessons()->count();
            if ($remaining === 0 && $locked->status !== TeacherCourse::DRAFT) {
                throw ValidationException::withMessages([
                    'lesson' => 'ลบบทสุดท้ายไม่ได้ คอร์สที่ส่งตรวจ เปิดรับสมัคร หรือมีผู้เรียนแล้วต้องมีบทเรียนอย่างน้อย 1 บท',
                ]);
            }

            $teacherLesson->delete();
            $this->renumber($locked);
            $sentForReview = $this->review->contentChanged($locked);
            $teacherCourse->setRawAttributes($locked->getAttributes(), true);

            return $sentForReview;
        });

        DB::afterCommit(fn () => Storage::disk(TeacherLesson::DISK)->delete($files));

        return $this->backToCourse($teacherCourse, 'ลบบทเรียนแล้ว', $sentForReview);
    }

    public function move(Request $request, TeacherCourse $teacherCourse, TeacherLesson $teacherLesson): RedirectResponse
    {
        Gate::authorize('update', $teacherCourse);

        $direction = $request->validate(['direction' => ['required', Rule::in(['up', 'down'])]])['direction'];

        [$moved, $sentForReview] = DB::transaction(function () use ($teacherCourse, $teacherLesson, $direction) {
            TeacherCourse::query()->lockForUpdate()->findOrFail($teacherCourse->id);
            $this->renumber($teacherCourse);

            $ordered = $teacherCourse->teacherLessons()->get()->values();
            $index = $ordered->search(fn (TeacherLesson $lesson) => $lesson->id === $teacherLesson->id);
            $swapWith = $direction === 'up' ? $index - 1 : $index + 1;

            if ($index === false || ! isset($ordered[$swapWith])) {
                return [false, false];
            }

            [$current, $other] = [$ordered[$index], $ordered[$swapWith]];
            [$currentPosition, $otherPosition] = [$current->position, $other->position];
            $current->forceFill(['position' => $otherPosition])->save();
            $other->forceFill(['position' => $currentPosition])->save();

            return [true, $this->review->contentChanged($teacherCourse)];
        });

        return $this->backToCourse($teacherCourse, $moved ? 'จัดลำดับบทเรียนแล้ว' : 'ย้ายต่อไม่ได้แล้ว', $sentForReview);
    }

    /**
     * Files are stored before the DB write; on failure the new files are removed,
     * on success the replaced files are removed after commit.
     *
     * @param  (callable(TeacherLesson): void)|null  $prepare
     * @return bool whether a published course was sent back for admin review
     */
    private function save(TeacherLessonRequest $request, TeacherCourse $course, TeacherLesson $lesson, ?callable $prepare = null): bool
    {
        $directory = 'teacher-course-materials/'.$course->id;
        $disk = Storage::disk(TeacherLesson::DISK);
        $newFiles = [];
        $oldFiles = [];

        try {
            $lesson->fill($request->safe()->only(['title', 'body', 'video_url']));
            $lesson->body = trim((string) $lesson->body) === '' ? null : $lesson->body;

            if ($request->hasFile('document')) {
                $oldFiles[] = $lesson->document_path;
                $newFiles[] = $this->attach($lesson, 'document', $request->file('document'), $directory);
            } elseif ($request->boolean('remove_document')) {
                $oldFiles[] = $lesson->document_path;
                $lesson->forceFill(['document_path' => null, 'document_name' => null, 'document_size' => null]);
            }

            if ($request->hasFile('video_file')) {
                $oldFiles[] = $lesson->video_path;
                $newFiles[] = $this->attach($lesson, 'video', $request->file('video_file'), $directory);
                $lesson->video_url = null;
            } elseif ($request->boolean('remove_video') || $lesson->video_url !== null) {
                // Removing the video, or switching from an uploaded file to a YouTube/Vimeo link.
                $oldFiles[] = $lesson->video_path;
                $lesson->forceFill(['video_path' => null, 'video_name' => null, 'video_size' => null]);
                if ($request->boolean('remove_video')) {
                    $lesson->video_url = null;
                }
            }

            $sentForReview = DB::transaction(function () use ($course, $lesson, $prepare) {
                TeacherCourse::query()->lockForUpdate()->findOrFail($course->id);
                if ($prepare) {
                    $prepare($lesson);
                }
                $changed = $lesson->isDirty();
                $lesson->save();

                return $changed && $this->review->contentChanged($course);
            });
        } catch (Throwable $exception) {
            $disk->delete(array_filter($newFiles));

            throw $exception;
        }

        $disk->delete(array_values(array_filter($oldFiles)));

        return $sentForReview;
    }

    private function attach(TeacherLesson $lesson, string $kind, UploadedFile $file, string $directory): string
    {
        // The client extension was already checked against the allow-list by the "extensions" rule.
        $path = $file->storeAs($directory, Str::uuid().'.'.strtolower($file->getClientOriginalExtension()), TeacherLesson::DISK);
        if ($path === false) {
            throw ValidationException::withMessages([$kind === 'video' ? 'video_file' : 'document' => 'บันทึกไฟล์ไม่สำเร็จ ลองใหม่อีกครั้ง']);
        }

        $lesson->forceFill([
            $kind.'_path' => $path,
            $kind.'_name' => mb_substr(basename($file->getClientOriginalName()), 0, 200),
            $kind.'_size' => $file->getSize(),
        ]);

        return $path;
    }

    private function renumber(TeacherCourse $course): void
    {
        $course->teacherLessons()->get()->values()->each(function (TeacherLesson $lesson, int $index) {
            if ($lesson->position !== $index + 1) {
                $lesson->forceFill(['position' => $index + 1])->save();
            }
        });
    }

    private function backToCourse(TeacherCourse $course, string $message, bool $sentForReview = false): RedirectResponse
    {
        if ($sentForReview) {
            $message .= ' · คอร์สกลับไปรอแอดมินตรวจอีกครั้ง (ผู้เรียนเดิมยังเรียนต่อได้)';
        }

        return redirect()->to(route('teacher.courses.edit', $course).'#my-lessons')->with('success', $message);
    }
}
