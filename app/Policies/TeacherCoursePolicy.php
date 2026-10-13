<?php

namespace App\Policies;

use App\Models\CourseEnrollment;
use App\Models\TeacherCourse;
use App\Models\User;

class TeacherCoursePolicy
{
    /** Marketplace detail: listed courses, the owner, an admin, or a learner who already has an enrollment. */
    public function view(?User $user, TeacherCourse $course): bool
    {
        if ($course->isOwnedBy($user) || $user?->isAdmin()) {
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

    /** Open teacher lessons, documents and videos: owner, admin (review) or a learner who may study. */
    public function study(User $user, TeacherCourse $course): bool
    {
        if ($course->isOwnedBy($user) || $user->isAdmin()) {
            return true;
        }

        return CourseEnrollment::query()
            ->where('teacher_course_id', $course->id)
            ->where('user_id', $user->id)
            ->learning()
            ->exists();
    }

    /** Approve or reject courses submitted for review. */
    public function review(User $user, TeacherCourse $course): bool
    {
        return $user->isAdmin() && ! $course->isOwnedBy($user);
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
