<?php

namespace Tests\Feature;

use App\Models\Answer;
use App\Models\Exercise;
use App\Models\Question;
use App\Models\Vocabulary;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\DB;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\Support\CreatesContentLesson;
use Tests\TestCase;

class ContentIntegrationTest extends TestCase
{
    use CreatesContentLesson;
    use LazilyRefreshDatabase;

    public function test_lesson_relationships_and_explicit_factory_parents(): void
    {
        $lesson = $this->createContentLesson();
        $vocabulary = Vocabulary::factory()->create(['lesson_id' => $lesson->id]);
        $exercise = Exercise::factory()->for($lesson)->create();
        $otherLesson = $this->createContentLesson();
        $otherVocabulary = Vocabulary::factory()->for($otherLesson)->create();
        $otherExercise = Exercise::factory()->create(['lesson_id' => $otherLesson->id]);

        $this->assertTrue($vocabulary->fresh()->lesson->is($lesson));
        $this->assertTrue($exercise->fresh()->lesson->is($lesson));
        $this->assertSame([$vocabulary->id], $lesson->vocabularies->modelKeys());
        $this->assertSame([$exercise->id], $lesson->exercises->modelKeys());
        $this->assertNotSame($lesson->id, $otherVocabulary->lesson_id);
        $this->assertNotSame($lesson->id, $otherExercise->lesson_id);
        $this->assertDatabaseCount('lessons', 2);
        $this->assertDatabaseCount('units', 2);
        $this->assertDatabaseCount('courses', 2);
        $this->assertDatabaseCount('languages', 2);
    }

    #[DataProvider('contentModels')]
    public function test_content_rejects_invalid_lesson_ids(string $model): void
    {
        $this->expectException(QueryException::class);

        $model::factory()->create(['lesson_id' => 999]);
    }

    public static function contentModels(): array
    {
        return [[Vocabulary::class], [Exercise::class]];
    }

    public function test_deleting_a_lesson_cascades_only_to_its_content(): void
    {
        $lesson = $this->createContentLesson();
        $vocabulary = Vocabulary::factory()->for($lesson)->create();
        $exercise = Exercise::factory()->for($lesson)->has(
            Question::factory()->has(Answer::factory())
        )->create();
        $question = $exercise->questions->sole();
        $answer = $question->answers->sole();
        $otherLesson = $this->createContentLesson();
        $otherAnswer = Answer::factory()->for(
            Question::factory()->for(Exercise::factory()->for($otherLesson))
        )->create();
        $otherVocabulary = Vocabulary::factory()->for($otherLesson)->create();

        DB::table('lessons')->where('id', $lesson->id)->delete();

        foreach ([$lesson, $vocabulary, $exercise, $question, $answer] as $deleted) {
            $this->assertModelMissing($deleted);
        }

        foreach ([$lesson->unit, $otherVocabulary, $otherVocabulary->lesson, $otherAnswer, $otherAnswer->question, $otherAnswer->question->exercise, $otherAnswer->question->exercise->lesson] as $remaining) {
            $this->assertModelExists($remaining);
        }
    }

    #[DataProvider('mediaQuestions')]
    public function test_questions_store_optional_media(string $type, ?string $audioPath, ?string $imagePath): void
    {
        $exercise = Exercise::factory()->for($this->createContentLesson())->create(['type' => $type]);
        $question = $exercise->questions()->create([
            'question' => 'Example prompt',
            'audio_path' => $audioPath,
            'image_path' => $imagePath,
        ]);
        $answer = $question->answers()->create(['answer' => 'Example answer', 'is_correct' => 1]);
        $question = $question->fresh();

        $this->assertSame($audioPath, $question->audio_path);
        $this->assertSame($imagePath, $question->image_path);
        $this->assertSame($type, $question->exercise->type);
        $this->assertTrue($question->exercise->is($exercise));
        $this->assertTrue($question->answers->sole()->is($answer));
        $this->assertTrue($answer->fresh()->is_correct);
    }

    public static function mediaQuestions(): array
    {
        return [
            'fill blank' => ['fill_blank', null, null],
            'multiple choice' => ['multiple_choice', null, null],
            'listening' => ['listening', 'questions/audio/greeting.mp3', null],
            'image choice' => ['image_choice', null, 'questions/images/apple.jpg'],
        ];
    }

    public function test_question_factory_defaults_to_no_media_and_accepts_overrides(): void
    {
        $exercise = Exercise::factory()->for($this->createContentLesson())->create();
        $question = Question::factory()->for($exercise)->create()->fresh();
        $this->assertNull($question->audio_path);
        $this->assertNull($question->image_path);

        $withMedia = Question::factory()->for($exercise)->create([
            'audio_path' => 'questions/audio/example.mp3',
            'image_path' => 'questions/images/example.jpg',
        ])->fresh();

        $this->assertSame('questions/audio/example.mp3', $withMedia->audio_path);
        $this->assertSame('questions/images/example.jpg', $withMedia->image_path);
    }
}
