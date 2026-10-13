<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AssignmentStatus extends Model
{
    public const PENDING = 'pending';

    public const IN_PROGRESS = 'in_progress';

    public const COMPLETED = 'completed';

    public const LABELS = [
        self::PENDING => 'ยังไม่เริ่ม',
        self::IN_PROGRESS => 'กำลังทำ',
        self::COMPLETED => 'เสร็จแล้ว',
    ];

    protected $fillable = [
        'course_assignment_id', 'student_id', 'status',
        'attempt_baseline', 'completed_at',
    ];

    protected function casts(): array
    {
        return ['attempt_baseline' => 'integer', 'completed_at' => 'datetime'];
    }

    public function assignment(): BelongsTo
    {
        return $this->belongsTo(CourseAssignment::class, 'course_assignment_id');
    }
}
