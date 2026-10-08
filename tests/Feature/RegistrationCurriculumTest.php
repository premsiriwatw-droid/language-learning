<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\Language;
use App\Models\Lesson;
use App\Models\Unit;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegistrationCurriculumTest extends TestCase
{
    use RefreshDatabase;

    public function test_empty_catalogue_has_truthful_language_specific_messages(): void
    {
        $this->blade('<x-registration-curriculum />')
            ->assertSee('ยังไม่มีบทเรียนภาษาอังกฤษในระบบ')
            ->assertSee('ยังไม่มีบทเรียนภาษาจีนในระบบ')
            ->assertSee('data-curriculum-panel="england"', false)
            ->assertSee('data-curriculum-panel="china"', false)
            ->assertDontSee('HSK 1')
            ->assertDontSee('Lesson 1');
    }

    public function test_each_language_shows_real_titles_in_lesson_plan_order_with_preview_counts(): void
    {
        $english = Language::factory()->create(['name' => 'English']);
        $englishCourse = Course::factory()->for($english)->create(['title' => 'English foundations']);
        $laterUnit = Unit::factory()->for($englishCourse)->create(['title' => 'Travel unit', 'position' => 2]);
        $firstUnit = Unit::factory()->for($englishCourse)->create(['title' => 'Greetings unit', 'position' => 1]);
        foreach ([4, 2, 3, 1] as $position) {
            Lesson::factory()->for($firstUnit)->create([
                'title' => 'Ordered lesson '.$position,
                'position' => $position,
            ]);
        }
        Lesson::factory()->for($laterUnit)->create(['title' => 'At the airport']);

        $chinese = Language::factory()->create(['name' => 'ภาษาจีน']);
        $chineseCourse = Course::factory()->for($chinese)->create(['title' => 'Chinese foundations']);
        $chineseUnit = Unit::factory()->for($chineseCourse)->create(['title' => 'Chinese introductions']);
        Lesson::factory()->for($chineseUnit)->create(['title' => '你好']);

        $otherLanguage = Language::factory()->create(['name' => 'French']);
        Course::factory()->for($otherLanguage)->create(['title' => 'French course must stay out']);

        $this->blade('<x-registration-curriculum />')
            ->assertSeeInOrder([
                'id="curriculum-england"',
                'English foundations',
                'Greetings unit',
                'Ordered lesson 1',
                'Ordered lesson 2',
                'Ordered lesson 3',
                'Travel unit',
                'At the airport',
                'id="curriculum-china"',
                'Chinese foundations',
                'Chinese introductions',
                '你好',
            ], false)
            ->assertSee('1 หลักสูตร · 2 หน่วยการเรียน · 5 บทเรียน')
            ->assertSee('1 หลักสูตร · 1 หน่วยการเรียน · 1 บทเรียน')
            ->assertSee('และอีก 1 บทเรียน')
            ->assertDontSee('Ordered lesson 4')
            ->assertDontSee('French course must stay out');
    }

    public function test_preview_escapes_titles_and_does_not_expose_lesson_content(): void
    {
        $language = Language::factory()->create(['name' => '中文']);
        $course = Course::factory()->for($language)->create(['title' => '<script>alert("course")</script>']);
        $unit = Unit::factory()->for($course)->create(['title' => '<img src=x onerror=alert(1)>']);
        Lesson::factory()->for($unit)->create([
            'title' => '<b>Learn safely</b>',
            'content' => 'Private lesson explanation and answer: secret-answer-123',
        ]);

        $this->blade('<x-registration-curriculum />')
            ->assertSee(e($course->title), false)
            ->assertSee(e($unit->title), false)
            ->assertSee('&lt;b&gt;Learn safely&lt;/b&gt;', false)
            ->assertDontSee('<script>alert("course")</script>', false)
            ->assertDontSee('<img src=x onerror=alert(1)>', false)
            ->assertDontSee('secret-answer-123');
    }

    public function test_courses_and_units_without_content_still_show_their_real_state(): void
    {
        $language = Language::factory()->create(['name' => 'English']);
        Course::factory()->for($language)->create(['title' => 'Course without units']);
        $course = Course::factory()->for($language)->create(['title' => 'Course with empty unit']);
        Unit::factory()->for($course)->create(['title' => 'Unit without lessons']);

        $this->blade('<x-registration-curriculum />')
            ->assertSee('Course without units')
            ->assertSee('ยังไม่มีหน่วยการเรียนในหลักสูตรนี้')
            ->assertSee('Unit without lessons')
            ->assertSee('ยังไม่มีบทเรียนในหน่วยนี้')
            ->assertSee('2 หลักสูตร · 1 หน่วยการเรียน · 0 บทเรียน');
    }
}
