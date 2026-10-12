<?php

namespace Tests\Feature\Notifications;

use App\Enums\NotificationTopic;
use App\Events\TeacherCourses\EnrollmentStatusChanged;
use App\Models\CourseEnrollment;
use App\Models\TeacherCourse;
use App\Models\User;
use App\Services\TeacherCourses\EnrollmentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\Support\CreatesTeacherCourses;
use Tests\TestCase;

class EnrollmentNotificationTest extends TestCase
{
    use CreatesTeacherCourses, RefreshDatabase;

    public function test_pending_requests_notify_only_the_owner_with_a_working_management_link(): void
    {
        $teacher = $this->approvedTeacher();
        $otherTeacher = $this->approvedTeacher();
        $learner = $this->learner();
        $course = $this->teacherCourse($teacher, attributes: ['enrollment_mode' => TeacherCourse::APPROVAL_REQUIRED]);
        $service = app(EnrollmentService::class);

        DB::beginTransaction();
        $service->request($course, $learner);
        DB::rollBack();
        $this->assertSame(0, $teacher->notifications()->count());

        $enrollment = $service->request($course, $learner);
        $notification = $teacher->notifications()->sole();
        $this->assertSame(NotificationTopic::EnrollmentStatus->value, $notification->data['topic']);
        $this->assertStringContainsString($course->title, $notification->data['body']);
        $this->assertStringContainsString($learner->name, $notification->data['body']);
        $this->assertSame(route('teacher.courses.enrollments', $course, false), $notification->data['path']);
        $this->actingAs($teacher)->get($notification->data['path'])->assertOk()->assertSee($learner->name);
        $this->assertSame(0, $otherTeacher->notifications()->count());
        $this->assertSame(1, $learner->notifications()->count());

        $service->reject($enrollment, 'Please reapply');
        $enrollment = $service->request($course, $learner);
        $this->assertSame(2, $teacher->notifications()->count());
        $event = new EnrollmentStatusChanged($enrollment, CourseEnrollment::REJECTED, CourseEnrollment::PENDING);
        event($event);
        event($event);
        $this->assertSame(3, $teacher->notifications()->count());
        $service->approve($enrollment);
        $this->assertSame(3, $teacher->notifications()->count());
    }

    public function test_rejection_links_to_own_enrollments_when_course_is_closed_and_includes_reason(): void
    {
        $teacher = $this->approvedTeacher();
        $learner = $this->learner();
        $otherLearner = $this->learner();
        $course = $this->teacherCourse($teacher, attributes: ['enrollment_mode' => TeacherCourse::APPROVAL_REQUIRED]);
        $service = app(EnrollmentService::class);
        $enrollment = $service->request($course, $learner);
        $course->forceFill(['status' => TeacherCourse::CLOSED])->save();
        $reason = 'คอร์สเต็มแล้ว กรุณาสมัครรอบถัดไป';
        $service->reject($enrollment, $reason);
        $notification = $learner->notifications()->where('data->title', 'คำขอเข้าเรียนถูกปฏิเสธ')->sole();
        $this->assertSame(route('teacher-courses.mine', [], false), $notification->data['path']);
        $this->assertStringContainsString($reason, $notification->data['body']);
        $this->actingAs($learner)->get(route('teacher-courses.show', $course))->assertForbidden();
        $this->get($notification->data['path'])->assertOk()->assertSee($course->title);
        $this->get(route('notifications.index'))->assertOk()->assertSee($reason);
        $this->assertSame(0, $otherLearner->notifications()->count());
    }
}
