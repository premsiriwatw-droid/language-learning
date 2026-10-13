<?php

namespace Tests\Feature\TeacherCourses;

use App\Models\TeacherCourse;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\CreatesTeacherCourses;
use Tests\TestCase;

class TeacherCourseMarketplaceTest extends TestCase
{
    use CreatesTeacherCourses;
    use RefreshDatabase;

    public function test_languages_page_links_to_teacher_courses(): void
    {
        $this->actingAs($this->learner())
            ->get(route('languages.index'))
            ->assertOk()
            ->assertSee(route('teacher-courses.index'), false)
            ->assertSee('คอร์สจากผู้สอน');
    }

    public function test_marketplace_requires_login(): void
    {
        $this->get(route('teacher-courses.index'))->assertRedirect(route('login'));
    }

    public function test_marketplace_lists_only_published_courses_of_approved_teachers(): void
    {
        $teacher = $this->approvedTeacher();
        $this->teacherCourse($teacher, null, ['title' => 'Visible published']);
        $this->teacherCourse($teacher, null, ['title' => 'Hidden draft'], fn ($f) => $f->draft());
        $this->teacherCourse($teacher, null, ['title' => 'Hidden closed'], fn ($f) => $f->closed());

        $unapproved = $this->pendingTeacher();
        $this->teacherCourse($unapproved, null, ['title' => 'Hidden unapproved']);

        $this->actingAs($this->learner())
            ->get(route('teacher-courses.index'))
            ->assertOk()
            ->assertSee('Visible published')
            ->assertSee('Verified')
            ->assertDontSee('Hidden draft')
            ->assertDontSee('Hidden closed')
            ->assertDontSee('Hidden unapproved');
    }

    public function test_marketplace_is_separate_from_system_courses(): void
    {
        [$language] = $this->languageWithLessons('English');

        $this->actingAs($this->learner())
            ->get(route('teacher-courses.index'))
            ->assertOk()
            ->assertSee('ยังไม่มีคอร์สจากผู้สอน');

        $this->assertDatabaseCount('teacher_courses', 0);
        $this->assertDatabaseCount('courses', 1);
        $this->assertTrue($language->courses()->exists());
    }

    public function test_marketplace_filters_by_language_level_and_price(): void
    {
        $teacher = $this->approvedTeacher();
        [$english] = $this->languageWithLessons('English');
        [$chinese] = $this->languageWithLessons('Chinese');

        $this->teacherCourse($teacher, $english, ['title' => 'EN free beginner']);
        $this->teacherCourse($teacher, $english, ['title' => 'EN paid advanced', 'level' => 'advanced'], fn ($f) => $f->paid(450));
        $this->teacherCourse($teacher, $chinese, ['title' => 'ZH free intermediate', 'level' => 'intermediate']);

        $learner = $this->learner();

        $this->actingAs($learner)->get(route('teacher-courses.index', ['language' => $english->id]))
            ->assertSee('EN free beginner')->assertSee('EN paid advanced')->assertDontSee('ZH free intermediate');

        $this->actingAs($learner)->get(route('teacher-courses.index', ['level' => 'intermediate']))
            ->assertSee('ZH free intermediate')->assertDontSee('EN free beginner');

        $this->actingAs($learner)->get(route('teacher-courses.index', ['price' => 'paid']))
            ->assertSee('EN paid advanced')->assertSee('฿450')->assertDontSee('EN free beginner');

        $this->actingAs($learner)->get(route('teacher-courses.index', ['level' => 'expert']))
            ->assertSessionHasErrors('level');
    }

    public function test_course_detail_shows_teacher_badge_price_and_counts(): void
    {
        $teacher = $this->approvedTeacher(['name' => 'Ms Ann']);
        $course = $this->teacherCourse($teacher, null, ['title' => 'Detail course'], fn ($f) => $f->paid(299));

        $this->actingAs($this->learner())
            ->get(route('teacher-courses.show', $course))
            ->assertOk()
            ->assertSee('Detail course')
            ->assertSee('Ms Ann (Verified)')
            ->assertSee('Verified Instructor')
            ->assertSee('฿299')
            ->assertSee('2 บท')
            ->assertSee('0 คน')
            ->assertSee('ชำระเงิน (จำลอง)');
    }

    public function test_draft_course_is_visible_only_to_its_owner(): void
    {
        $teacher = $this->approvedTeacher();
        $course = $this->teacherCourse($teacher, null, ['title' => 'Secret draft'], fn ($f) => $f->draft());

        $this->actingAs($this->learner())->get(route('teacher-courses.show', $course))->assertForbidden();
        $this->actingAs($this->approvedTeacher())->get(route('teacher-courses.show', $course))->assertForbidden();
        $this->actingAs($teacher)->get(route('teacher-courses.show', $course))->assertOk()->assertSee('นี่คือคอร์สของคุณ');
    }

    public function test_marketplace_shows_teacher_shortcut_only_to_approved_teachers(): void
    {
        $this->actingAs($this->learner())->get(route('teacher-courses.index'))
            ->assertDontSee(route('teacher.courses.index'), false);

        $this->actingAs($this->approvedTeacher())->get(route('teacher-courses.index'))
            ->assertSee(route('teacher.courses.index'), false);
    }

    public function test_language_deleted_by_admin_keeps_teacher_course(): void
    {
        $teacher = $this->approvedTeacher();
        [$language] = $this->languageWithLessons('Temporary');
        $course = $this->teacherCourse($teacher, $language);

        $language->delete();

        $this->assertModelExists($course);
        $this->assertNull($course->fresh()->language_id);
        $this->assertSame(0, $course->lessons()->count());
        $this->assertSame(TeacherCourse::PUBLISHED, $course->fresh()->status);
    }
}
