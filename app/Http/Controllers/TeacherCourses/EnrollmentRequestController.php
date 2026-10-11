<?php

namespace App\Http\Controllers\TeacherCourses;

use App\Http\Controllers\Controller;
use App\Models\CourseEnrollment;
use App\Models\TeacherCourse;
use App\Services\TeacherCourses\EnrollmentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/** Approved teacher: review learners who asked to join one of their courses. */
class EnrollmentRequestController extends Controller
{
    private const ACTIONS = ['approve', 'reject', 'confirm_payment', 'complete'];

    public function index(TeacherCourse $teacherCourse): View
    {
        Gate::authorize('update', $teacherCourse);

        $enrollments = $teacherCourse->enrollments()
            ->with('user:id,name,email')
            ->orderByRaw("case status when 'pending' then 0 when 'approved' then 1 when 'active' then 2 when 'completed' then 3 else 4 end")
            ->orderBy('requested_at')
            ->get()
            ->groupBy('status');

        return view('teacher-courses.manage.enrollments', [
            'course' => $teacherCourse,
            'enrollments' => $enrollments,
        ]);
    }

    public function update(Request $request, CourseEnrollment $enrollment, EnrollmentService $service): RedirectResponse
    {
        Gate::authorize('manage', $enrollment);

        $data = $request->validate([
            'action' => ['required', Rule::in(self::ACTIONS)],
            'note' => ['nullable', 'string', 'max:500'],
        ], [
            'action.in' => 'คำสั่งไม่ถูกต้อง',
            'note.max' => 'ข้อความถึงผู้เรียนยาวเกินไป (ไม่เกิน 500 ตัวอักษร)',
        ]);

        $updated = match ($data['action']) {
            'approve' => $service->approve($enrollment, $data['note'] ?? null),
            'reject' => $service->reject($enrollment, $data['note'] ?? null),
            'confirm_payment' => $service->confirmPayment($enrollment),
            'complete' => $service->complete($enrollment),
        };

        $updated->loadMissing('user:id,name');

        return back()->with('success', $updated->user->name.': '.$updated->statusLabel());
    }
}
