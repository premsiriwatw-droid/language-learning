<?php

namespace Tests\Feature;

use App\Models\TeacherApplication;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TeacherIdentityTest extends TestCase
{
    use RefreshDatabase;

    private function application(User $user, string $status): TeacherApplication
    {
        return TeacherApplication::forceCreate([
            'user_id' => $user->id, 'full_name' => $user->name,
            'bio' => 'Teaching profile', 'teaching_experience' => 'Two years',
            'teaching_languages' => ['English'], 'status' => $status,
            'submitted_at' => now(),
            'approved_at' => $status === TeacherApplication::APPROVED ? now() : null,
        ]);
    }

    public function test_learner_and_admin_are_not_approved_teachers(): void
    {
        foreach (['user', 'admin'] as $role) {
            $user = User::factory()->create(['role' => $role]);
            $this->application($user, TeacherApplication::APPROVED);
            $this->assertFalse($user->isApprovedTeacher());
        }
    }

    public function test_teacher_role_alone_does_not_grant_verified_access(): void
    {
        $user = User::factory()->create(['role' => 'teacher']);
        $this->assertFalse($user->isApprovedTeacher());
        $this->application($user, TeacherApplication::PENDING);
        $this->assertFalse($user->isApprovedTeacher());
    }

    public function test_access_requires_approved_status_timestamp_and_teacher_role(): void
    {
        $user = User::factory()->create(['role' => 'teacher']);
        $application = $this->application($user, TeacherApplication::APPROVED);
        $this->assertTrue($user->isApprovedTeacher());
        $application->forceFill(['approved_at' => null])->save();
        $this->assertFalse($user->isApprovedTeacher());
        $application->forceFill(['approved_at' => now()])->save();
        $user->update(['role' => 'user']);
        $this->assertFalse($user->isApprovedTeacher());
    }

    public function test_status_changes_take_effect_even_if_relationship_was_loaded(): void
    {
        $user = User::factory()->create(['role' => 'teacher']);
        $application = $this->application($user, TeacherApplication::APPROVED);
        $user->load('teacherApplication');
        foreach ([TeacherApplication::REJECTED, TeacherApplication::NEEDS_MORE_INFO, TeacherApplication::PENDING] as $status) {
            $application->forceFill(['status' => $status])->save();
            $this->assertFalse($user->isApprovedTeacher());
        }
    }
}
