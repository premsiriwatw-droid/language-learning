<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Shared contract for Teacher Learning Management (Kantaphat):
 * "students in this teacher course" = enrollments with status active or completed
 * (see scopeLearning()).
 */
class CourseEnrollment extends Model
{
    public const PENDING = 'pending';       // waiting for teacher approval
    public const APPROVED = 'approved';     // approved, waiting for payment (paid courses)
    public const REJECTED = 'rejected';
    public const ACTIVE = 'active';         // can study
    public const COMPLETED = 'completed';
    public const STATUSES = [self::PENDING, self::APPROVED, self::REJECTED, self::ACTIVE, self::COMPLETED];

    public const PAYMENT_NOT_REQUIRED = 'not_required';
    public const PAYMENT_UNPAID = 'unpaid';
    public const PAYMENT_SUBMITTED = 'submitted';   // learner pressed "simulate payment"
    public const PAYMENT_CONFIRMED = 'confirmed';   // teacher confirmed manually

    public const STATUS_LABELS = [
        self::PENDING => 'รอผู้สอนอนุมัติ',
        self::APPROVED => 'อนุมัติแล้ว รอชำระเงิน',
        self::REJECTED => 'ไม่ผ่านการอนุมัติ',
        self::ACTIVE => 'กำลังเรียน',
        self::COMPLETED => 'เรียนจบแล้ว',
    ];

    public const PAYMENT_LABELS = [
        self::PAYMENT_NOT_REQUIRED => 'ไม่ต้องชำระ',
        self::PAYMENT_UNPAID => 'ยังไม่ชำระ',
        self::PAYMENT_SUBMITTED => 'แจ้งชำระแล้ว (จำลอง) รอผู้สอนยืนยัน',
        self::PAYMENT_CONFIRMED => 'ยืนยันการชำระแล้ว',
    ];

    // All state fields are written only through App\Services\TeacherCourses\EnrollmentService.
    protected $fillable = [];

    protected function casts(): array
    {
        return [
            'price_at_request' => 'decimal:2',
            'requested_at' => 'datetime',
            'decided_at' => 'datetime',
            'payment_submitted_at' => 'datetime',
            'payment_confirmed_at' => 'datetime',
            'activated_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(TeacherCourse::class, 'teacher_course_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function statusLabel(): string
    {
        return self::STATUS_LABELS[$this->status] ?? $this->status;
    }

    public function paymentLabel(): string
    {
        return self::PAYMENT_LABELS[$this->payment_status] ?? $this->payment_status;
    }

    /** Amount owed for this enrollment, locked at request time (not the course's current price). */
    public function amountLabel(): string
    {
        if ($this->price_at_request === null) {
            return 'ฟรี';
        }

        $amount = (float) $this->price_at_request;

        return '฿'.number_format($amount, $amount == (int) $amount ? 0 : 2);
    }

    public function canStudy(): bool
    {
        return in_array($this->status, [self::ACTIVE, self::COMPLETED], true);
    }

    /** Learners who are actually in the course (for dashboards / analytics). */
    public function scopeLearning(Builder $query): Builder
    {
        return $query->whereIn('status', [self::ACTIVE, self::COMPLETED]);
    }
}
