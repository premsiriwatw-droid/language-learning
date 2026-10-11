<?php

namespace Tests\Feature;

use App\Models\Language;
use App\Models\TeacherApplication;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class TeacherVerificationRecoveryTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('teacher_documents');
        Language::create(['name' => 'English']);
    }

    private function application(int $count = 1): TeacherApplication
    {
        $application = TeacherApplication::create([
            'user_id' => User::factory()->create(['role' => 'user'])->id,
            'full_name' => 'Applicant', 'bio' => 'Biography',
            'teaching_experience' => 'Experience', 'teaching_languages' => ['English'],
            'submitted_at' => now(),
        ]);
        $application->forceFill(['status' => 'needs_more_info'])->save();
        for ($i = 0; $i < $count; $i++) {
            $path = $application->id.'/old-'.$i.'.pdf';
            Storage::disk('teacher_documents')->put($path, 'old document');
            $application->documents()->create([
                'document_type' => 'certificate', 'file_path' => $path,
                'original_name' => 'old-'.$i.'.pdf', 'mime_type' => 'application/pdf',
                'file_size' => 12, 'uploaded_at' => now(),
            ]);
        }
        return $application->fresh();
    }

    private function payload(): array
    {
        return [
            'full_name' => 'Applicant', 'bio' => 'Updated biography',
            'teaching_experience' => 'Experience', 'teaching_languages' => ['English'],
            'document_type' => 'certificate',
            'documents' => [UploadedFile::fake()->create('replacement.pdf', 20, 'application/pdf')],
        ];
    }

    public function test_admin_can_restore_teacher_role_after_demotion(): void
    {
        $application = $this->application();
        $application->forceFill(['status' => 'approved', 'approved_at' => now()])->save();
        $user = $application->user;
        $user->update(['role' => 'teacher']);
        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin)->put(route('admin.users.update', $user), [
            'name' => $user->name, 'email' => $user->email, 'role' => 'user',
        ])->assertSessionHasNoErrors();
        $this->assertFalse($user->fresh()->isApprovedTeacher());
        $this->get(route('admin.users.edit', $user))->assertOk()->assertSee('คืนสิทธิ์ครู');
        $this->put(route('admin.users.update', $user), [
            'name' => $user->name, 'email' => $user->email, 'role' => 'teacher',
        ])->assertSessionHasNoErrors();
        $this->assertTrue($user->fresh()->isApprovedTeacher());
        $this->actingAs($user->fresh())->get(route('teacher.verified'))->assertOk();
    }

    public function test_restoration_requires_approved_application_and_timestamp(): void
    {
        $application = $this->application();
        $user = $application->user;
        $this->actingAs(User::factory()->create(['role' => 'admin']));
        foreach (['pending', 'rejected', 'needs_more_info', 'approved'] as $status) {
            $application->forceFill(['status' => $status, 'approved_at' => null])->save();
            $this->put(route('admin.users.update', $user), [
                'name' => $user->name, 'email' => $user->email, 'role' => 'teacher',
            ])->assertSessionHasErrors('role');
            $this->assertSame('user', $user->fresh()->role);
        }
    }

    public function test_missing_document_can_be_replaced_at_ten_file_limit_and_approved(): void
    {
        $application = $this->application(10);
        $old = $application->documents()->first();
        Storage::disk('teacher_documents')->delete($old->file_path);
        $payload = $this->payload();
        $payload['remove_document_ids'] = [$old->id];
        $this->actingAs($application->user)->post(route('teacher.application.store'), $payload)->assertSessionHasNoErrors();
        $fresh = $application->fresh();
        $this->assertSame('pending', $fresh->status);
        $this->assertSame(10, $fresh->documents()->count());
        $this->assertDatabaseMissing('teacher_documents', ['id' => $old->id]);
        $this->assertCount(10, Storage::disk('teacher_documents')->allFiles());
        $this->actingAs(User::factory()->create(['role' => 'admin']))
            ->post(route('admin.teacher-applications.review', $fresh), [
                'decision' => 'approved', 'submitted_at' => $fresh->submitted_at->toISOString(),
                'review_version' => $fresh->reviews()->count(),
            ])->assertSessionHasNoErrors();
        $this->assertTrue($fresh->user->fresh()->isApprovedTeacher());
    }

    public function test_replacing_existing_file_removes_it_only_after_success(): void
    {
        $application = $this->application();
        $old = $application->documents()->first();
        $payload = $this->payload();
        $payload['remove_document_ids'] = [$old->id];
        $this->actingAs($application->user)->post(route('teacher.application.store'), $payload)->assertSessionHasNoErrors();
        Storage::disk('teacher_documents')->assertMissing($old->file_path);
        $replacement = $application->documents()->sole();
        Storage::disk('teacher_documents')->assertExists($replacement->file_path);
        $this->assertSame('replacement.pdf', $replacement->original_name);
    }

    public function test_foreign_document_removal_is_rejected_without_changes(): void
    {
        $own = $this->application();
        $foreign = $this->application();
        $payload = $this->payload();
        $payload['remove_document_ids'] = [$foreign->documents()->first()->id];
        $this->actingAs($own->user)->post(route('teacher.application.store'), $payload)->assertSessionHasErrors('remove_document_ids');
        $this->assertSame('needs_more_info', $own->fresh()->status);
        $this->assertDatabaseCount('teacher_documents', 2);
        $this->assertCount(2, Storage::disk('teacher_documents')->allFiles());
    }

    public function test_cannot_remove_last_document_without_replacement(): void
    {
        $application = $this->application();
        $old = $application->documents()->first();
        $payload = $this->payload();
        unset($payload['documents']);
        $payload['remove_document_ids'] = [$old->id];
        $this->actingAs($application->user)->post(route('teacher.application.store'), $payload)->assertSessionHasErrors('documents');
        $this->assertDatabaseHas('teacher_documents', ['id' => $old->id]);
        Storage::disk('teacher_documents')->assertExists($old->file_path);
        $this->assertSame('needs_more_info', $application->fresh()->status);
    }

    public function test_missing_retained_document_and_excess_count_do_not_modify_application(): void
    {
        $application = $this->application(10);
        $this->actingAs($application->user)->post(route('teacher.application.store'), $this->payload())->assertSessionHasErrors('documents');
        $this->assertSame(10, $application->documents()->count());
        Storage::disk('teacher_documents')->delete($application->documents()->first()->file_path);
        $payload = $this->payload();
        unset($payload['documents']);
        $this->post(route('teacher.application.store'), $payload)->assertSessionHasErrors('documents');
        $this->assertSame('needs_more_info', $application->fresh()->status);
        $this->assertDatabaseCount('teacher_application_reviews', 0);
    }

    public function test_pending_application_cannot_remove_documents(): void
    {
        $application = $this->application();
        $application->forceFill(['status' => 'pending'])->save();
        $old = $application->documents()->first();
        $payload = $this->payload();
        $payload['remove_document_ids'] = [$old->id];
        $this->actingAs($application->user)->post(route('teacher.application.store'), $payload)->assertSessionHasErrors('application');
        $this->assertDatabaseHas('teacher_documents', ['id' => $old->id]);
        Storage::disk('teacher_documents')->assertExists($old->file_path);
    }
}
