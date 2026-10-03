<?php

namespace Tests\Feature\Admin;

use App\Models\Language;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\Support\CreatesLearningStructure;
use Tests\TestCase;

class LanguageCrudTest extends TestCase
{
    use CreatesLearningStructure;
    use LazilyRefreshDatabase;

    public function test_admin_can_create_a_language(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)->post(route('admin.languages.store'), [
            'name' => 'Chinese',
        ]);

        $response->assertRedirect();
        $response->assertSessionHasNoErrors();
        $this->assertDatabaseHas('languages', ['name' => 'Chinese']);
    }

    public function test_name_is_required(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)->post(route('admin.languages.store'), [
            'name' => '',
        ]);

        $response->assertSessionHasErrors('name');
        $this->assertDatabaseCount('languages', 0);
    }

    public function test_admin_can_rename_a_language(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $language = Language::factory()->create(['name' => 'Chinese']);

        $response = $this->actingAs($admin)->put(route('admin.languages.update', $language), [
            'name' => 'Mandarin Chinese',
        ]);

        $response->assertRedirect();
        $this->assertSame('Mandarin Chinese', $language->fresh()->name);
    }

    public function test_deleting_a_language_cascades_to_its_courses_units_and_lessons(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $lesson = $this->createUnitWithLesson();
        $course = $lesson->unit->course;
        $language = $course->language;

        $response = $this->actingAs($admin)->delete(route('admin.languages.destroy', $language));

        $response->assertRedirect(route('admin.languages.index'));
        $this->assertModelMissing($language);
        $this->assertModelMissing($course);
        $this->assertModelMissing($lesson->unit);
        $this->assertModelMissing($lesson);
    }
}
