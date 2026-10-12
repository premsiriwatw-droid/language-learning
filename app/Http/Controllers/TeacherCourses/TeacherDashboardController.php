<?php

namespace App\Http\Controllers\TeacherCourses;

use App\Http\Controllers\Controller;
use App\Models\TeacherCourse;
use App\Services\TeacherCourses\TeacherAnalyticsService;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class TeacherDashboardController extends Controller
{
    public function show(TeacherCourse $teacherCourse, TeacherAnalyticsService $analytics): View
    {
        Gate::authorize('update', $teacherCourse);

        $teacherCourse->loadMissing('language');

        return view('teacher-courses.manage.dashboard', [
            'course' => $teacherCourse,
            'summary' => $analytics->summary($teacherCourse),
        ]);
    }

    public function students(TeacherCourse $teacherCourse, TeacherAnalyticsService $analytics): View
    {
        Gate::authorize('update', $teacherCourse);

        $teacherCourse->loadMissing('language');
        $list = $analytics->studentList($teacherCourse);

        return view('teacher-courses.manage.students', [
            'course' => $teacherCourse,
            'students' => $list['students'],
            'lessonsCount' => $list['lessons_count'],
            'asOf' => $list['as_of'],
        ]);
    }
    public function studentDetail(
        TeacherCourse $teacherCourse,
        string $student
    ): View {
        // ชั้นแรก: ผู้ที่เปิดหน้าต้องเป็นครูเจ้าของคอร์ส
        Gate::authorize('update', $teacherCourse);

        // ชั้นที่สอง: หา enrollment ของนักเรียนภายในคอร์สนี้เท่านั้น
        $enrollment = $teacherCourse->enrollments()
            ->learning()
            ->where('user_id', $student)
            ->with('user:id,name')
            ->firstOrFail();

        $learner = $enrollment->user;

        abort_if($learner === null, 404);

        return view('teacher-courses.manage.student-detail', [
            'course' => $teacherCourse,
            'student' => $learner,
        ]);
    }
    public function weakTopics(
        TeacherCourse $teacherCourse,
        TeacherAnalyticsService $analytics
    ): View {
        Gate::authorize('update', $teacherCourse);

        return view('teacher-courses.manage.weak-topics', [
            'course' => $teacherCourse,
            'report' => $analytics->weakTopics($teacherCourse),
        ]);
    }
}
