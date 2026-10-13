<?php

namespace App\Http\Controllers\TeacherCourses;

use App\Http\Controllers\Controller;
use App\Models\CourseEnrollment;
use App\Models\Language;
use App\Models\TeacherCourse;
use App\Services\Progress\LessonAccess;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/** Learner side: browse teacher courses and see own enrollments. */
class MarketplaceController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->validate([
            'language' => ['nullable', 'integer'],
            'level' => ['nullable', Rule::in(TeacherCourse::LEVELS)],
            'price' => ['nullable', Rule::in(TeacherCourse::PRICE_TYPES)],
        ]);

        $courses = TeacherCourse::query()
            ->listed()
            ->filter($filters)
            ->with(['language', 'teacher:id,name', 'teacher.teacherApplication:id,user_id,full_name'])
            ->withCount([
                'lessons',
                'teacherLessons',
                'enrollments as learner_count' => fn ($q) => $q->learning(),
            ])
            ->latest('published_at')
            ->paginate(12)
            ->withQueryString();

        $myStatuses = CourseEnrollment::query()
            ->where('user_id', $request->user()->id)
            ->whereIn('teacher_course_id', $courses->pluck('id'))
            ->pluck('status', 'teacher_course_id');

        return view('teacher-courses.marketplace', [
            'courses' => $courses,
            'filters' => $filters,
            'languages' => Language::orderBy('name')->get(['id', 'name']),
            'myStatuses' => $myStatuses,
            'isTeacher' => $request->user()->isApprovedTeacher(),
            'pendingReviewCount' => $request->user()->isAdmin()
                ? TeacherCourse::where('status', TeacherCourse::PENDING_REVIEW)->count()
                : null,
        ]);
    }

    public function show(Request $request, TeacherCourse $teacherCourse, LessonAccess $access): View
    {
        Gate::authorize('view', $teacherCourse);

        $teacherCourse->load(['language', 'teacher:id,name,role', 'teacher.teacherApplication:id,user_id,full_name,bio', 'lessons.unit', 'teacherLessons'])
            ->loadCount(['enrollments as learner_count' => fn ($q) => $q->learning()]);

        $user = $request->user();
        $enrollment = CourseEnrollment::query()
            ->where('teacher_course_id', $teacherCourse->id)
            ->where('user_id', $user->id)
            ->first();

        // Read-only use of the existing lesson unlock rules; this module does not change them.
        $lessonOpen = [];
        if ($enrollment?->canStudy()) {
            foreach ($teacherCourse->lessons as $lesson) {
                $lessonOpen[$lesson->id] = $access->canLearn($user, $lesson);
            }
        }

        return view('teacher-courses.show', [
            'course' => $teacherCourse,
            'enrollment' => $enrollment,
            'lessonOpen' => $lessonOpen,
            'isOwner' => $teacherCourse->isOwnedBy($user),
            'canOpenMaterials' => Gate::allows('study', $teacherCourse),
            'canReview' => Gate::allows('review', $teacherCourse),
            'teacherVerified' => $teacherCourse->teacher->isApprovedTeacher(),
        ]);
    }

    public function mine(Request $request): View
    {
        $enrollments = CourseEnrollment::query()
            ->where('user_id', $request->user()->id)
            ->with(['course.language', 'course.teacher:id,name'])
            ->latest('requested_at')
            ->get();

        return view('teacher-courses.mine', compact('enrollments'));
    }
}
