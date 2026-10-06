<?php

namespace Tests\Feature;

use App\Models\Lesson;
use App\Models\Unit;
use App\Models\User;
use App\Models\Vocabulary;
use App\Services\Progress\LessonAccess;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\CreatesContentLesson;
use Tests\TestCase;

class LessonUnlockTest extends TestCase
{
    use RefreshDatabase;
    use CreatesContentLesson;

    public function test_locked_lesson_cannot_be_opened_or_submitted(): void
    {
        $user = User::factory()->create();
        [$first, $second] = $this->createLessonPair();

        $this->actingAs($user);

        $this->get(route('lessons.learn', $first))
            ->assertRedirect();

        $this->get(route('lessons.learn', $second))
            ->assertForbidden();

        $this->get(route('lessons.learn.step', [
            'lesson' => $second->id,
            'step' => 1,
        ]))->assertForbidden();

        $this->post(route('lessons.learn.submit', [
            'lesson' => $second->id,
            'step' => 1,
        ]))->assertForbidden();

        $this->get(route('lessons.learn.summary', $second))
            ->assertForbidden();

        $this->assertDatabaseCount('lesson_progress', 0);
    }

    public function test_finishing_first_lesson_unlocks_second_only_for_that_user(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        [$first, $second] = $this->createLessonPair();

        $this->actingAs($user);
        $this->finishVocabularyLesson($first);

        $this->get(route('lessons.learn', $second))
            ->assertRedirect();

        // บทที่จบแล้วกลับมาเรียนได้
        $this->get(route('lessons.learn', $first))
            ->assertRedirect();

        // ผู้ใช้อีกคนยังไม่ได้ปลดล็อก
        $this->actingAs($otherUser)
            ->get(route('lessons.learn', $second))
            ->assertForbidden();

        $this->assertDatabaseMissing('lesson_progress', [
            'user_id' => $otherUser->id,
            'lesson_id' => $first->id,
        ]);
    }

    public function test_completion_unlocks_next_unit_but_not_another_courses_second_lesson(): void
    {
        $user = User::factory()->create();
        $first = $this->createContentLesson();

        Vocabulary::factory()->create([
            'lesson_id' => $first->id,
        ]);

        $nextUnit = Unit::forceCreate([
            'course_id' => $first->unit->course_id,
            'title' => 'Unit ถัดไป',
        ]);

        $nextLesson = Lesson::forceCreate([
            'unit_id' => $nextUnit->id,
            'title' => 'บทใน Unit ถัดไป',
        ]);

        Vocabulary::factory()->create([
            'lesson_id' => $nextLesson->id,
        ]);

        [$otherFirst, $otherSecond] = $this->createLessonPair();

        $this->actingAs($user);

        $this->get(route('lessons.learn', $nextLesson))
            ->assertForbidden();

        $this->finishVocabularyLesson($first);

        $this->get(route('lessons.learn', $nextLesson))
            ->assertRedirect();

        // แต่ละ Course มีบทแรกเปิดให้เรียน
        $this->get(route('lessons.learn', $otherFirst))
            ->assertRedirect();

        // การจบ Course แรกไม่ปลดล็อกบทที่สองของอีก Course
        $this->get(route('lessons.learn', $otherSecond))
            ->assertForbidden();
    }

    public function test_empty_lesson_does_not_block_the_next_playable_lesson(): void
    {
        $user = User::factory()->create();

        // บทแรกยังไม่มีเนื้อหา
        $emptyLesson = $this->createContentLesson();

        $playableLesson = Lesson::forceCreate([
            'unit_id' => $emptyLesson->unit_id,
            'title' => 'บทที่มีเนื้อหา',
        ]);

        Vocabulary::factory()->create([
            'lesson_id' => $playableLesson->id,
        ]);

        $this->actingAs($user);

        $this->get(route('lessons.learn', $emptyLesson))
            ->assertForbidden();

        $this->get(route('lessons.learn', $playableLesson))
            ->assertRedirect();

        $states = app(LessonAccess::class)->forCourse(
            (int) $emptyLesson->unit->course_id,
            $user
        );

        $this->assertFalse($states[$emptyLesson->id]['available']);
        $this->assertTrue($states[$playableLesson->id]['available']);
    }

    public function test_guest_can_try_first_lesson_but_cannot_unlock_second(): void
    {
        [$first, $second] = $this->createLessonPair();

        $this->finishVocabularyLesson($first);

        $this->get(route('lessons.learn', $second))
            ->assertForbidden();

        $this->assertDatabaseCount('lesson_progress', 0);
    }

    public function test_lesson_list_shows_updated_access_states(): void
    {
        $user = User::factory()->create();
        [$first, $second] = $this->createLessonPair();

        $this->actingAs($user);

        $listUrl = route('units.lessons', [
            'unit' => $first->unit_id,
        ]);

        $this->get($listUrl)
            ->assertOk()
            ->assertViewHas('lessonStates', fn ($states) =>
                $states[$first->id]['available'] === true
                && $states[$second->id]['available'] === false
            )
            ->assertSee('ยังไม่เปิด');

        $this->finishVocabularyLesson($first);

        $this->get($listUrl)
            ->assertOk()
            ->assertViewHas('lessonStates', fn ($states) =>
                $states[$first->id]['completed'] === true
                && $states[$second->id]['available'] === true
            )
            ->assertSee('ทบทวน');
    }

    private function createLessonPair(): array
    {
        $first = $this->createContentLesson();

        $second = Lesson::forceCreate([
            'unit_id' => $first->unit_id,
            'title' => 'บทที่สอง',
        ]);

        foreach ([$first, $second] as $lesson) {
            Vocabulary::factory()->create([
                'lesson_id' => $lesson->id,
            ]);
        }

        return [$first, $second];
    }

    private function finishVocabularyLesson(Lesson $lesson): void
    {
        $this->get(route('lessons.learn', $lesson))
            ->assertRedirect();

        $this->post(route('lessons.learn.submit', [
            'lesson' => $lesson->id,
            'step' => 1,
        ]))->assertRedirect();

        $this->get(route('lessons.learn.summary', $lesson))
            ->assertOk();
    }
}