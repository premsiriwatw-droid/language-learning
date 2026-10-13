<?php

namespace Tests\Feature\TeacherCourses;

use App\Models\CourseEnrollment;
use App\Models\TeacherCourse;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\CreatesTeacherCourses;
use Tests\TestCase;

class TeacherCourseManagementTest extends TestCase
{
    use CreatesTeacherCourses;
    use RefreshDatabase;

    private function validPayload(int $languageId, array $lessonIds, array $overrides = []): array
    {
        return $overrides + [
            'title' => 'English Conversation Beginner',
            'description' => 'Speak simple English every day.',
            'language_id' => $languageId,
            'level' => 'beginner',
            'price_type' => 'free',
            'price' => '',
            'enrollment_mode' => 'instant',
            'lessons' => $lessonIds,
        ];
    }

    public function test_only_approved_teachers_can_open_course_management(): void
    {
        $this->get(route('teacher.courses.index'))->assertRedirect(route('login'));

        $this->actingAs($this->learner())->get(route('teacher.courses.index'))->assertForbidden();
        $this->actingAs($this->pendingTeacher())->get(route('teacher.courses.create'))->assertForbidden();

        [$language, $lessons] = $this->languageWithLessons();
        $this->actingAs($this->pendingTeacher())
            ->post(route('teacher.courses.store'), $this->validPayload($language->id, [$lessons[0]->id]))
            ->assertForbidden();
        $this->assertDatabaseCount('teacher_courses', 0);

        $this->actingAs($this->approvedTeacher())->get(route('teacher.courses.index'))->assertOk();
    }

    public function test_approved_teacher_creates_several_draft_courses_owned_by_them(): void
    {
        $teacher = $this->approvedTeacher();
        $other = $this->approvedTeacher();
        [$language, $lessons] = $this->languageWithLessons();

        $this->actingAs($teacher)
            ->post(route('teacher.courses.store'), $this->validPayload($language->id, [$lessons[1]->id, $lessons[0]->id], [
                // Ownership and status must never come from the form.
                'teacher_id' => $other->id,
                'status' => 'published',
            ]))
            ->assertRedirect();

        $this->actingAs($teacher)
            ->post(route('teacher.courses.store'), $this->validPayload($language->id, [$lessons[0]->id], [
                'title' => 'Second course', 'price_type' => 'paid', 'price' => '199',
                'enrollment_mode' => 'approval_required', 'level' => 'intermediate',
            ]))
            ->assertRedirect();

        $courses = TeacherCourse::orderBy('id')->get();
        $this->assertCount(2, $courses);
        $this->assertTrue($courses->every(fn ($c) => $c->teacher_id === $teacher->id && $c->status === TeacherCourse::DRAFT));
        $this->assertSame([$lessons[1]->id, $lessons[0]->id], $courses[0]->lessons->pluck('id')->all());
        $this->assertNull($courses[0]->price);
        $this->assertSame('199.00', $courses[1]->price);
        $this->assertSame(TeacherCourse::APPROVAL_REQUIRED, $courses[1]->enrollment_mode);

        $this->actingAs($teacher)->get(route('teacher.courses.index'))
            ->assertOk()->assertSee('English Conversation Beginner')->assertSee('Second course');
    }

    public function test_course_validation_rules(): void
    {
        $teacher = $this->approvedTeacher();
        [$english, $englishLessons] = $this->languageWithLessons('English');
        [, $chineseLessons] = $this->languageWithLessons('Chinese');

        $this->actingAs($teacher)
            ->post(route('teacher.courses.store'), $this->validPayload($english->id, [], ['price_type' => 'paid', 'price' => '']))
            ->assertSessionHasErrors('price');

        $this->actingAs($teacher)
            ->post(route('teacher.courses.store'), $this->validPayload($english->id, [$englishLessons[0]->id, $chineseLessons[0]->id]))
            ->assertSessionHasErrors('lessons');

        $this->actingAs($teacher)
            ->post(route('teacher.courses.store'), $this->validPayload($english->id, [], ['level' => 'expert', 'title' => '']))
            ->assertSessionHasErrors(['level', 'title']);

        $this->assertDatabaseCount('teacher_courses', 0);

        // A free course never keeps a price even if one is sent.
        $this->actingAs($teacher)
            ->post(route('teacher.courses.store'), $this->validPayload($english->id, [], ['price_type' => 'free', 'price' => '500']))
            ->assertSessionHasNoErrors();
        $this->assertNull(TeacherCourse::sole()->price);
    }

    public function test_teacher_cannot_touch_another_teachers_course(): void
    {
        $owner = $this->approvedTeacher();
        $intruder = $this->approvedTeacher();
        $course = $this->teacherCourse($owner, null, ['title' => 'Owner course']);

        $this->actingAs($intruder)->get(route('teacher.courses.edit', $course))->assertForbidden();
        $this->actingAs($intruder)->get(route('teacher.courses.enrollments', $course))->assertForbidden();
        $this->actingAs($intruder)
            ->put(route('teacher.courses.update', $course), $this->validPayload($course->language_id, [], ['title' => 'Hacked']))
            ->assertForbidden();
        $this->actingAs($intruder)
            ->patch(route('teacher.courses.status', $course), ['action' => 'close'])
            ->assertForbidden();

        $course->refresh();
        $this->assertSame('Owner course', $course->title);
        $this->assertSame(TeacherCourse::PUBLISHED, $course->status);
    }

    public function test_owner_submits_for_review_and_cannot_publish_directly(): void
    {
        $teacher = $this->approvedTeacher();
        $admin = User::factory()->create(['role' => 'admin']);
        [$language, $lessons] = $this->languageWithLessons();
        $course = $this->teacherCourse($teacher, $language, [], fn ($f) => $f->draft());
        $course->lessons()->detach();

        // No lessons yet: cannot be sent for review.
        $this->actingAs($teacher)
            ->patch(route('teacher.courses.status', $course), ['action' => 'submit'])
            ->assertSessionHasErrors('status');
        $this->assertSame(TeacherCourse::DRAFT, $course->fresh()->status);

        // The old "publish it myself" request is no longer accepted.
        $this->actingAs($teacher)
            ->patch(route('teacher.courses.status', $course), ['status' => 'published'])
            ->assertSessionHasErrors('action');

        $this->actingAs($teacher)
            ->put(route('teacher.courses.update', $course), $this->validPayload($language->id, [$lessons[0]->id], ['title' => 'Renamed']))
            ->assertSessionHasNoErrors();

        $this->actingAs($teacher)
            ->patch(route('teacher.courses.status', $course), ['action' => 'submit'])
            ->assertSessionHasNoErrors();
        $course->refresh();
        $this->assertSame('Renamed', $course->title);
        $this->assertSame(TeacherCourse::PENDING_REVIEW, $course->status);
        $this->assertNull($course->published_at);
        $this->actingAs($this->learner())->get(route('teacher-courses.index'))->assertDontSee('Renamed');

        $this->actingAs($admin)
            ->post(route('admin.teacher-courses.review', $course), ['revision' => $course->fresh()->content_revision, 'decision' => 'approve'])
            ->assertSessionHasNoErrors();
        $course->refresh();
        $this->assertSame(TeacherCourse::PUBLISHED, $course->status);
        $this->assertNotNull($course->published_at);

        $this->actingAs($teacher)->patch(route('teacher.courses.status', $course), ['action' => 'close']);
        $this->assertSame(TeacherCourse::CLOSED, $course->fresh()->status);

        // Closed but untouched since approval: reopens without another review.
        $this->actingAs($teacher)->patch(route('teacher.courses.status', $course), ['action' => 'reopen'])->assertSessionHasNoErrors();
        $this->assertSame(TeacherCourse::PUBLISHED, $course->fresh()->status);
    }

    public function test_course_with_learners_never_goes_back_to_draft(): void
    {
        $teacher = $this->approvedTeacher();
        $course = $this->teacherCourse($teacher);
        $this->actingAs($this->learner())->post(route('teacher-courses.enroll', $course));

        // Editing a live course sends it back for review…
        $this->actingAs($teacher)
            ->put(route('teacher.courses.update', $course), $this->validPayload($course->language_id, $course->lessons->pluck('id')->all(), ['title' => 'Edited title']))
            ->assertSessionHasNoErrors();
        $this->assertSame(TeacherCourse::PENDING_REVIEW, $course->fresh()->status);

        // …and withdrawing the review keeps it closed (not a draft), because a learner is inside.
        $this->actingAs($teacher)
            ->patch(route('teacher.courses.status', $course), ['action' => 'withdraw'])
            ->assertSessionHasNoErrors();
        $this->assertSame(TeacherCourse::CLOSED, $course->fresh()->status);
        $this->assertSame(1, CourseEnrollment::count());
    }

    public function test_teacher_who_loses_approval_loses_management_and_listing(): void
    {
        $teacher = $this->approvedTeacher();
        $course = $this->teacherCourse($teacher, null, ['title' => 'Soon hidden']);
        $learner = $this->learner();

        $this->actingAs($learner)->get(route('teacher-courses.index'))->assertSee('Soon hidden');

        $teacher->update(['role' => 'user']);

        $this->actingAs($teacher)->get(route('teacher.courses.edit', $course))->assertForbidden();
        $this->actingAs($learner)->get(route('teacher-courses.index'))->assertDontSee('Soon hidden');
        $this->actingAs($learner)->post(route('teacher-courses.enroll', $course))->assertForbidden();
    }

    public function test_published_or_closed_course_cannot_be_saved_without_lessons(): void
    {
        $teacher = $this->approvedTeacher();
        [$language, $lessons] = $this->languageWithLessons();

        foreach (['published' => null, 'closed' => fn ($f) => $f->closed()] as $label => $state) {
            $course = $this->teacherCourse($teacher, $language, [], $state);

            $this->actingAs($teacher)
                ->put(route('teacher.courses.update', $course), $this->validPayload($language->id, []))
                ->assertSessionHasErrors('lessons');

            $this->assertSame(count($lessons), $course->lessons()->count(), $label.' course lost its lessons');
        }

        // A draft is not visible to learners yet, so it may be saved empty.
        $draft = $this->teacherCourse($teacher, $language, [], fn ($f) => $f->draft());
        $this->actingAs($teacher)
            ->put(route('teacher.courses.update', $draft), $this->validPayload($language->id, []))
            ->assertSessionHasNoErrors();
        $this->assertSame(0, $draft->lessons()->count());
    }
}
