<?php

namespace Tests\Feature\Admin;

use App\Models\Answer;
use App\Models\Course;
use App\Models\Exercise;
use App\Models\Language;
use App\Models\Lesson;
use App\Models\Question;
use App\Models\Unit;
use App\Models\User;
use App\Models\Vocabulary;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\Support\CreatesLearningStructure;
use Tests\TestCase;

class LearningStructureIntegrityTest extends TestCase
{
    use CreatesLearningStructure;
    use LazilyRefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create(['role' => 'admin']);
    }

    /* ---------------------------------------------------------------
     | Validation
     * --------------------------------------------------------------- */

    public function test_language_name_must_be_unique(): void
    {
        Language::factory()->create(['name' => 'Chinese']);

        $this->actingAs($this->admin())
            ->post(route('admin.languages.store'), ['name' => 'Chinese'])
            ->assertSessionHasErrors('name');

        $this->assertSame(1, Language::where('name', 'Chinese')->count());
    }

    public function test_titles_must_be_unique_within_the_same_parent_only(): void
    {
        $admin = $this->admin();
        $courseA = $this->createLanguageWithCourse();
        $courseB = $this->createLanguageWithCourse();
        Unit::factory()->for($courseA)->create(['title' => 'Unit 1: Basics']);

        // same title in the same course -> rejected
        $this->actingAs($admin)
            ->post(route('admin.courses.units.store', $courseA), ['title' => 'Unit 1: Basics'])
            ->assertSessionHasErrors('title');

        // same title in a different course -> allowed
        $this->actingAs($admin)
            ->post(route('admin.courses.units.store', $courseB), ['title' => 'Unit 1: Basics'])
            ->assertSessionHasNoErrors();

        $this->assertSame(1, $courseA->units()->count());
        $this->assertSame(1, $courseB->units()->count());
    }

    public function test_saving_an_unchanged_title_does_not_trip_the_unique_rule(): void
    {
        $lesson = $this->createUnitWithLesson();

        $this->actingAs($this->admin())
            ->put(route('admin.lessons.update', $lesson), ['title' => $lesson->title])
            ->assertSessionHasNoErrors();
    }

    public function test_blank_and_too_long_titles_are_rejected(): void
    {
        $admin = $this->admin();
        $unit = $this->createCourseWithUnit();

        $this->actingAs($admin)
            ->post(route('admin.units.lessons.store', $unit), ['title' => '   '])
            ->assertSessionHasErrors('title');

        $this->actingAs($admin)
            ->post(route('admin.units.lessons.store', $unit), ['title' => str_repeat('ก', 256)])
            ->assertSessionHasErrors('title');

        $this->assertSame(0, $unit->lessons()->count());
    }

    /* ---------------------------------------------------------------
     | Cross-parent protection
     * --------------------------------------------------------------- */

    public function test_updating_a_unit_cannot_move_it_to_another_course_or_change_its_position(): void
    {
        $unit = $this->createCourseWithUnit();
        $otherCourse = $this->createLanguageWithCourse();
        $originalCourseId = $unit->course_id;

        $this->actingAs($this->admin())->put(route('admin.units.update', $unit), [
            'title' => 'Renamed',
            'course_id' => $otherCourse->id,
            'position' => 99,
        ]);

        $unit->refresh();
        $this->assertSame('Renamed', $unit->title);
        $this->assertSame($originalCourseId, $unit->course_id);
        $this->assertSame(1, $unit->position);
    }

    public function test_updating_a_lesson_cannot_move_it_to_another_unit(): void
    {
        $lesson = $this->createUnitWithLesson();
        $otherUnit = $this->createCourseWithUnit();
        $originalUnitId = $lesson->unit_id;

        $this->actingAs($this->admin())->put(route('admin.lessons.update', $lesson), [
            'title' => 'Renamed',
            'unit_id' => $otherUnit->id,
        ]);

        $this->assertSame($originalUnitId, $lesson->fresh()->unit_id);
    }

    public function test_creating_ignores_a_parent_id_sent_in_the_form(): void
    {
        $course = $this->createLanguageWithCourse();
        $otherLanguage = Language::factory()->create();

        $this->actingAs($this->admin())->post(route('admin.languages.courses.store', $course->language), [
            'title' => 'Injected',
            'language_id' => $otherLanguage->id,
        ]);

        $this->assertSame($course->language_id, Course::where('title', 'Injected')->value('language_id'));
    }

    public function test_reorder_rejects_ids_from_another_parent(): void
    {
        $unit = $this->createCourseWithUnit();
        $lessonA = Lesson::factory()->for($unit)->create();
        $foreignLesson = $this->createUnitWithLesson();

        $this->actingAs($this->admin())
            ->patch(route('admin.units.lessons.reorder', $unit), ['order' => [$foreignLesson->id]])
            ->assertSessionHasErrors('order');

        $this->assertSame(1, $lessonA->fresh()->position);
        $this->assertSame(1, $foreignLesson->fresh()->position);
    }

    public function test_reorder_rejects_duplicate_ids(): void
    {
        $unit = $this->createCourseWithUnit();
        $lessonA = Lesson::factory()->for($unit)->create();
        $lessonB = Lesson::factory()->for($unit)->create();

        $this->actingAs($this->admin())
            ->patch(route('admin.units.lessons.reorder', $unit), ['order' => [$lessonA->id, $lessonA->id]])
            ->assertSessionHasErrors();

        $this->assertSame(1, $lessonA->fresh()->position);
        $this->assertSame(2, $lessonB->fresh()->position);
    }

    public function test_reorder_rejects_a_stale_list_after_a_sibling_was_added(): void
    {
        $course = $this->createLanguageWithCourse();
        $unitA = Unit::factory()->for($course)->create();
        $unitB = Unit::factory()->for($course)->create();
        Unit::factory()->for($course)->create(); // added "while the admin page was open"

        $this->actingAs($this->admin())
            ->patch(route('admin.courses.units.reorder', $course), ['order' => [$unitB->id, $unitA->id]])
            ->assertSessionHasErrors('order');

        $this->assertSame(1, $unitA->fresh()->position);
    }

    /* ---------------------------------------------------------------
     | Delete confirmation
     * --------------------------------------------------------------- */

    public function test_confirm_page_lists_every_child_that_will_be_deleted(): void
    {
        $lesson = $this->createUnitWithLesson();
        Vocabulary::factory()->for($lesson)->count(2)->create();
        $exercise = Exercise::factory()->for($lesson)->create();
        $question = Question::factory()->for($exercise)->create();
        Answer::factory()->for($question)->count(3)->create();

        $response = $this->actingAs($this->admin())
            ->get(route('admin.units.delete', $lesson->unit));

        $response->assertOk();
        $response->assertSee($lesson->unit->title);
        $response->assertSeeInOrder(['Lesson', '1']);
        $response->assertSeeInOrder(['คำศัพท์', '2']);
        $response->assertSeeInOrder(['คำตอบ', '3']);
    }

    public function test_confirm_page_is_admin_only(): void
    {
        $lesson = $this->createUnitWithLesson();
        $user = User::factory()->create(['role' => 'user']);

        $this->actingAs($user)->get(route('admin.lessons.delete', $lesson))->assertForbidden();
    }

    public function test_delete_without_typed_confirmation_is_rejected(): void
    {
        $lesson = $this->createUnitWithLesson();

        $this->actingAs($this->admin())
            ->delete(route('admin.lessons.destroy', $lesson))
            ->assertSessionHasErrors('confirm_name');

        $this->assertModelExists($lesson);
    }

    public function test_delete_with_wrong_typed_name_is_rejected(): void
    {
        $unit = $this->createCourseWithUnit();
        $lesson = Lesson::factory()->for($unit)->create();

        $this->actingAs($this->admin())
            ->delete(route('admin.units.destroy', $unit), ['confirm_name' => 'something else'])
            ->assertSessionHasErrors('confirm_name');

        $this->assertModelExists($unit);
        $this->assertModelExists($lesson);
    }

    public function test_delete_cascades_down_to_questions_and_answers(): void
    {
        $lesson = $this->createUnitWithLesson();
        $exercise = Exercise::factory()->for($lesson)->create();
        $question = Question::factory()->for($exercise)->create();
        $answer = Answer::factory()->for($question)->create();

        $this->actingAs($this->admin())
            ->delete(route('admin.lessons.destroy', $lesson), ['confirm_name' => $lesson->title])
            ->assertSessionHasNoErrors();

        $this->assertModelMissing($exercise);
        $this->assertModelMissing($question);
        $this->assertModelMissing($answer);
    }

    /* ---------------------------------------------------------------
     | Ordering stability
     * --------------------------------------------------------------- */

    public function test_duplicate_positions_are_ordered_deterministically_by_id(): void
    {
        $course = $this->createLanguageWithCourse();
        $first = Unit::factory()->for($course)->create(['position' => 1]);
        $second = Unit::factory()->for($course)->create(['position' => 1]);
        $third = Unit::factory()->for($course)->create(['position' => 2]);

        $this->assertSame(
            [$first->id, $second->id, $third->id],
            $course->fresh()->units->pluck('id')->all()
        );
    }

    public function test_next_and_previous_walk_every_unit_exactly_once_even_with_duplicate_positions(): void
    {
        $course = $this->createLanguageWithCourse();
        $first = Unit::factory()->for($course)->create(['position' => 1]);
        $second = Unit::factory()->for($course)->create(['position' => 1]);
        $third = Unit::factory()->for($course)->create(['position' => 2]);

        $this->assertTrue($first->nextUnit()->is($second));
        $this->assertTrue($second->nextUnit()->is($third));
        $this->assertNull($third->nextUnit());

        $this->assertTrue($third->previousUnit()->is($second));
        $this->assertTrue($second->previousUnit()->is($first));
        $this->assertNull($first->previousUnit());
    }

    public function test_resequence_repairs_duplicate_and_gapped_positions(): void
    {
        $unit = $this->createCourseWithUnit();
        $a = Lesson::factory()->for($unit)->create(['position' => 5]);
        $b = Lesson::factory()->for($unit)->create(['position' => 5]);
        $c = Lesson::factory()->for($unit)->create(['position' => 9]);

        Lesson::resequence($unit->id);

        $this->assertSame([1, 2, 3], [$a->fresh()->position, $b->fresh()->position, $c->fresh()->position]);
    }

    public function test_positions_stay_contiguous_after_mixed_create_reorder_and_delete(): void
    {
        $admin = $this->admin();
        $unit = $this->createCourseWithUnit();

        foreach (['L1', 'L2', 'L3', 'L4'] as $title) {
            $this->actingAs($admin)->post(route('admin.units.lessons.store', $unit), ['title' => $title]);
        }

        $ids = $unit->lessons()->pluck('id', 'title');

        $this->actingAs($admin)->patch(route('admin.units.lessons.reorder', $unit), [
            'order' => [$ids['L4'], $ids['L1'], $ids['L3'], $ids['L2']],
        ]);

        $this->actingAs($admin)->delete(
            route('admin.lessons.destroy', $ids['L1']),
            ['confirm_name' => 'L1']
        );

        $this->actingAs($admin)->post(route('admin.units.lessons.store', $unit), ['title' => 'L5']);

        $this->assertSame(['L4', 'L3', 'L2', 'L5'], $unit->lessons()->pluck('title')->all());
        $this->assertSame([1, 2, 3, 4], $unit->lessons()->pluck('position')->all());
    }
}
