<?php

namespace Tests\Feature\Notifications;

use App\Models\TeacherApplication;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class TeacherSubmissionNotificationTest extends TestCase
{
    use RefreshDatabase;

    private function submit(User $user): TeacherApplication
    {
        $application = new TeacherApplication;
        $application->fill([
            'user_id' => $user->id, 'full_name' => 'Applicant', 'bio' => 'Profile',
            'teaching_experience' => 'Experience', 'teaching_languages' => ['English'],
            'submitted_at' => now(),
        ]);
        $application->forceFill(['status' => TeacherApplication::PENDING])->save();

        return $application;
    }

    public function test_submission_notifies_applicant_and_all_admins_only(): void
    {
        $applicant = User::factory()->create(['role' => 'user']);
        $admins = User::factory()->count(2)->create(['role' => 'admin']);
        $other = User::factory()->create(['role' => 'user']);
        $teacher = User::factory()->create(['role' => 'teacher']);
        $application = $this->submit($applicant);

        $this->assertSame(1, $applicant->notifications()->count());
        foreach ($admins as $admin) {
            $this->assertSame(1, $admin->notifications()->count());
            $this->assertSame('/admin/teacher-applications/'.$application->id, $admin->notifications()->sole()->data['path']);
        }
        $this->assertSame(0, $other->notifications()->count());
        $this->assertSame(0, $teacher->notifications()->count());
    }

    public function test_same_second_resubmission_is_distinct_but_profile_edits_do_not_notify(): void
    {
        $this->freezeTime();
        $applicant = User::factory()->create(['role' => 'user']);
        $admin = User::factory()->create(['role' => 'admin']);
        $application = $this->submit($applicant);
        $application->update(['bio' => 'Updated profile']);
        $this->assertSame(1, $admin->notifications()->count());

        $application->forceFill(['status' => TeacherApplication::NEEDS_MORE_INFO])->save();
        $application->forceFill(['status' => TeacherApplication::PENDING])->save();
        $this->assertSame(2, $admin->notifications()->count());
        $this->assertSame(3, $applicant->notifications()->count());
    }

    public function test_rolled_back_submission_does_not_notify_anyone(): void
    {
        $applicant = User::factory()->create(['role' => 'user']);
        User::factory()->create(['role' => 'admin']);
        DB::beginTransaction();
        $this->submit($applicant);
        DB::rollBack();

        $this->assertDatabaseCount('teacher_applications', 0);
        $this->assertDatabaseCount('notifications', 0);
    }
}
