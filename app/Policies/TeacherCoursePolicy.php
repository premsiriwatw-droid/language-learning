<?php

namespace App\Policies;

use App\Models\CourseEnrollment;
use App\Models\TeacherCourse;
use App\Models\User;

class TeacherCoursePolicy
{
    /** Marketplace detail: listed courses, the owner, or a learner who already has an enrollment. */
    public function view(?User $user, TeacherCourse $course): bool
    {
        if ($course->isOwnedBy($user)) {
            return true;
        }

        if ($course->isPublished() && TeacherCourse::query()->whereKey($course->id)->fromApprovedTeachers()->exists()) {
            return true;
        }

        return $user !== null && CourseEnrollment::query()
            ->where('teacher_course_id', $course->id)
            ->where('user_id', $user->id)
            ->where('status', '!=', CourseEnrollment::REJECTED)
            ->exists();
    }

    public function create(User $user): bool
    {
        return $user->isApprovedTeacher();
    }

    /** Edit / publish / close / manage enrollments: only the approved teacher who owns it. */
    public function update(User $user, TeacherCourse $course): bool
    {
        return $course->isOwnedBy($user) && $user->isApprovedTeacher();
    }
}
