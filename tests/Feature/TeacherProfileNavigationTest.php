<?php

namespace Tests\Feature;

use App\Models\TeacherApplication;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TeacherProfileNavigationTest extends TestCase
{
    use RefreshDatabase;

    public function test_learner_can_start_application_from_profile(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'user']))->get(route('profile'))
            ->assertOk()->assertSee('สมัครเป็นผู้สอน')
            ->assertSee(route('teacher.application.show'), false)
            ->assertDontSee('เข้าสู่หน้าผู้สอน');
    }

    public function test_pending_rejected_and_more_info_statuses_have_application_links(): void
    {
        foreach (['pending' => 'รอพิจารณา', 'rejected' => 'ไม่ผ่านการอนุมัติ', 'needs_more_info' => 'ต้องส่งข้อมูลเพิ่มเติม'] as $status => $label) {
            $user = User::factory()->create(['role' => 'user']);
            $application = TeacherApplication::create([
                'user_id' => $user->id, 'full_name' => 'Applicant', 'bio' => 'Bio',
                'teaching_experience' => 'Experience', 'teaching_languages' => ['English'],
                'submitted_at' => now(),
            ]);
            $application->forceFill(['status' => $status, 'review_note' => 'Review note'])->save();
            $this->actingAs($user)->get(route('profile'))->assertOk()->assertSee($label)
                ->assertSee('Review note')->assertSee(route('teacher.application.show'), false)
                ->assertDontSee('เข้าสู่หน้าผู้สอน');
        }
    }

    public function test_only_approved_teacher_has_verified_page_link(): void
    {
        $user = User::factory()->create(['role' => 'teacher']);
        $application = TeacherApplication::create([
            'user_id' => $user->id, 'full_name' => 'Teacher', 'bio' => 'Bio',
            'teaching_experience' => 'Experience', 'teaching_languages' => ['English'],
            'submitted_at' => now(),
        ]);
        $this->actingAs($user)->get(route('profile'))->assertOk()->assertDontSee('เข้าสู่หน้าผู้สอน');
        $application->forceFill(['status' => 'approved', 'approved_at' => now()])->save();
        $this->get(route('profile'))->assertOk()->assertSee('ผู้สอนที่ผ่านการยืนยันแล้ว')
            ->assertSee('เข้าสู่หน้าผู้สอน')->assertSee(route('teacher.verified'), false);
    }

    public function test_admin_can_review_applications_from_profile(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'admin']))->get(route('profile'))
            ->assertOk()->assertSee('ตรวจใบสมัครผู้สอน')
            ->assertSee(route('admin.teacher-applications.index'), false)
            ->assertDontSee('สมัครเป็นผู้สอน');
    }
}
