<?php

namespace Tests\Feature;

use App\Models\Lesson;
use App\Models\Unit;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\Support\CreatesLearningStructure;
use Tests\TestCase;

class LearningStructureOrderingTest extends TestCase
{
    use CreatesLearningStructure;
    use LazilyRefreshDatabase;

    public function test_position_is_scoped_per_parent_not_global(): void
    {
        $courseA = $this->createLanguageWithCourse();
        $courseB = $this->createLanguageWithCourse();

        Unit::factory()->for($courseA)->create();
        $firstUnitOfB = Unit::factory()->for($courseB)->create();

        // Course B's first unit still gets position 1, even though
        // Course A already has a unit in the table.
        $this->assertSame(1, $firstUnitOfB->position);
    }

    public function test_units_relation_always_returns_lesson_plan_order_regardless_of_creation_order(): void
    {
        $course = $this->createLanguageWithCourse();

        $third = Unit::factory()->for($course)->create(['position' => 3]);
        $first = Unit::factory()->for($course)->create(['position' => 1]);
        $second = Unit::factory()->for($course)->create(['position' => 2]);

        $this->assertSame(
            [$first->id, $second->id, $third->id],
            $course->fresh()->units->pluck('id')->all()
        );
    }

    public function test_lessons_relation_always_returns_lesson_plan_order_regardless_of_creation_order(): void
    {
        $unit = $this->createCourseWithUnit();

        $third = Lesson::factory()->for($unit)->create(['position' => 3]);
        $first = Lesson::factory()->for($unit)->create(['position' => 1]);
        $second = Lesson::factory()->for($unit)->create(['position' => 2]);

        $this->assertSame(
            [$first->id, $second->id, $third->id],
            $unit->fresh()->lessons->pluck('id')->all()
        );
    }

    public function test_resequence_closes_gaps_after_a_row_is_removed_outside_the_normal_flow(): void
    {
        $course = $this->createLanguageWithCourse();
        $unit1 = Unit::factory()->for($course)->create();
        $unit2 = Unit::factory()->for($course)->create();
        $unit3 = Unit::factory()->for($course)->create();

        // simulate a deletion that bypassed the admin controller
        $unit2->delete();

        Unit::resequence($course->id);

        $this->assertSame(1, $unit1->fresh()->position);
        $this->assertSame(2, $unit3->fresh()->position);
    }

    public function test_supports_more_units_and_lessons_than_originally_seeded_without_any_hardcoded_limit(): void
    {
        $course = $this->createLanguageWithCourse();

        // Mirrors the Chinese track growing to 3 Units / 11 Lessons: the
        // system should not care about any particular count.
        $units = Unit::factory()->for($course)->count(5)->create();

        foreach ($units as $index => $unit) {
            Lesson::factory()->for($unit)->count($index + 1)->create();
        }

        $this->assertCount(5, $course->fresh()->units);
        $this->assertSame(1 + 2 + 3 + 4 + 5, Lesson::whereIn('unit_id', $units->pluck('id'))->count());
    }
}
