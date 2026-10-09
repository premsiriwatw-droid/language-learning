<?php

namespace Tests\Feature;

use App\Models\Exercise;
use App\Models\Lesson;
use App\Models\LessonProgress;
use App\Models\Unit;
use App\Models\User;
use App\Models\Vocabulary;
use App\Services\Progress\UnitAccess;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\CreatesContentLesson;
use Tests\TestCase;

class UnitCompletionAccessTest extends TestCase
{
    use RefreshDatabase;
    use CreatesContentLesson;

    public function test_next_unit_requires_every_playable_lesson_even_if_last_lesson_is_completed(): void
    {
        $user = User::factory()->create();
        $first = $this->createContentLesson();
        $first->unit->update(['position' => 1]);
        $first->update(['position' => 1]);
        $second = Lesson::forceCreate(['unit_id' => $first->unit_id, 'title' => 'บทที่สอง', 'position' => 2]);
        $nextUnit = Unit::forceCreate(['course_id' => $first->unit->course_id, 'title' => 'Unit ถัดไป', 'position' => 2]);
        $next = Lesson::forceCreate(['unit_id' => $nextUnit->id, 'title' => 'บทถัดไป', 'position' => 1]);

        foreach ([$first, $second, $next] as $lesson) {
            Vocabulary::factory()->create(['lesson_id' => $lesson->id]);
        }

        // จำลองข้อมูลเก่าที่จบเพียงบทสุดท้าย แต่บทแรกยังไม่จบ
        LessonProgress::create([
            'user_id' => $user->id, 'lesson_id' => $second->id,
            'completed_at' => now(), 'last_step' => 1, 'xp' => 20, 'stars' => 1,
        ]);

        $exercise = Exercise::factory()->create(['lesson_id' => $next->id, 'type' => 'multiple_choice']);
        $this->actingAs($user);

        $this->assertFalse(app(UnitAccess::class)->canOpen($user, $nextUnit));
        $this->get(route('units.lessons', $nextUnit))->assertForbidden();
        $this->get(route('lessons.learn', $next))->assertForbidden();
        $this->get(route('lessons.show', $next))->assertForbidden();
        $this->get(route('quiz.show', $exercise))->assertForbidden();
        $this->post(route('quiz.submit', $exercise), [])->assertForbidden();

        // เมื่อจบบทแรกด้วย Unit ถัดไปจึงเปิด
        $this->get(route('lessons.learn', $first))->assertRedirect();
        $this->post(route('lessons.learn.submit', ['lesson' => $first->id, 'step' => 1]))->assertRedirect();

        $this->assertTrue(app(UnitAccess::class)->canOpen($user, $nextUnit));
        $this->get(route('units.lessons', $nextUnit))->assertOk();
        $this->get(route('lessons.learn', $next))->assertRedirect();

        $otherUser = User::factory()->create();
        $this->assertFalse(app(UnitAccess::class)->canOpen($otherUser, $nextUnit));
        $this->assertFalse(app(UnitAccess::class)->canOpen(null, $nextUnit));
    }
}
