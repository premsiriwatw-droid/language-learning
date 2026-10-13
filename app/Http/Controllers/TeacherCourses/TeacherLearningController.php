<?php

namespace App\Http\Controllers\TeacherCourses;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCourseAssignmentRequest;
use App\Http\Requests\StoreCourseMessageRequest;
use App\Http\Requests\StoreTeacherRecommendationRequest;
use App\Models\TeacherCourse;
use App\Services\TeacherCourses\TeacherLearningService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TeacherLearningController extends Controller
{
    public function recommend(
        StoreTeacherRecommendationRequest $request,
        TeacherCourse $teacherCourse,
        string $student,
        TeacherLearningService $service
    ): RedirectResponse {
        $data = $request->validated();

        $service->recommend(
            $request->user(), $teacherCourse, $student,
            (int) $data['lesson_id'], $data['reason']
        );

        return $this->teacherBack($teacherCourse, $student, 'ส่งคำแนะนำแล้ว');
    }

    public function assign(
        StoreCourseAssignmentRequest $request,
        TeacherCourse $teacherCourse,
        string $student,
        TeacherLearningService $service
    ): RedirectResponse {
        $data = $request->validated();

        $service->assign($request->user(), $teacherCourse, $student, $data);

        return $this->teacherBack($teacherCourse, $student, 'มอบหมายงานแล้ว');
    }

    public function teacherMessage(
        StoreCourseMessageRequest $request,
        TeacherCourse $teacherCourse,
        string $student,
        TeacherLearningService $service
    ): RedirectResponse {
        $learner = $service->teacherStudent($request->user(), $teacherCourse, $student);
        $data = $request->validated();

        $service->message($request->user(), $teacherCourse, $learner, $data['body']);

        return $this->teacherBack($teacherCourse, $student, 'ส่งข้อความแล้ว');
    }

    public function learnerPanel(
        Request $request,
        TeacherCourse $teacherCourse,
        TeacherLearningService $service
    ): View {
        $student = $request->user();

        return view('teacher-courses.learning-tools', [
            'course' => $teacherCourse,
            'student' => $student,
            'panel' => $service->panel($student, $teacherCourse, $student),
        ]);
    }

    public function learnerMessage(
        StoreCourseMessageRequest $request,
        TeacherCourse $teacherCourse,
        TeacherLearningService $service
    ): RedirectResponse {
        $data = $request->validated();

        $service->message($request->user(), $teacherCourse, $request->user(), $data['body']);

        return $this->learnerBack($teacherCourse, 'ส่งข้อความแล้ว');
    }

    public function start(
        Request $request,
        TeacherCourse $teacherCourse,
        string $assignment,
        TeacherLearningService $service
    ): RedirectResponse {
        $service->start($request->user(), $teacherCourse, $assignment);

        return $this->learnerBack($teacherCourse, 'เริ่มทำงานแล้ว');
    }

    public function complete(
        Request $request,
        TeacherCourse $teacherCourse,
        string $assignment,
        TeacherLearningService $service
    ): RedirectResponse {
        $service->complete($request->user(), $teacherCourse, $assignment);

        return $this->learnerBack($teacherCourse, 'ตรวจสอบการเรียนและบันทึกว่าเสร็จแล้ว');
    }

    private function teacherBack(TeacherCourse $course, string $student, string $message): RedirectResponse
    {
        return redirect()->route('teacher.courses.students.show', [
            'teacherCourse' => $course->id, 'student' => $student,
        ])->with('success', $message);
    }

    private function learnerBack(TeacherCourse $course, string $message): RedirectResponse
    {
        return redirect()->route('teacher-courses.learning-tools', $course)->with('success', $message);
    }
}
