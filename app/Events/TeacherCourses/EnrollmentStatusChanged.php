<?php

namespace App\Events\TeacherCourses;

use App\Models\CourseEnrollment;
use Illuminate\Contracts\Events\ShouldDispatchAfterCommit;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Fired every time a Teacher Course enrollment changes status.
 *
 * Contract for other modules (listeners belong to them, not to this module):
 * - Notifications (Chotikan): request sent (from null), approved, rejected, activated, completed.
 * - Rewards (Premsiriwat): $toStatus === CourseEnrollment::COMPLETED means the learner
 *   finished the teacher course. Fired only once per completion. This module never
 *   grants XP or stars itself.
 */
class EnrollmentStatusChanged implements ShouldDispatchAfterCommit
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public CourseEnrollment $enrollment,
        public ?string $fromStatus,
        public string $toStatus,
    ) {
    }
}
