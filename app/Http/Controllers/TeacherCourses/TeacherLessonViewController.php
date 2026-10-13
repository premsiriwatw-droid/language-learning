<?php

namespace App\Http\Controllers\TeacherCourses;

use App\Http\Controllers\Controller;
use App\Models\TeacherCourse;
use App\Models\TeacherLesson;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

/** Learners (active/completed), the owner and admins open a teacher lesson and its files. */
class TeacherLessonViewController extends Controller
{
    public function show(Request $request, TeacherCourse $teacherCourse, TeacherLesson $teacherLesson): View
    {
        Gate::authorize('study', $teacherCourse);

        $lessons = $teacherCourse->teacherLessons()->get(['id', 'teacher_course_id', 'title', 'position'])->values();
        $index = $lessons->search(fn (TeacherLesson $lesson) => $lesson->id === $teacherLesson->id);

        return view('teacher-courses.lesson', [
            'course' => $teacherCourse,
            'lesson' => $teacherLesson,
            'previous' => $index > 0 ? $lessons[$index - 1] : null,
            'next' => $lessons[$index + 1] ?? null,
            'number' => $index + 1,
            'total' => $lessons->count(),
            'isOwner' => $teacherCourse->isOwnedBy($request->user()),
        ]);
    }

    public function document(TeacherCourse $teacherCourse, TeacherLesson $teacherLesson): StreamedResponse
    {
        Gate::authorize('study', $teacherCourse);

        $disk = Storage::disk(TeacherLesson::DISK);
        abort_unless($teacherLesson->hasDocument() && $disk->exists($teacherLesson->document_path), 404);

        // Always a download, never rendered inline by the browser.
        return $disk->download($teacherLesson->document_path, $teacherLesson->document_name, [
            'X-Content-Type-Options' => 'nosniff',
            'Cache-Control' => 'private, no-store',
        ]);
    }

    public function video(TeacherCourse $teacherCourse, TeacherLesson $teacherLesson): BinaryFileResponse
    {
        Gate::authorize('study', $teacherCourse);

        $disk = Storage::disk(TeacherLesson::DISK);
        abort_unless($teacherLesson->hasVideoFile() && $disk->exists($teacherLesson->video_path), 404);

        // BinaryFileResponse handles Range requests, so the player can seek.
        return response()->file($disk->path($teacherLesson->video_path), [
            'Content-Type' => $teacherLesson->videoContentType(),
            'X-Content-Type-Options' => 'nosniff',
            'Cache-Control' => 'private, max-age=0',
        ]);
    }
}
