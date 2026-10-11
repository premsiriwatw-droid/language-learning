<?php

namespace Tests\Feature\TeacherCourses;

use App\Models\CourseEnrollment;
use App\Models\TeacherCourse;
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
            ->patch(route('teacher.courses.status', $course), ['status' => 'closed'])
            ->assertForbidden();

        $course->refresh();
        $this->assertSame('Owner course', $course->title);
        $this->assertSame(TeacherCourse::PUBLISHED, $course->status);
    }

    public function test_owner_edits_publishes_and_closes_course(): void
    {
        $teacher = $this->approvedTeacher();
        [$language, $lessons] = $this->languageWithLessons();
        $course = $this->teacherCourse($teacher, $language, [], fn ($f) => $f->draft());
        $course->lessons()->detach();

        $this->actingAs($teacher)
            ->patch(route('teacher.courses.status', $course), ['status' => 'published'])
            ->assertSessionHasErrors('status');
        $this->assertSame(TeacherCourse::DRAFT, $course->fresh()->status);

        $this->actingAs($teacher)
            ->put(route('teacher.courses.update', $course), $this->validPayload($language->id, [$lessons[0]->id], ['title' => 'Renamed']))
            ->assertSessionHasNoErrors();

        $this->actingAs($teacher)
            ->patch(route('teacher.courses.status', $course), ['status' => 'published'])
            ->assertSessionHasNoErrors();
        $course->refresh();
        $this->assertSame('Renamed', $course->title);
        $this->assertSame(TeacherCourse::PUBLISHED, $course->status);
        $this->assertNotNull($course->published_at);

        $this->actingAs($teacher)->patch(route('teacher.courses.status', $course), ['status' => 'closed']);
        $this->assertSame(TeacherCourse::CLOSED, $course->fresh()->status);
    }

    public function test_course_with_learners_cannot_go_back_to_draft(): void
    {
        $teacher = $this->approvedTeacher();
        $course = $this->teacherCourse($teacher);
        $this->actingAs($this->learner())->post(route('teacher-courses.enroll', $course));

        $this->actingAs($teacher)
            ->patch(route('teacher.courses.status', $course), ['status' => 'draft'])
            ->assertSessionHasErrors('status');
        $this->assertSame(TeacherCourse::PUBLISHED, $course->fresh()->status);
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
}
