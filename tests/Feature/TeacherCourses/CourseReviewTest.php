<?php

namespace Tests\Feature\TeacherCourses;

use App\Models\TeacherCourse;
use App\Models\TeacherLesson;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\CreatesTeacherCourses;
use Tests\TestCase;

class CourseReviewTest extends TestCase
{
    use CreatesTeacherCourses;
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create(['role' => 'admin']);
    }

    /** Draft with only a teacher-written lesson, no system lessons. */
    private function draftWithOwnLesson(User $teacher, string $title = 'Business English'): TeacherCourse
    {
        $course = $this->teacherCourse($teacher, null, ['title' => $title], fn ($f) => $f->draft());
        $course->lessons()->detach();
        (new TeacherLesson())->forceFill([
            'teacher_course_id' => $course->id, 'title' => 'Emails', 'body' => 'Dear team,', 'position' => 1,
        ])->save();

        return $course;
    }

    public function test_full_review_cycle_with_notifications(): void
    {
        $teacher = $this->approvedTeacher();
        $admin = $this->admin();
        $otherAdmin = $this->admin();
        $learner = $this->learner();
        $course = $this->draftWithOwnLesson($teacher);

        $this->actingAs($teacher)->patch(route('teacher.courses.status', $course), ['action' => 'submit'])
            ->assertSessionHasNoErrors();
        $course->refresh();
        $this->assertSame(TeacherCourse::PENDING_REVIEW, $course->status);
        $this->assertNotNull($course->submitted_at);

        // Every admin hears about it, with a working link.
        foreach ([$admin, $otherAdmin] as $user) {
            $notification = $user->notifications()->sole();
            $this->assertSame(route('admin.teacher-courses.show', $course, false), $notification->data['path']);
            $this->assertStringContainsString('Business English', $notification->data['body']);
        }
        $this->assertSame(0, $learner->notifications()->count());

        $this->actingAs($learner)->get(route('teacher-courses.index'))->assertDontSee('Business English');
        $this->actingAs($learner)->get(route('teacher-courses.show', $course))->assertForbidden();

        $this->actingAs($admin)->get(route('admin.teacher-courses.index'))
            ->assertOk()->assertSee('Business English')->assertSee('รอตรวจ (1)');
        $this->actingAs($admin)->get(route('admin.teacher-courses.show', $course))
            ->assertOk()->assertSee('Emails')->assertSee('Dear team,')->assertSee('อนุมัติและเผยแพร่');

        // Rejection needs a reason.
        $this->actingAs($admin)->post(route('admin.teacher-courses.review', $course), ['revision' => $course->fresh()->content_revision, 'decision' => 'reject', 'review_note' => '  '])
            ->assertSessionHasErrors('review_note');
        $this->assertSame(TeacherCourse::PENDING_REVIEW, $course->fresh()->status);

        $this->actingAs($admin)->post(route('admin.teacher-courses.review', $course), [
            'revision' => $course->fresh()->content_revision, 'decision' => 'reject', 'review_note' => 'Add a video for lesson 1',
        ])->assertSessionHasNoErrors()->assertRedirect(route('admin.teacher-courses.index'));

        $course->refresh();
        $this->assertSame(TeacherCourse::DRAFT, $course->status);
        $this->assertSame('Add a video for lesson 1', $course->review_note);
        $this->assertSame($admin->id, $course->reviewed_by);
        $rejected = $teacher->notifications()->sole();
        $this->assertStringContainsString('Add a video for lesson 1', $rejected->data['body']);
        $this->assertSame(route('teacher.courses.edit', $course, false), $rejected->data['path']);
        $this->actingAs($teacher)->get($rejected->data['path'])
            ->assertOk()->assertSee('แอดมินยังไม่อนุมัติ')->assertSee('Add a video for lesson 1')->assertSee('ส่งให้แอดมินตรวจ');

        // Resubmit and approve.
        $this->actingAs($teacher)->patch(route('teacher.courses.status', $course), ['action' => 'submit'])->assertSessionHasNoErrors();
        $this->actingAs($admin)->post(route('admin.teacher-courses.review', $course), ['revision' => $course->fresh()->content_revision, 'decision' => 'approve', 'review_note' => 'Nice work'])
            ->assertSessionHasNoErrors();

        $course->refresh();
        $this->assertSame(TeacherCourse::PUBLISHED, $course->status);
        $this->assertNotNull($course->approved_at);
        $this->assertNotNull($course->published_at);
        $this->assertSame(2, $teacher->notifications()->count());

        $this->actingAs($learner)->get(route('teacher-courses.index'))->assertSee('Business English');
        $this->actingAs($learner)->post(route('teacher-courses.enroll', $course))->assertSessionHasNoErrors();
    }

    public function test_review_pages_are_admin_only(): void
    {
        $teacher = $this->approvedTeacher();
        $course = $this->draftWithOwnLesson($teacher);
        $this->actingAs($teacher)->patch(route('teacher.courses.status', $course), ['action' => 'submit']);

        $this->app['auth']->forgetGuards();
        $this->post(route('admin.teacher-courses.review', $course), ['revision' => $course->fresh()->content_revision, 'decision' => 'approve'])->assertRedirect(route('login'));

        foreach ([$teacher, $this->learner(), $this->approvedTeacher()] as $user) {
            $this->actingAs($user)->get(route('admin.teacher-courses.index'))->assertForbidden();
            $this->actingAs($user)->get(route('admin.teacher-courses.show', $course))->assertForbidden();
            $this->actingAs($user)->post(route('admin.teacher-courses.review', $course), ['revision' => $course->fresh()->content_revision, 'decision' => 'approve'])->assertForbidden();
        }

        $this->assertSame(TeacherCourse::PENDING_REVIEW, $course->fresh()->status);
    }

    public function test_teacher_cannot_self_publish_or_skip_review(): void
    {
        $teacher = $this->approvedTeacher();
        $course = $this->draftWithOwnLesson($teacher);

        $this->actingAs($teacher)->patch(route('teacher.courses.status', $course), ['action' => 'reopen'])->assertSessionHasErrors('status');
        $this->actingAs($teacher)->patch(route('teacher.courses.status', $course), ['action' => 'publish'])->assertSessionHasErrors('action');
        $this->actingAs($teacher)->put(route('teacher.courses.update', $course), [
            'title' => 'x', 'description' => 'y', 'language_id' => $course->language_id, 'level' => 'beginner',
            'price_type' => 'free', 'enrollment_mode' => 'instant', 'status' => 'published', 'approved_at' => now()->toDateTimeString(),
        ])->assertSessionHasNoErrors();

        $course->refresh();
        $this->assertSame(TeacherCourse::DRAFT, $course->status);
        $this->assertNull($course->approved_at);
    }

    public function test_withdraw_and_stale_decisions(): void
    {
        $teacher = $this->approvedTeacher();
        $admin = $this->admin();
        $course = $this->draftWithOwnLesson($teacher);
        $this->actingAs($teacher)->patch(route('teacher.courses.status', $course), ['action' => 'submit']);

        $this->actingAs($teacher)->patch(route('teacher.courses.status', $course), ['action' => 'withdraw'])->assertSessionHasNoErrors();
        $this->assertSame(TeacherCourse::DRAFT, $course->fresh()->status);

        // The admin still had the old page open.
        $this->actingAs($admin)->post(route('admin.teacher-courses.review', $course), ['revision' => $course->fresh()->content_revision, 'decision' => 'approve'])
            ->assertSessionHasErrors('review');
        $this->assertSame(TeacherCourse::DRAFT, $course->fresh()->status);
        $this->assertSame(0, $teacher->notifications()->count());
    }

    public function test_closed_course_edited_after_approval_must_be_reviewed_again(): void
    {
        $teacher = $this->approvedTeacher();
        $admin = $this->admin();
        $course = $this->teacherCourse($teacher, null, ['title' => 'Closed one']);
        $learner = $this->learner();
        $this->actingAs($learner)->post(route('teacher-courses.enroll', $course));
        $this->actingAs($teacher)->patch(route('teacher.courses.status', $course), ['action' => 'close']);

        $this->actingAs($teacher)->put(route('teacher.courses.update', $course), [
            'title' => 'Closed one (v2)', 'description' => $course->description, 'language_id' => $course->language_id,
            'level' => $course->level, 'price_type' => 'free', 'enrollment_mode' => 'instant',
            'lessons' => $course->lessons->pluck('id')->all(),
        ])->assertSessionHasNoErrors();
        $this->assertSame(TeacherCourse::CLOSED, $course->fresh()->status);
        $this->assertNull($course->fresh()->approved_at);

        $this->actingAs($teacher)->patch(route('teacher.courses.status', $course), ['action' => 'reopen'])->assertSessionHasErrors('status');
        $this->actingAs($teacher)->patch(route('teacher.courses.status', $course), ['action' => 'submit'])->assertSessionHasNoErrors();

        // Rejecting a course that already has learners keeps it closed, never a draft.
        $this->actingAs($admin)->post(route('admin.teacher-courses.review', $course), ['revision' => $course->fresh()->content_revision, 'decision' => 'reject', 'review_note' => 'Fix title']);
        $this->assertSame(TeacherCourse::CLOSED, $course->fresh()->status);
        $this->actingAs($learner)->get(route('teacher-courses.show', $course))->assertOk()->assertSee('Closed one (v2)');
    }

    public function test_saving_without_changes_does_not_trigger_review(): void
    {
        $teacher = $this->approvedTeacher();
        $course = $this->teacherCourse($teacher);

        $this->actingAs($teacher)->put(route('teacher.courses.update', $course), [
            'title' => $course->title, 'description' => $course->description, 'language_id' => $course->language_id,
            'level' => $course->level, 'price_type' => 'free', 'price' => '', 'enrollment_mode' => 'instant',
            'lessons' => $course->lessons->pluck('id')->all(),
        ])->assertSessionHasNoErrors();

        $this->assertSame(TeacherCourse::PUBLISHED, $course->fresh()->status);
    }

    /** Revision exactly as the admin's review form carries it. */
    private function revisionOnReviewPage(User $admin, TeacherCourse $course): int
    {
        $html = $this->actingAs($admin)->get(route('admin.teacher-courses.show', $course))->assertOk()->getContent();
        $this->assertSame(1, preg_match('/name="revision" value="(\d+)"/', $html, $match));

        return (int) $match[1];
    }

    public function test_admin_cannot_approve_content_changed_after_opening_the_page(): void
    {
        $teacher = $this->approvedTeacher();
        $admin = $this->admin();
        $course = $this->draftWithOwnLesson($teacher, 'Swapped course');
        $this->actingAs($teacher)->patch(route('teacher.courses.status', $course), ['action' => 'submit']);

        $seen = $this->revisionOnReviewPage($admin, $course);

        // The teacher changes a lesson while the admin is still reading the old version.
        $lesson = $course->teacherLessons()->first();
        $this->actingAs($teacher)->put(route('teacher.courses.teacher-lessons.update', [$course, $lesson]), [
            'title' => 'Emails', 'body' => 'Content the admin never saw',
        ])->assertSessionHasNoErrors();
        $this->assertSame(TeacherCourse::PENDING_REVIEW, $course->fresh()->status);

        foreach (['approve' => null, 'reject' => 'Old note'] as $decision => $note) {
            $this->actingAs($admin)
                ->from(route('admin.teacher-courses.show', $course))
                ->post(route('admin.teacher-courses.review', $course), ['revision' => $seen, 'decision' => $decision, 'review_note' => $note])
                ->assertSessionHasErrors('review')
                ->assertRedirect(route('admin.teacher-courses.show', $course));
        }
        $course->refresh();
        $this->assertSame(TeacherCourse::PENDING_REVIEW, $course->status);
        $this->assertNull($course->approved_at);

        // Missing revision is refused too.
        $this->actingAs($admin)->post(route('admin.teacher-courses.review', $course), ['decision' => 'approve'])
            ->assertSessionHasErrors('revision');

        // After reloading the page the admin sees the new content and can decide.
        $this->actingAs($admin)->get(route('admin.teacher-courses.show', $course))->assertSee('Content the admin never saw');
        $fresh = $this->revisionOnReviewPage($admin, $course);
        $this->assertGreaterThan($seen, $fresh);
        $this->actingAs($admin)->post(route('admin.teacher-courses.review', $course), ['revision' => $fresh, 'decision' => 'approve'])
            ->assertSessionHasNoErrors();
        $this->assertSame(TeacherCourse::PUBLISHED, $course->fresh()->status);
    }

    public function test_withdraw_and_resubmit_makes_an_open_review_page_stale(): void
    {
        $teacher = $this->approvedTeacher();
        $admin = $this->admin();
        $course = $this->draftWithOwnLesson($teacher);
        $this->actingAs($teacher)->patch(route('teacher.courses.status', $course), ['action' => 'submit']);
        $seen = $this->revisionOnReviewPage($admin, $course);

        $this->actingAs($teacher)->patch(route('teacher.courses.status', $course), ['action' => 'withdraw']);
        $this->actingAs($teacher)->patch(route('teacher.courses.status', $course), ['action' => 'submit']);
        $this->assertSame(TeacherCourse::PENDING_REVIEW, $course->fresh()->status);

        $this->actingAs($admin)->post(route('admin.teacher-courses.review', $course), ['revision' => $seen, 'decision' => 'approve'])
            ->assertSessionHasErrors('review');
        $this->assertSame(TeacherCourse::PENDING_REVIEW, $course->fresh()->status);
    }

    public function test_every_kind_of_content_change_bumps_the_revision(): void
    {
        $teacher = $this->approvedTeacher();
        $course = $this->draftWithOwnLesson($teacher);
        $lesson = $course->teacherLessons()->first();
        $revision = fn () => $course->fresh()->content_revision;

        $steps = [
            fn () => $this->actingAs($teacher)->put(route('teacher.courses.update', $course), [
                'title' => 'New title', 'description' => $course->description, 'language_id' => $course->language_id,
                'level' => $course->level, 'price_type' => 'free', 'enrollment_mode' => 'instant',
            ]),
            fn () => $this->actingAs($teacher)->post(route('teacher.courses.teacher-lessons.store', $course), ['title' => 'Second', 'body' => 'b']),
            fn () => $this->actingAs($teacher)->put(route('teacher.courses.teacher-lessons.update', [$course, $lesson]), ['title' => 'Emails v2', 'body' => 'c']),
            fn () => $this->actingAs($teacher)->patch(route('teacher.courses.teacher-lessons.move', [$course, $lesson]), ['direction' => 'down']),
            fn () => $this->actingAs($teacher)->delete(route('teacher.courses.teacher-lessons.destroy', [$course, $lesson])),
        ];

        foreach ($steps as $i => $step) {
            $before = $revision();
            $step()->assertSessionHasNoErrors();
            $this->assertSame($before + 1, $revision(), 'step '.$i);
        }
    }
}
