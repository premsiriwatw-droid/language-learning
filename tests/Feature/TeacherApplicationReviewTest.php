<?php

namespace Tests\Feature;

use App\Models\TeacherApplication;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class TeacherApplicationReviewTest extends TestCase
{
    use RefreshDatabase;

    private function application(): TeacherApplication
    {
        Storage::fake('teacher_documents');
        $application = TeacherApplication::create([
            'user_id' => User::factory()->create()->id, 'full_name' => 'Applicant',
            'bio' => 'About me', 'teaching_experience' => 'Two years',
            'teaching_languages' => ['English'], 'submitted_at' => now(),
        ])->fresh();
        Storage::disk('teacher_documents')->put('certificate.pdf', 'test document');
        $application->documents()->create([
            'document_type' => 'certificate', 'file_path' => 'certificate.pdf',
            'original_name' => 'certificate.pdf', 'mime_type' => 'application/pdf',
            'file_size' => 13, 'uploaded_at' => now(),
        ]);
        return $application;
    }

    private function decision(TeacherApplication $application, string $decision, ?string $note = null): array
    {
        return ['review_version' => $application->reviews()->count(), 'decision' => $decision, 'note' => $note, 'submitted_at' => $application->submitted_at->toISOString()];
    }

    public function test_only_admin_can_view_or_review_applications(): void
    {
        $application = $this->application();
        $this->get(route('admin.teacher-applications.index'))->assertRedirect(route('login'));
        $this->actingAs($application->user)->get(route('admin.teacher-applications.index'))->assertForbidden();
        $this->get(route('admin.teacher-applications.show', $application))->assertForbidden();
        $this->post(route('admin.teacher-applications.review', $application), $this->decision($application, 'approved'))->assertForbidden();
        $this->assertSame('pending', $application->fresh()->status);
        $this->assertDatabaseCount('teacher_application_reviews', 0);
    }

    public function test_approval_grants_verified_teacher_access_and_cannot_be_repeated(): void
    {
        $application = $this->application();
        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($application->user)->get(route('teacher.verified'))->assertForbidden();
        $this->actingAs($admin)->get(route('admin.teacher-applications.index'))->assertOk()->assertSee('Applicant');
        $this->get(route('admin.teacher-applications.show', $application))->assertOk()->assertSee('certificate.pdf');
        $url = route('admin.teacher-applications.review', $application);
        $this->post($url, $this->decision($application, 'approved'))->assertSessionHasNoErrors()->assertRedirect();
        $fresh = $application->fresh();
        $this->assertSame('approved', $fresh->status);
        $this->assertSame($admin->id, $fresh->reviewed_by);
        $this->assertNotNull($fresh->approved_at);
        $this->assertTrue($fresh->user->isApprovedTeacher());
        $this->assertDatabaseHas('teacher_application_reviews', ['teacher_application_id' => $application->id, 'reviewer_id' => $admin->id, 'from_status' => 'pending', 'to_status' => 'approved']);
        $this->post($url, $this->decision($application, 'rejected', 'Duplicate'))->assertSessionHasErrors('application');
        $this->assertDatabaseCount('teacher_application_reviews', 1);
        $this->assertSame('approved', $application->fresh()->status);
        $this->actingAs($fresh->user)->get(route('teacher.verified'))->assertOk()->assertSee('ยืนยันตัวตนผู้สอนแล้ว');
        $this->get(route('admin.teacher-applications.index'))->assertForbidden();
    }

    public function test_rejection_and_information_requests_require_reasons_and_keep_learner_role(): void
    {
        foreach (['rejected', 'needs_more_info'] as $decision) {
            $application = $this->application();
            $this->actingAs(User::factory()->create(['role' => 'admin']));
            $url = route('admin.teacher-applications.review', $application);
            $this->post($url, $this->decision($application, $decision))->assertSessionHasErrors('note');
            $this->assertSame('pending', $application->fresh()->status);
            $this->post($url, $this->decision($application, $decision, 'กรุณาเพิ่มหลักฐาน'))->assertSessionHasNoErrors();
            $fresh = $application->fresh();
            $this->assertSame($decision, $fresh->status);
            $this->assertNull($fresh->approved_at);
            $this->assertSame('user', $fresh->user->role);
            $this->actingAs($fresh->user)->get(route('teacher.application.show'))->assertOk()->assertSee('กรุณาเพิ่มหลักฐาน');
            $this->get(route('teacher.verified'))->assertForbidden();
        }
    }

    public function test_stale_submission_and_missing_documents_are_not_approved(): void
    {
        $application = $this->application();
        $this->actingAs(User::factory()->create(['role' => 'admin']));
        $url = route('admin.teacher-applications.review', $application);
        $application->forceFill(['submitted_at' => now()->addMinute()])->save();
        $this->post($url, ['review_version' => 0, 'decision' => 'approved', 'submitted_at' => now()->subDay()->toISOString()])->assertSessionHasErrors('application');
        Storage::disk('teacher_documents')->delete('certificate.pdf');
        $this->post($url, $this->decision($application->fresh(), 'approved'))->assertSessionHasErrors('application');
        $this->assertSame('pending', $application->fresh()->status);
        $this->assertDatabaseCount('teacher_application_reviews', 0);
        $this->assertSame('user', $application->user->role);
    }

    public function test_teacher_role_alone_does_not_grant_access_or_allow_bypassing_approval(): void
    {
        $user = User::factory()->create(['role' => 'teacher']);
        $this->actingAs($user)->get(route('teacher.verified'))->assertForbidden();
        $this->actingAs(User::factory()->create(['role' => 'admin']))
            ->put(route('admin.users.update', $user), ['name' => $user->name, 'email' => $user->email, 'role' => 'teacher'])
            ->assertSessionHasErrors('role');
    }

    public function test_user_management_preserves_approved_teacher_when_editing_name(): void
    {
        $application = $this->application();
        $this->actingAs(User::factory()->create(['role' => 'admin']))
            ->post(route('admin.teacher-applications.review', $application), $this->decision($application, 'approved'))
            ->assertSessionHasNoErrors();
        $user = $application->user->fresh();
        $this->get(route('admin.users.edit', $user))->assertOk()->assertSee('คงบทบาทครู');
        $this->put(route('admin.users.update', $user), ['name' => 'Updated Teacher', 'email' => $user->email, 'role' => 'teacher'])->assertSessionHasNoErrors();
        $this->assertTrue($user->fresh()->isApprovedTeacher());
    }
}
