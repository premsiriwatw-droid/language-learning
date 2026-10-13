<?php

namespace App\Models;

use App\Enums\LearnerProficiency;
// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'email', 'password', 'role', 'learner_proficiency', 'placement_required'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $attributes = [
        'role' => 'user',
        'learner_proficiency' => 'beginner',
        'placement_required' => false,
        'current_streak' => 0,
        'longest_streak' => 0,
    ];

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isLearner(): bool
    {
        return in_array($this->role, ['user', 'learner'], true);
    }

    public function teacherApplication(): HasOne
    {
        return $this->hasOne(TeacherApplication::class);
    }

    /** Shared contract for Marketplace and Teacher Dashboard permissions. */
    public function isApprovedTeacher(): bool
    {
        return $this->role === 'teacher'
            && $this->teacherApplication()
                ->where('status', TeacherApplication::APPROVED)
                ->whereNotNull('approved_at')
                ->exists();
    }

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'learner_proficiency' => LearnerProficiency::class,
            'placement_required' => 'boolean',
            'current_streak' => 'integer',
            'longest_streak' => 'integer',
            'last_active_date' => 'date',
        ];
    }
}