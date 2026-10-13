<?php

namespace Tests\Feature;

use App\Models\Answer;
use App\Models\Course;
use App\Models\Exercise;
use App\Models\Language;
use App\Models\Lesson;
use App\Models\Question;
use App\Models\TeacherCourse;
use App\Models\Unit;
use App\Models\User;
use DOMDocument;
use DOMXPath;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class AuthLanguageExplorerTest extends TestCase
{
    use RefreshDatabase;

    #[DataProvider('pages')]
    public function test_each_language_has_its_own_outline_in_lesson_plan_order(string $route): void
    {
        $english = Language::factory()->create(['name' => 'English']);
        $englishCourse = Course::factory()->for($english)->create(['title' => 'English course outline']);
        $secondUnit = Unit::factory()->for($englishCourse)->create(['title' => 'English second unit', 'position' => 2]);
        $firstUnit = Unit::factory()->for($englishCourse)->create(['title' => 'English first unit', 'position' => 1]);
        Lesson::factory()->for($firstUnit)->create(['title' => 'English second lesson', 'position' => 2]);
        Lesson::factory()->for($firstUnit)->create(['title' => 'English first lesson', 'position' => 1]);
        Lesson::factory()->for($secondUnit)->create(['title' => 'English later lesson']);

        $chinese = Language::factory()->create(['name' => 'Chinese']);
        $chineseCourse = Course::factory()->for($chinese)->create(['title' => 'Chinese course outline']);
        $chineseUnit = Unit::factory()->for($chineseCourse)->create(['title' => 'Chinese first unit']);
        Lesson::factory()->for($chineseUnit)->create(['title' => '你好 Greetings']);

        $french = Language::factory()->create(['name' => 'French']);
        Course::factory()->for($french)->create(['title' => 'French course outside this preview']);

        $response = $this->get(route($route))->assertOk()
            ->assertSeeInOrder([
                'English course outline', 'English first unit', 'English first lesson',
                'English second lesson', 'English second unit', 'English later lesson',
            ])
            ->assertDontSee('French course outside this preview');

        $englishOutline = $this->outlineText($response->getContent(), 'england');
        $chineseOutline = $this->outlineText($response->getContent(), 'china');
        $this->assertStringContainsString('English course outline', $englishOutline);
        $this->assertStringContainsString('English first lesson', $englishOutline);
        $this->assertStringNotContainsString('Chinese course outline', $englishOutline);
        $this->assertStringContainsString('Chinese course outline', $chineseOutline);
        $this->assertStringContainsString('你好 Greetings', $chineseOutline);
        $this->assertStringNotContainsString('English course outline', $chineseOutline);
    }

    #[DataProvider('pages')]
    public function test_public_outline_escapes_titles_and_excludes_private_content(string $route): void
    {
        $language = Language::factory()->create(['name' => 'English']);
        $courseTitle = '<script>coursePreview()</script>';
        $unitTitle = '<img src=x onerror=unitPreview()>';
        $lessonTitle = '<svg onload=lessonPreview()>';
        $course = Course::factory()->for($language)->create(['title' => $courseTitle]);
        $unit = Unit::factory()->for($course)->create(['title' => $unitTitle]);
        $lesson = Lesson::factory()->for($unit)->create([
            'title' => $lessonTitle,
            'content' => 'Private lesson body marker',
        ]);
        $exercise = Exercise::factory()->for($lesson)->create(['title' => 'Private exercise marker']);
        $question = Question::factory()->for($exercise)->create(['question' => 'Private question marker']);
        Answer::factory()->for($question)->correct()->create(['answer' => 'Private answer marker']);

        $teacher = User::factory()->create(['name' => 'Private teacher name marker']);
        $teacherCourse = TeacherCourse::factory()->draft()->create([
            'teacher_id' => $teacher->id,
            'language_id' => $language->id,
            'title' => 'Private teacher course marker',
        ]);
        $teacherCourse->teacherLessons()->create([
            'title' => 'Private teacher lesson marker',
            'body' => 'Private teacher material marker',
        ]);

        $response = $this->get(route($route))->assertOk();

        foreach ([$courseTitle, $unitTitle, $lessonTitle] as $title) {
            $response->assertSee($title)->assertDontSee($title, false);
        }

        foreach ([
            'Private lesson body marker', 'Private exercise marker', 'Private question marker',
            'Private answer marker', 'Private teacher name marker', 'Private teacher course marker',
            'Private teacher lesson marker', 'Private teacher material marker',
        ] as $privateText) {
            $response->assertDontSee($privateText);
        }

        $this->assertGuest();
    }

    #[DataProvider('pages')]
    public function test_missing_languages_show_an_empty_outline_without_creating_data(string $route): void
    {
        $response = $this->get(route($route))->assertOk();

        foreach (['england', 'china'] as $scene) {
            $this->assertStringContainsString(
                'ยังไม่มีบทเรียนสำหรับภาษานี้',
                $this->outlineText($response->getContent(), $scene),
            );
        }

        $this->assertDatabaseCount('languages', 0);
        $this->assertDatabaseCount('courses', 0);
        $this->assertDatabaseCount('units', 0);
        $this->assertDatabaseCount('lessons', 0);
    }

    /** @return array<string, array{string}> */
    public static function pages(): array
    {
        return ['login' => ['login'], 'register' => ['register']];
    }

    private function outlineText(string $html, string $scene): string
    {
        $document = new DOMDocument;
        $previous = libxml_use_internal_errors(true);

        try {
            $document->loadHTML('<?xml encoding="utf-8" ?>'.$html);
        } finally {
            libxml_clear_errors();
            libxml_use_internal_errors($previous);
        }

        $nodes = (new DOMXPath($document))->query('//*[@data-outline="'.$scene.'"]');
        $this->assertNotFalse($nodes);
        $this->assertCount(1, $nodes);

        return $nodes->item(0)->textContent;
    }
}
