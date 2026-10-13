<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TeacherApplication extends Model
{
    public const PENDING = 'pending';
    public const APPROVED = 'approved';
    public const REJECTED = 'rejected';
    public const NEEDS_MORE_INFO = 'needs_more_info';

    // Approval fields deliberately excluded from public form mass assignment.
    protected $fillable = [
        'user_id', 'full_name', 'bio', 'teaching_experience',
        'teaching_languages', 'submitted_at',
    ];

    protected function casts(): array
    {
        return [
            'teaching_languages' => 'array', 'submitted_at' => 'datetime',
            'reviewed_at' => 'datetime', 'approved_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function documents(): HasMany
    {
        return $this->hasMany(TeacherDocument::class);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(TeacherApplicationReview::class)->orderBy('id');
    }
}
