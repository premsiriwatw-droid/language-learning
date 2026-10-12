<?php

namespace App\Policies;

use App\Models\CourseEnrollment;
use App\Models\User;

class CourseEnrollmentPolicy
{
    /** Teacher side: approve / reject / confirm payment / mark completed. */
    public function manage(User $user, CourseEnrollment $enrollment): bool
    {
        $enrollment->loadMissing('course');

        return $enrollment->course->isOwnedBy($user) && $user->isApprovedTeacher();
    }

    /** Learner side: cancel own request / simulate own payment. */
    public function own(User $user, CourseEnrollment $enrollment): bool
    {
        return (int) $enrollment->user_id === (int) $user->id;
    }
}
