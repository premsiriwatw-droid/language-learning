<?php

namespace Tests\Feature\Notifications;

use App\Enums\NotificationTopic;
use App\Events\TeacherCourses\EnrollmentStatusChanged;
use App\Models\DailyQuest;
use App\Models\ShopItem;
use App\Models\TeacherApplication;
use App\Models\TeacherCourse;
use App\Models\User;
use App\Models\UserItem;
use App\Services\Progress\DailyQuestService;
use App\Services\Progress\LearningProgress;
use App\Services\Shop\StarWallet;
use App\Services\TeacherCourses\EnrollmentService;
use Database\Seeders\ShopItemSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Tests\Support\CreatesContentLesson;
use Tests\Support\CreatesTeacherCourses;
use Tests\TestCase;

class CrossModuleNotificationTest extends TestCase
{
    use CreatesContentLesson, CreatesTeacherCourses, RefreshDatabase;

    private function countTopic(User $user, NotificationTopic $topic): int
    {
        return $user->notifications()->where('data->topic', $topic->value)->count();
    }

    public function test_learning_to_quest_claim_to_purchase_equip_and_inbox(): void
    {
        $this->seed(ShopItemSeeder::class);
        config()->set('learning.levels.xp_per_level', 100);
        $user = User::factory()->create();
        $lesson = $this->createContentLesson();
        app(LearningProgress::class)->complete($user, $lesson, 110, 20);
        app(LearningProgress::class)->complete($user, $lesson, 110, 20);
        app(DailyQuestService::class)->recordLesson($user, $lesson->id, now());
        app(DailyQuestService::class)->recordLesson($user, $lesson->id, now());
        $this->assertSame(1, $this->countTopic($user, NotificationTopic::LevelUp));
        $this->assertSame(1, $this->countTopic($user, NotificationTopic::QuestComplete));
        $quest = DailyQuest::where('type', 'complete_lesson')->sole();
        $this->actingAs($user)->post(route('daily-quests.claim', $quest->id))->assertSessionHasNoErrors();
        $this->post(route('daily-quests.claim', $quest->id))->assertSessionHasNoErrors();
        $this->assertSame(22, app(StarWallet::class)->summary($user)['balance']);
        $item = ShopItem::where('sku', 'name_color-ruby')->sole();
        $this->post(route('shop.purchase', $item->id))->assertSessionHasNoErrors();
        $this->put(route('inventory.equip', UserItem::sole()->id))->assertSessionHasNoErrors();
        $this->get(route('profile'))->assertOk()->assertSee('cosmetic-name-ruby')->assertSee('พร้อมใช้ 7 ดวง');
        $this->get(route('notifications.index'))->assertOk()->assertSee('เลเวลเพิ่มแล้ว!')->assertSee('ทำภารกิจสำเร็จแล้ว');
        $this->assertDatabaseHas('lesson_progress', ['user_id' => $user->id, 'xp' => 110, 'stars' => 20]);
    }

    public function test_teacher_review_notifies_applicant_only_after_successful_review(): void
    {
        Storage::fake('teacher_documents');
        $applicant = $this->pendingTeacher();
        $application = $applicant->teacherApplication;
        $admin = User::factory()->create(['role' => 'admin']);
        $beforeReview = $this->countTopic($applicant, NotificationTopic::TeacherApplicationStatus);
        $adminBeforeReview = $admin->notifications()->count();
        $payload = ['decision' => 'approved', 'review_version' => 0, 'submitted_at' => $application->submitted_at->toISOString()];
        $this->actingAs($admin)->post(route('admin.teacher-applications.review', $application), $payload)->assertSessionHasErrors('application');
        $this->assertSame($beforeReview, $this->countTopic($applicant, NotificationTopic::TeacherApplicationStatus));
        Storage::disk('teacher_documents')->put('fixture.pdf', 'fixture');
        $application->documents()->create([
            'document_type' => 'certificate', 'file_path' => 'fixture.pdf', 'original_name' => 'fixture.pdf',
            'mime_type' => 'application/pdf', 'file_size' => 7, 'uploaded_at' => now(),
        ]);
        $this->post(route('admin.teacher-applications.review', $application), $payload)->assertSessionHasNoErrors();
        $this->post(route('admin.teacher-applications.review', $application), $payload)->assertSessionHasErrors('application');
        $this->assertTrue($applicant->fresh()->isApprovedTeacher());
        $this->assertSame($beforeReview + 1, $this->countTopic($applicant, NotificationTopic::TeacherApplicationStatus));
        $this->assertSame($adminBeforeReview, $admin->notifications()->count());
        $this->actingAs($applicant->fresh())->get(route('notifications.index'))->assertSee('ใบสมัครผู้สอนได้รับการอนุมัติแล้ว');
    }

    public function test_enrollment_approval_rejection_and_same_second_rerequest_deliver_distinct_events(): void
    {
        $this->freezeTime();
        $teacher = $this->approvedTeacher();
        $user = $this->learner();
        $course = $this->teacherCourse($teacher, attributes: [
            'status' => TeacherCourse::PUBLISHED, 'enrollment_mode' => TeacherCourse::APPROVAL_REQUIRED,
        ]);
        $service = app(EnrollmentService::class);
        $enrollment = $service->request($course, $user);
        $service->reject($enrollment);
        $enrollment = $service->request($course, $user);
        $service->approve($enrollment);
        $this->assertSame(4, $this->countTopic($user, NotificationTopic::EnrollmentStatus));
        $this->assertSame(2, $teacher->notifications()->count());
        $this->actingAs($user)->get(route('notifications.index'))->assertSee('คำขอเข้าเรียนถูกปฏิเสธ')
            ->assertSee('เข้าเรียนคอร์สได้แล้ว');
        $event = new EnrollmentStatusChanged($enrollment->fresh(), 'active', 'completed');
        event($event);
        event($event);
        $this->assertSame(5, $this->countTopic($user, NotificationTopic::EnrollmentStatus));
    }

    public function test_rollback_of_real_progress_and_status_changes_preserves_existing_notifications(): void
    {
        $user = $this->pendingTeacher();
        $lesson = $this->createContentLesson();
        $notificationsBefore = DB::table('notifications')->orderBy('id')->get()->map(fn ($row) => (array) $row)->all();
        DB::beginTransaction();
        app(LearningProgress::class)->complete($user, $lesson, 999, 20);
        app(DailyQuestService::class)->recordLesson($user, $lesson->id, now());
        $user->teacherApplication->forceFill(['status' => TeacherApplication::REJECTED])->save();
        DB::rollBack();
        $this->assertSame($notificationsBefore, DB::table('notifications')->orderBy('id')->get()->map(fn ($row) => (array) $row)->all());
        $this->assertDatabaseCount('lesson_progress', 0);
        $this->assertSame(TeacherApplication::PENDING, $user->teacherApplication->fresh()->status);
    }
}
