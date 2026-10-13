<?php

namespace App\Http\Controllers\TeacherCourses;

use App\Http\Controllers\Controller;
use App\Http\Requests\TeacherCourseRequest;
use App\Models\CourseEnrollment;
use App\Models\Language;
use App\Models\TeacherCourse;
use App\Services\TeacherCourses\CourseReviewService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/** Approved teacher: create / edit own courses, send them for admin review, close / reopen them. */
class TeacherCourseManagementController extends Controller
{
    public function index(Request $request): View
    {
        $courses = TeacherCourse::query()
            ->where('teacher_id', $request->user()->id)
            ->with('language')
            ->withCount([
                'lessons',
                'teacherLessons',
                'enrollments as learner_count' => fn ($q) => $q->learning(),
                'enrollments as waiting_count' => fn ($q) => $q->whereIn('status', [CourseEnrollment::PENDING, CourseEnrollment::APPROVED]),
            ])
            ->latest('updated_at')
            ->get();

        return view('teacher-courses.manage.index', compact('courses'));
    }

    public function create(Request $request): View
    {
        Gate::authorize('create', TeacherCourse::class);

        return view('teacher-courses.manage.form', [
            'course' => new TeacherCourse([
                'level' => 'beginner',
                'price_type' => TeacherCourse::FREE,
                'enrollment_mode' => TeacherCourse::INSTANT,
            ]),
            'selectedLessons' => [],
            'languages' => $this->languagesWithLessons(),
        ]);
    }

    public function store(TeacherCourseRequest $request): RedirectResponse
    {
        $course = DB::transaction(function () use ($request) {
            $course = new TeacherCourse($request->safe()->except('lessons'));
            $course->teacher_id = $request->user()->id;
            $course->status = TeacherCourse::DRAFT;
            $course->save();
            $this->syncLessons($course, $request->validated('lessons', []));

            return $course;
        });

        return redirect()
            ->route('teacher.courses.edit', $course)
            ->with('success', 'สร้างคอร์สแล้ว (ฉบับร่าง) เพิ่มบทเรียนของคุณ แล้วกด "ส่งให้แอดมินตรวจ" เมื่อพร้อม');
    }

    public function edit(TeacherCourse $teacherCourse): View
    {
        Gate::authorize('update', $teacherCourse);

        return view('teacher-courses.manage.form', [
            'course' => $teacherCourse,
            'selectedLessons' => $teacherCourse->lessons()->pluck('lessons.id')->all(),
            'teacherLessons' => $teacherCourse->teacherLessons()->get(),
            'languages' => $this->languagesWithLessons(),
        ]);
    }

    public function update(TeacherCourseRequest $request, TeacherCourse $teacherCourse, CourseReviewService $review): RedirectResponse
    {
        [$changed, $sentForReview] = DB::transaction(function () use ($request, $teacherCourse, $review) {
            TeacherCourse::query()->lockForUpdate()->findOrFail($teacherCourse->id);
            $teacherCourse->fill($request->safe()->except('lessons'));
            $infoChanged = $teacherCourse->isDirty();
            $teacherCourse->save();
            $changed = $this->syncLessons($teacherCourse, $request->validated('lessons', [])) || $infoChanged;

            return [$changed, $changed && $review->contentChanged($teacherCourse)];
        });

        if ($sentForReview) {
            return back()->with('success', 'บันทึกแล้ว คอร์สกลับไปรอแอดมินตรวจอีกครั้ง (ผู้เรียนเดิมยังเรียนต่อได้)');
        }

        return back()->with('success', $changed ? 'บันทึกการแก้ไขแล้ว' : 'ไม่มีอะไรเปลี่ยนแปลง');
    }

    /** submit → admin review, withdraw the request, close, or reopen a still-approved course. */
    public function updateStatus(Request $request, TeacherCourse $teacherCourse, CourseReviewService $review): RedirectResponse
    {
        Gate::authorize('update', $teacherCourse);

        $action = $request->validate([
            'action' => ['required', Rule::in(['submit', 'withdraw', 'close', 'reopen'])],
        ], ['action.*' => 'คำสั่งไม่ถูกต้อง'])['action'];

        $review->{$action}($teacherCourse);

        $message = [
            'submit' => 'ส่งคอร์สให้แอดมินตรวจแล้ว คอร์สจะเผยแพร่เมื่อได้รับอนุมัติ',
            'withdraw' => 'ถอนคำขอตรวจแล้ว แก้ไขต่อได้เลย',
            'close' => 'ปิดคอร์สแล้ว ผู้เรียนเดิมยังเรียนต่อได้',
            'reopen' => 'เปิดรับสมัครอีกครั้งแล้ว',
        ][$action];

        return back()->with('success', $message);
    }

    /**
     * @param  array<int, int|string>  $lessonIds
     * @return bool whether the curriculum changed
     */
    private function syncLessons(TeacherCourse $course, array $lessonIds): bool
    {
        $wanted = collect($lessonIds)->values()->mapWithKeys(fn ($id, $index) => [(int) $id => $index + 1]);
        $current = $course->lessons()->get(['lessons.id'])->mapWithKeys(fn ($lesson) => [(int) $lesson->id => (int) $lesson->pivot->position]);

        $course->lessons()->sync($wanted->map(fn (int $position) => ['position' => $position])->all());

        // sync() reports unchanged pivot rows as "updated", so compare ids and positions directly.
        return $wanted->sortKeys()->all() !== $current->sortKeys()->all();
    }

    private function languagesWithLessons()
    {
        return Language::query()
            ->orderBy('name')
            ->with(['courses.units.lessons' => fn ($q) => $q->select('lessons.id', 'lessons.unit_id', 'lessons.title', 'lessons.position')])
            ->get();
    }
}
