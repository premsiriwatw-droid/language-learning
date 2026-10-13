<?php

namespace App\Events\TeacherCourses;

use Illuminate\Contracts\Events\ShouldDispatchAfterCommit;
use Illuminate\Foundation\Events\Dispatchable;

/**
 * Contract for the team's Notification Hub:
 * recommendation.created / assignment.created / assignment.completed / message.sent.
 * The listener belongs to Chotikan's module. Payloads contain IDs, not message text.
 */
class TeacherLearningActivity implements ShouldDispatchAfterCommit
{
    use Dispatchable;

    public function __construct(
        public string $kind,
        public int $teacherCourseId,
        public int $actorId,
        public int $recipientId,
        public int $recordId,
    ) {}
}
