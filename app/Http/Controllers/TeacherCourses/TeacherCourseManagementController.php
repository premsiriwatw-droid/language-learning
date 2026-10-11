<?php

namespace App\Http\Controllers\TeacherCourses;

use App\Http\Controllers\Controller;
use App\Http\Requests\TeacherCourseRequest;
use App\Models\CourseEnrollment;
use App\Models\Language;
use App\Models\TeacherCourse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

/** Approved teacher: create / edit / publish / close own courses. */
class TeacherCourseManagementController extends Controller
{
    public function index(Request $request): View
    {
        $courses = TeacherCourse::query()
            ->where('teacher_id', $request->user()->id)
            ->with('language')
            ->withCount([
                'lessons',
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
            ->with('success', 'สร้างคอร์สแล้ว (ฉบับร่าง) กด "เปิดรับสมัคร" เมื่อพร้อม');
    }

    public function edit(TeacherCourse $teacherCourse): View
    {
        Gate::authorize('update', $teacherCourse);

        return view('teacher-courses.manage.form', [
            'course' => $teacherCourse,
            'selectedLessons' => $teacherCourse->lessons()->pluck('lessons.id')->all(),
            'languages' => $this->languagesWithLessons(),
        ]);
    }

    public function update(TeacherCourseRequest $request, TeacherCourse $teacherCourse): RedirectResponse
    {
        DB::transaction(function () use ($request, $teacherCourse) {
            $teacherCourse->fill($request->safe()->except('lessons'))->save();
            $this->syncLessons($teacherCourse, $request->validated('lessons', []));
        });

        return back()->with('success', 'บันทึกการแก้ไขแล้ว');
    }

    public function updateStatus(Request $request, TeacherCourse $teacherCourse): RedirectResponse
    {
        Gate::authorize('update', $teacherCourse);

        $status = $request->validate([
            'status' => ['required', Rule::in(TeacherCourse::STATUSES)],
        ])['status'];

        if ($status === TeacherCourse::PUBLISHED && $teacherCourse->lessons()->doesntExist()) {
            throw ValidationException::withMessages(['status' => 'เพิ่มบทเรียนอย่างน้อย 1 บทก่อนเปิดรับสมัคร']);
        }

        if ($status === TeacherCourse::PUBLISHED && $teacherCourse->language_id === null) {
            throw ValidationException::withMessages(['status' => 'เลือกภาษาของคอร์สก่อนเปิดรับสมัคร']);
        }

        // A draft is invisible to learners, so it cannot hold anyone who already joined.
        if (
            $status === TeacherCourse::DRAFT
            && $teacherCourse->enrollments()->where('status', '!=', CourseEnrollment::REJECTED)->exists()
        ) {
            throw ValidationException::withMessages(['status' => 'คอร์สมีผู้เรียนหรือคำขอแล้ว เปลี่ยนเป็นฉบับร่างไม่ได้ ใช้ "ปิดคอร์ส" แทน']);
        }

        $teacherCourse->status = $status;
        if ($status === TeacherCourse::PUBLISHED && $teacherCourse->published_at === null) {
            $teacherCourse->published_at = now();
        }
        $teacherCourse->save();

        return back()->with('success', 'สถานะคอร์ส: '.TeacherCourse::STATUS_LABELS[$status]);
    }

    /** @param array<int, int|string> $lessonIds */
    private function syncLessons(TeacherCourse $course, array $lessonIds): void
    {
        $course->lessons()->sync(
            collect($lessonIds)->values()->mapWithKeys(fn ($id, $index) => [(int) $id => ['position' => $index + 1]])->all()
        );
    }

    private function languagesWithLessons()
    {
        return Language::query()
            ->orderBy('name')
            ->with(['courses.units.lessons' => fn ($q) => $q->select('lessons.id', 'lessons.unit_id', 'lessons.title', 'lessons.position')])
            ->get();
    }
}
