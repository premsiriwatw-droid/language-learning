<?php

namespace App\Http\Controllers\TeacherCourses;

use App\Http\Controllers\Controller;
use App\Models\TeacherCourse;
use App\Services\TeacherCourses\TeacherAnalyticsService;
use App\Services\TeacherCourses\TeacherLearningService;
use Illuminate\Http\Request;
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
        Request $request,
        TeacherCourse $teacherCourse,
        string $student,
        TeacherLearningService $service
    ): View {
        $learner = $service->teacherStudent($request->user(), $teacherCourse, $student);

        return view('teacher-courses.manage.student-detail', [
            'course' => $teacherCourse,
            'student' => $learner,
            'panel' => $service->panel($request->user(), $teacherCourse, $learner),
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
