<?php

namespace App\Http\Controllers\TeacherCourses;

use App\Http\Controllers\Controller;
use App\Models\CourseEnrollment;
use App\Models\TeacherCourse;
use App\Services\TeacherCourses\EnrollmentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

/** Learner side: request to join, cancel a request, simulate payment. */
class LearnerEnrollmentController extends Controller
{
    public function store(Request $request, TeacherCourse $teacherCourse, EnrollmentService $service): RedirectResponse
    {
        Gate::authorize('view', $teacherCourse);

        $enrollment = $service->request($teacherCourse, $request->user());

        $message = match ($enrollment->status) {
            CourseEnrollment::ACTIVE => 'เข้าร่วมคอร์สแล้ว เริ่มเรียนได้เลย',
            CourseEnrollment::APPROVED => 'จองที่นั่งแล้ว ชำระเงิน (จำลอง) เพื่อเริ่มเรียน',
            default => 'ส่งคำขอแล้ว รอผู้สอนอนุมัติ',
        };

        return redirect()->route('teacher-courses.show', $teacherCourse)->with('success', $message);
    }

    public function destroy(Request $request, TeacherCourse $teacherCourse, EnrollmentService $service): RedirectResponse
    {
        $enrollment = $this->own($request, $teacherCourse);
        $service->cancel($enrollment);

        return redirect()->route('teacher-courses.show', $teacherCourse)->with('success', 'ยกเลิกคำขอแล้ว');
    }

    public function pay(Request $request, TeacherCourse $teacherCourse, EnrollmentService $service): RedirectResponse
    {
        $enrollment = $this->own($request, $teacherCourse);
        $service->submitPayment($enrollment);

        return redirect()->route('teacher-courses.show', $teacherCourse)
            ->with('success', 'แจ้งชำระเงินแล้ว (จำลอง ไม่มีการตัดเงินจริง) รอผู้สอนยืนยัน');
    }

    private function own(Request $request, TeacherCourse $course): CourseEnrollment
    {
        $enrollment = CourseEnrollment::query()
            ->where('teacher_course_id', $course->id)
            ->where('user_id', $request->user()->id)
            ->firstOrFail();

        Gate::authorize('own', $enrollment);

        return $enrollment;
    }
}
