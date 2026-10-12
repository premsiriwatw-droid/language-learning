<?php

namespace Tests\Feature;

use App\Models\Language;
use App\Models\TeacherApplication;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class TeacherApplicationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('teacher_documents');
        Language::create(['name' => 'English']);
    }

    private function payload(): array
    {
        return [
            'full_name' => 'Applicant Name', 'bio' => 'Teaching profile',
            'teaching_experience' => 'Two years', 'teaching_languages' => ['English'],
            'document_type' => 'certificate',
            'documents' => [UploadedFile::fake()->create('certificate.pdf', 30, 'application/pdf')],
        ];
    }

    public function test_teacher_registration_stays_learner_and_redirects_to_application(): void
    {
        $this->post(route('register.store'), [
            'name' => 'New Teacher', 'email' => 'teacher@example.test',
            'password' => 'password123', 'password_confirmation' => 'password123',
            'account_type' => 'teacher_applicant', 'role' => 'admin',
        ])->assertRedirect(route('teacher.application.show'));
        $user = User::where('email', 'teacher@example.test')->sole();
        $this->assertAuthenticatedAs($user);
        $this->assertSame('user', $user->role);
        $this->assertFalse($user->isApprovedTeacher());
        $this->assertDatabaseCount('teacher_applications', 0);
    }

    public function test_submission_is_pending_and_files_are_private(): void
    {
        $user = User::factory()->create(['role' => 'user']);
        $payload = $this->payload() + ['status' => 'approved', 'role' => 'teacher', 'approved_at' => now()->toDateTimeString()];
        $this->actingAs($user)->post(route('teacher.application.store'), $payload)
            ->assertRedirect(route('teacher.application.show'))->assertSessionHasNoErrors();
        $application = TeacherApplication::sole();
        $document = $application->documents()->sole();
        $this->assertSame('pending', $application->status);
        $this->assertNull($application->approved_at);
        $this->assertSame('user', $user->fresh()->role);
        Storage::disk('teacher_documents')->assertExists($document->file_path);
        $this->assertArrayNotHasKey('file_path', $document->toArray());
        $this->get(route('teacher.application.show'))->assertOk()->assertSee('รอพิจารณา');
        $this->get(route('teacher.documents.download', $document))->assertOk()
            ->assertHeader('Cache-Control', 'no-store, private');
        $this->assertFalse($user->fresh()->isApprovedTeacher());
    }

    public function test_documents_require_owner_or_admin_and_guest_requires_login(): void
    {
        $owner = User::factory()->create(['role' => 'user']);
        $this->actingAs($owner)->post(route('teacher.application.store'), $this->payload())->assertRedirect();
        $document = TeacherApplication::sole()->documents()->sole();
        $this->actingAs(User::factory()->create(['role' => 'user']))
            ->get(route('teacher.documents.download', $document))->assertNotFound();
        $this->get(route('teacher.application.show'))->assertOk()->assertViewHas('application', null);
        $this->actingAs(User::factory()->create(['role' => 'admin']))
            ->get(route('teacher.documents.download', $document))->assertOk();
        $this->post(route('teacher.application.store'), $this->payload())->assertForbidden();
        auth()->logout();
        $this->get(route('teacher.documents.download', $document))->assertRedirect(route('login'));
        $this->get(route('teacher.application.show'))->assertRedirect(route('login'));
    }

    public function test_duplicate_pending_submission_does_not_replace_application_or_files(): void
    {
        $user = User::factory()->create(['role' => 'user']);
        $this->actingAs($user)->post(route('teacher.application.store'), $this->payload())->assertRedirect();
        $this->post(route('teacher.application.store'), $this->payload())
            ->assertRedirect()->assertSessionHasErrors('application');
        $this->assertDatabaseCount('teacher_applications', 1);
        $this->assertDatabaseCount('teacher_documents', 1);
        $this->assertCount(1, Storage::disk('teacher_documents')->allFiles());
    }

    public function test_resubmission_preserves_existing_documents_and_records_status_transition(): void
    {
        $user = User::factory()->create(['role' => 'user']);
        $this->actingAs($user)->post(route('teacher.application.store'), $this->payload())->assertRedirect();
        $application = TeacherApplication::sole();
        $document = $application->documents()->sole();
        $application->forceFill(['status' => 'needs_more_info', 'review_note' => 'Please clarify experience'])->save();
        $payload = $this->payload();
        unset($payload['documents']);
        $payload['bio'] = 'Updated profile';
        $this->post(route('teacher.application.store'), $payload)->assertRedirect()->assertSessionHasNoErrors();
        $this->assertSame('pending', $application->fresh()->status);
        $this->assertSame('Updated profile', $application->fresh()->bio);
        $this->assertDatabaseCount('teacher_documents', 1);
        Storage::disk('teacher_documents')->assertExists($document->file_path);
        $this->assertDatabaseHas('teacher_application_reviews', [
            'teacher_application_id' => $application->id,
            'from_status' => 'needs_more_info', 'to_status' => 'pending',
        ]);
    }

    public function test_invalid_or_missing_document_and_unknown_language_are_rejected(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'user']));
        $payload = $this->payload();
        $payload['documents'] = [UploadedFile::fake()->create('script.php', 10, 'text/plain')];
        $this->post(route('teacher.application.store'), $payload)->assertSessionHasErrors('documents.0');
        $payload = $this->payload();
        unset($payload['documents']);
        $this->post(route('teacher.application.store'), $payload)->assertSessionHasErrors('documents');
        $payload = $this->payload();
        $payload['teaching_languages'] = ['Unknown'];
        $this->post(route('teacher.application.store'), $payload)->assertSessionHasErrors('teaching_languages.0');
        $payload = $this->payload();
        $payload['documents'] = [UploadedFile::fake()->create('large.pdf', 5121, 'application/pdf')];
        $this->post(route('teacher.application.store'), $payload)->assertSessionHasErrors('documents.0');
        $this->assertDatabaseCount('teacher_applications', 0);
        $this->assertCount(0, Storage::disk('teacher_documents')->allFiles());
    }
}
