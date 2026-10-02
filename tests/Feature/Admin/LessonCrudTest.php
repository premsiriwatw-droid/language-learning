<?php

namespace Tests\Feature\Admin;

use App\Models\Exercise;
use App\Models\Lesson;
use App\Models\User;
use App\Models\Vocabulary;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\Support\CreatesLearningStructure;
use Tests\TestCase;

class LessonCrudTest extends TestCase
{
    use CreatesLearningStructure;
    use LazilyRefreshDatabase;

    public function test_new_lessons_are_appended_to_the_end_of_the_unit_automatically(): void
    {
        $admin = User::factory()->admin()->create();
        $unit = $this->createCourseWithUnit();

        $this->actingAs($admin)->post(route('admin.units.lessons.store', $unit), ['title' => 'Greetings']);
        $this->actingAs($admin)->post(route('admin.units.lessons.store', $unit), ['title' => 'Numbers']);

        $titles = $unit->lessons()->pluck('title')->all();

        $this->assertSame(['Greetings', 'Numbers'], $titles);
        $this->assertSame([1, 2], $unit->lessons()->pluck('position')->all());
    }

    public function test_admin_can_edit_a_lessons_title_and_content(): void
    {
        $admin = User::factory()->admin()->create();
        $lesson = $this->createUnitWithLesson();

        $response = $this->actingAs($admin)->put(route('admin.lessons.update', $lesson), [
            'title' => 'Updated title',
            'content' => 'Updated content',
        ]);

        $response->assertRedirect();
        $lesson->refresh();
        $this->assertSame('Updated title', $lesson->title);
        $this->assertSame('Updated content', $lesson->content);
    }

    public function test_deleting_a_lesson_cascades_to_its_content_and_resequences_siblings(): void
    {
        $admin = User::factory()->admin()->create();
        $unit = $this->createCourseWithUnit();
        $lesson1 = Lesson::factory()->for($unit)->create();
        $lesson2 = Lesson::factory()->for($unit)->create();
        $lesson3 = Lesson::factory()->for($unit)->create();
        $vocabulary = Vocabulary::factory()->for($lesson2)->create();
        $exercise = Exercise::factory()->for($lesson2)->create();

        $response = $this->actingAs($admin)->delete(route('admin.lessons.destroy', $lesson2));

        $response->assertRedirect(route('admin.units.lessons.index', $unit));
        $this->assertModelMissing($lesson2);
        $this->assertModelMissing($vocabulary);
        $this->assertModelMissing($exercise);

        // positions close the gap left by the deleted lesson
        $this->assertSame(1, $lesson1->fresh()->position);
        $this->assertSame(2, $lesson3->fresh()->position);
    }

    public function test_reorder_persists_a_new_lesson_sequence(): void
    {
        $admin = User::factory()->admin()->create();
        $unit = $this->createCourseWithUnit();
        $lessonA = Lesson::factory()->for($unit)->create();
        $lessonB = Lesson::factory()->for($unit)->create();
        $lessonC = Lesson::factory()->for($unit)->create();

        $response = $this->actingAs($admin)->patch(route('admin.units.lessons.reorder', $unit), [
            'order' => [$lessonC->id, $lessonA->id, $lessonB->id],
        ]);

        $response->assertRedirect();
        $this->assertSame(1, $lessonC->fresh()->position);
        $this->assertSame(2, $lessonA->fresh()->position);
        $this->assertSame(3, $lessonB->fresh()->position);
    }

    public function test_next_lesson_and_previous_lesson_follow_position_order(): void
    {
        $unit = $this->createCourseWithUnit();
        $lesson1 = Lesson::factory()->for($unit)->create();
        $lesson2 = Lesson::factory()->for($unit)->create();
        $lesson3 = Lesson::factory()->for($unit)->create();

        $this->assertTrue($lesson1->nextLesson()->is($lesson2));
        $this->assertNull($lesson3->nextLesson());

        $this->assertNull($lesson1->previousLesson());
        $this->assertTrue($lesson3->previousLesson()->is($lesson2));
    }

    public function test_editing_a_lesson_does_not_touch_its_vocabulary_or_exercises(): void
    {
        $admin = User::factory()->admin()->create();
        $lesson = $this->createUnitWithLesson();
        $vocabulary = Vocabulary::factory()->for($lesson)->create();
        $exercise = Exercise::factory()->for($lesson)->create();

        $this->actingAs($admin)->put(route('admin.lessons.update', $lesson), [
            'title' => 'New title',
        ]);

        $this->assertModelExists($vocabulary);
        $this->assertModelExists($exercise);
        $this->assertSame($lesson->id, $vocabulary->fresh()->lesson_id);
        $this->assertSame($lesson->id, $exercise->fresh()->lesson_id);
    }
}
