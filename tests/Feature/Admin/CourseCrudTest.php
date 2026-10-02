<?php

namespace Tests\Feature\Admin;

use App\Models\Course;
use App\Models\Language;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\Support\CreatesLearningStructure;
use Tests\TestCase;

class CourseCrudTest extends TestCase
{
    use CreatesLearningStructure;
    use LazilyRefreshDatabase;

    public function test_admin_can_create_a_course_under_a_language(): void
    {
        $admin = User::factory()->admin()->create();
        $language = Language::factory()->create();

        $response = $this->actingAs($admin)->post(
            route('admin.languages.courses.store', $language),
            ['title' => 'Chinese Beginner']
        );

        $response->assertRedirect();
        $this->assertDatabaseHas('courses', [
            'language_id' => $language->id,
            'title' => 'Chinese Beginner',
        ]);
    }

    public function test_title_is_required(): void
    {
        $admin = User::factory()->admin()->create();
        $language = Language::factory()->create();

        $response = $this->actingAs($admin)->post(
            route('admin.languages.courses.store', $language),
            ['title' => '']
        );

        $response->assertSessionHasErrors('title');
        $this->assertDatabaseCount('courses', 0);
    }

    public function test_admin_can_rename_a_course(): void
    {
        $admin = User::factory()->admin()->create();
        $course = Course::factory()->for(Language::factory())->create(['title' => 'Old title']);

        $response = $this->actingAs($admin)->put(route('admin.courses.update', $course), [
            'title' => 'New title',
        ]);

        $response->assertRedirect();
        $this->assertSame('New title', $course->fresh()->title);
    }

    public function test_deleting_a_course_cascades_to_its_units_and_lessons(): void
    {
        $admin = User::factory()->admin()->create();
        $unit = $this->createCourseWithUnit();
        $course = $unit->course;

        $response = $this->actingAs($admin)->delete(route('admin.courses.destroy', $course));

        $response->assertRedirect(route('admin.languages.courses.index', $course->language));
        $this->assertModelMissing($course);
        $this->assertModelMissing($unit);
    }
}
