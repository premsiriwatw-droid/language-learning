<?php

namespace App\Http\Controllers\TeacherCourses;

use App\Http\Controllers\Controller;
use App\Models\TeacherCourse;
use App\Services\TeacherCourses\CourseReviewService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/** Admin: check teacher courses before they are published to learners. */
class CourseReviewController extends Controller
{
    public function index(Request $request): View
    {
        $tab = $request->validate(['tab' => ['nullable', Rule::in(['pending', 'all'])]])['tab'] ?? 'pending';

        $courses = TeacherCourse::query()
            ->when($tab === 'pending', fn ($q) => $q->where('status', TeacherCourse::PENDING_REVIEW))
            ->when($tab === 'all', fn ($q) => $q->where('status', '!=', TeacherCourse::DRAFT))
            ->with(['language', 'teacher:id,name,email', 'teacher.teacherApplication:id,user_id,full_name'])
            ->withCount(['lessons', 'teacherLessons'])
            ->orderByRaw('submitted_at IS NULL')
            ->orderBy($tab === 'pending' ? 'submitted_at' : 'updated_at', $tab === 'pending' ? 'asc' : 'desc')
            ->paginate(20)
            ->withQueryString();

        return view('teacher-courses.admin.index', [
            'courses' => $courses,
            'tab' => $tab,
            'pendingCount' => TeacherCourse::where('status', TeacherCourse::PENDING_REVIEW)->count(),
        ]);
    }

    public function show(TeacherCourse $teacherCourse): View
    {
        $teacherCourse->load([
            'language', 'reviewer:id,name', 'lessons.unit', 'teacherLessons',
            'teacher:id,name,email,role', 'teacher.teacherApplication:id,user_id,full_name,status',
        ])->loadCount(['enrollments as learner_count' => fn ($q) => $q->learning()]);

        return view('teacher-courses.admin.show', [
            'course' => $teacherCourse,
            'teacherVerified' => $teacherCourse->teacher->isApprovedTeacher(),
        ]);
    }

    public function decide(Request $request, TeacherCourse $teacherCourse, CourseReviewService $review): RedirectResponse
    {
        Gate::authorize('review', $teacherCourse);

        $data = $request->validate([
            'decision' => ['required', Rule::in(['approve', 'reject'])],
            'review_note' => ['nullable', 'string', 'max:1000'],
        ], [
            'decision.*' => 'เลือกผลการตรวจ',
            'review_note.max' => 'หมายเหตุยาวเกินไป (ไม่เกิน 1000 ตัวอักษร)',
        ]);

        if ($data['decision'] === 'approve') {
            $review->approve($teacherCourse, $request->user(), $data['review_note'] ?? null);

            return redirect()->route('admin.teacher-courses.index')
                ->with('success', 'อนุมัติ "'.$teacherCourse->title.'" แล้ว คอร์สเผยแพร่ให้ผู้เรียนเห็นแล้ว');
        }

        $review->reject($teacherCourse, $request->user(), (string) ($data['review_note'] ?? ''));

        return redirect()->route('admin.teacher-courses.index')
            ->with('success', 'ส่ง "'.$teacherCourse->title.'" กลับให้ผู้สอนแก้ไขแล้ว');
    }
}
