<?php

namespace App\Models;

use Database\Factories\TeacherCourseFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * A course opened by a verified Teacher. Separate from the System Course
 * (App\Models\Course) that Admin manages.
 */
class TeacherCourse extends Model
{
    /** @use HasFactory<TeacherCourseFactory> */
    use HasFactory;

    public const DRAFT = 'draft';
    public const PUBLISHED = 'published';
    public const CLOSED = 'closed';
    public const STATUSES = [self::DRAFT, self::PUBLISHED, self::CLOSED];

    public const FREE = 'free';
    public const PAID = 'paid';
    public const PRICE_TYPES = [self::FREE, self::PAID];

    public const INSTANT = 'instant';
    public const APPROVAL_REQUIRED = 'approval_required';
    public const ENROLLMENT_MODES = [self::INSTANT, self::APPROVAL_REQUIRED];

    // Same values as the learner proficiency levels (beginner/intermediate/advanced).
    public const LEVELS = ['beginner', 'intermediate', 'advanced'];

    public const LEVEL_LABELS = [
        'beginner' => 'ง่าย',
        'intermediate' => 'กลาง',
        'advanced' => 'ยาก',
    ];

    public const STATUS_LABELS = [
        self::DRAFT => 'ฉบับร่าง',
        self::PUBLISHED => 'เปิดรับสมัคร',
        self::CLOSED => 'ปิดคอร์ส',
    ];

    // teacher_id, status and published_at are set by the controller, never by mass assignment.
    protected $fillable = [
        'language_id', 'title', 'description', 'level',
        'price_type', 'price', 'enrollment_mode',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'published_at' => 'datetime',
        ];
    }

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    public function language(): BelongsTo
    {
        return $this->belongsTo(Language::class);
    }

    public function lessons(): BelongsToMany
    {
        return $this->belongsToMany(Lesson::class, 'teacher_course_lessons')
            ->withPivot('position')
            ->withTimestamps()
            ->orderByPivot('position')
            ->orderBy('lessons.id');
    }

    public function enrollments(): HasMany
    {
        return $this->hasMany(CourseEnrollment::class);
    }

    public function isFree(): bool
    {
        return $this->price_type === self::FREE;
    }

    public function isPublished(): bool
    {
        return $this->status === self::PUBLISHED;
    }

    public function isOwnedBy(?User $user): bool
    {
        return $user !== null && (int) $this->teacher_id === (int) $user->id;
    }

    public function levelLabel(): string
    {
        return self::LEVEL_LABELS[$this->level] ?? $this->level;
    }

    public function priceLabel(): string
    {
        return $this->isFree()
            ? 'ฟรี'
            : '฿'.number_format((float) $this->price, $this->price == (int) $this->price ? 0 : 2);
    }

    /**
     * Query form of the shared contract User::isApprovedTeacher():
     * role = teacher AND teacher_application.status = approved AND approved_at set.
     */
    public function scopeFromApprovedTeachers(Builder $query): Builder
    {
        return $query->whereHas('teacher', fn (Builder $teacher) => $teacher
            ->where('role', 'teacher')
            ->whereHas('teacherApplication', fn (Builder $application) => $application
                ->where('status', TeacherApplication::APPROVED)
                ->whereNotNull('approved_at')));
    }

    /** What learners can browse in the marketplace. */
    public function scopeListed(Builder $query): Builder
    {
        return $query->where('status', self::PUBLISHED)->fromApprovedTeachers();
    }

    public function scopeFilter(Builder $query, array $filters): Builder
    {
        return $query
            ->when($filters['language'] ?? null, fn ($q, $id) => $q->where('language_id', $id))
            ->when($filters['level'] ?? null, fn ($q, $level) => $q->where('level', $level))
            ->when($filters['price'] ?? null, fn ($q, $type) => $q->where('price_type', $type));
    }
}
