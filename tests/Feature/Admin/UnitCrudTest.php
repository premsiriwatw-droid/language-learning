<?php

namespace Tests\Feature\Admin;

use App\Models\Unit;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\Support\CreatesLearningStructure;
use Tests\TestCase;

class UnitCrudTest extends TestCase
{
    use CreatesLearningStructure;
    use LazilyRefreshDatabase;

    public function test_new_units_are_appended_to_the_end_of_the_course_automatically(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $course = $this->createLanguageWithCourse();

        $this->actingAs($admin)->post(route('admin.courses.units.store', $course), ['title' => 'Unit 1']);
        $this->actingAs($admin)->post(route('admin.courses.units.store', $course), ['title' => 'Unit 2']);
        $this->actingAs($admin)->post(route('admin.courses.units.store', $course), ['title' => 'Unit 3']);

        $titles = $course->units()->pluck('title')->all();

        $this->assertSame(['Unit 1', 'Unit 2', 'Unit 3'], $titles);
        $this->assertSame([1, 2, 3], $course->units()->pluck('position')->all());
    }

    public function test_admin_can_rename_a_unit_without_changing_its_course(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $unit = $this->createCourseWithUnit();

        $response = $this->actingAs($admin)->put(route('admin.units.update', $unit), [
            'title' => 'Renamed unit',
        ]);

        $response->assertRedirect();
        $unit->refresh();
        $this->assertSame('Renamed unit', $unit->title);
    }

    public function test_deleting_a_unit_cascades_to_its_lessons_and_resequences_siblings(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $course = $this->createLanguageWithCourse();
        $unit1 = Unit::factory()->for($course)->create();
        $unit2 = Unit::factory()->for($course)->create();
        $unit3 = Unit::factory()->for($course)->create();
        $lessonInUnit2 = \App\Models\Lesson::factory()->for($unit2)->create();

        $response = $this->actingAs($admin)->delete(route('admin.units.destroy', $unit2));

        $response->assertRedirect(route('admin.courses.units.index', $course));
        $this->assertModelMissing($unit2);
        $this->assertModelMissing($lessonInUnit2);

        // positions close the gap left by the deleted unit
        $this->assertSame(1, $unit1->fresh()->position);
        $this->assertSame(2, $unit3->fresh()->position);
    }

    public function test_reorder_persists_a_new_unit_sequence(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $course = $this->createLanguageWithCourse();
        $unitA = Unit::factory()->for($course)->create();
        $unitB = Unit::factory()->for($course)->create();
        $unitC = Unit::factory()->for($course)->create();

        $response = $this->actingAs($admin)->patch(route('admin.courses.units.reorder', $course), [
            'order' => [$unitC->id, $unitA->id, $unitB->id],
        ]);

        $response->assertRedirect();
        $this->assertSame(1, $unitC->fresh()->position);
        $this->assertSame(2, $unitA->fresh()->position);
        $this->assertSame(3, $unitB->fresh()->position);
    }

    public function test_reorder_rejects_a_list_that_does_not_match_the_courses_units(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $course = $this->createLanguageWithCourse();
        $unitA = Unit::factory()->for($course)->create();
        $unitB = Unit::factory()->for($course)->create();
        $otherCourseUnit = $this->createCourseWithUnit();

        // missing unitB, and includes a unit from a different course
        $response = $this->actingAs($admin)->patch(route('admin.courses.units.reorder', $course), [
            'order' => [$unitA->id, $otherCourseUnit->id],
        ]);

        $response->assertSessionHasErrors('order');
        $this->assertSame(1, $unitA->fresh()->position);
        $this->assertSame(2, $unitB->fresh()->position);
    }

    public function test_next_unit_and_previous_unit_follow_position_order(): void
    {
        $course = $this->createLanguageWithCourse();
        $unit1 = Unit::factory()->for($course)->create();
        $unit2 = Unit::factory()->for($course)->create();
        $unit3 = Unit::factory()->for($course)->create();

        $this->assertTrue($unit1->nextUnit()->is($unit2));
        $this->assertTrue($unit2->nextUnit()->is($unit3));
        $this->assertNull($unit3->nextUnit());

        $this->assertNull($unit1->previousUnit());
        $this->assertTrue($unit2->previousUnit()->is($unit1));
        $this->assertTrue($unit3->previousUnit()->is($unit2));
    }
}
