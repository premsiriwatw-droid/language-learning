<?php

namespace Tests\Feature;

use App\Models\Exercise;
use App\Models\Question;
use App\Models\Vocabulary;
use App\Services\Content\QuestionVocabularyImporter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\CreatesContentLesson;
use Tests\TestCase;

class EnglishQuestionVocabularyMappingTest extends TestCase
{
    use RefreshDatabase;
    use CreatesContentLesson;

    private function fixture(): array
    {
        $lesson = $this->createContentLesson();
        $lesson->unit->course->language->update(['name' => 'English']);
        $hello = Vocabulary::factory()->create(['lesson_id' => $lesson->id, 'word' => 'hello']);
        $water = Vocabulary::factory()->create(['lesson_id' => $lesson->id, 'word' => 'water']);
        $exercise = Exercise::factory()->create(['lesson_id' => $lesson->id, 'type' => 'multiple_choice']);
        $question = Question::factory()->for($exercise)->create(['question' => 'Choose a greeting.', 'vocabulary_mode' => 'pending']);
        $item = ['question' => $question->question, 'answers' => [['Hello', true], ['water', false]]];
        foreach ($item['answers'] as [$answer, $correct]) {
            $question->answers()->create(['answer' => $answer, 'is_correct' => $correct]);
        }
        return [$question, $hello, $water, $item];
    }

    public function test_english_auto_mapping_is_saved_and_idempotent(): void
    {
        [$question, $hello, $water, $item] = $this->fixture();
        $importer = app(QuestionVocabularyImporter::class);
        $this->assertTrue($importer->apply($question, $item));
        $this->assertSame('after_vocabulary', $question->fresh()->vocabulary_mode);
        $this->assertSame([$hello->id], $question->vocabularies()->pluck('vocabularies.id')->all());
        $this->assertFalse($importer->apply($question, $item));
        $this->assertDatabaseCount('question_vocabulary', 1);
    }

    public function test_admin_mapping_is_preserved(): void
    {
        [$question, $hello, $water, $item] = $this->fixture();
        $question->vocabularies()->sync([$water->id]);
        $question->update(['vocabulary_mode' => 'after_vocabulary']);
        $this->assertFalse(app(QuestionVocabularyImporter::class)->apply($question, $item));
        $this->assertSame([$water->id], $question->vocabularies()->pluck('vocabularies.id')->all());
    }

    public function test_lesson_end_is_preserved(): void
    {
        [$question, $hello, $water, $item] = $this->fixture();
        $question->update(['vocabulary_mode' => 'lesson_end']);
        $this->assertFalse(app(QuestionVocabularyImporter::class)->apply($question, $item));
        $this->assertSame('lesson_end', $question->fresh()->vocabulary_mode);
        $this->assertDatabaseCount('question_vocabulary', 0);
    }

    public function test_unmatched_content_stays_pending(): void
    {
        [$question] = $this->fixture();
        $item = ['question' => 'Choose.', 'answers' => [['Unknown', true], ['Hello', false]]];
        $this->assertFalse(app(QuestionVocabularyImporter::class)->apply($question, $item));
        $this->assertSame('pending', $question->fresh()->vocabulary_mode);
        $this->assertDatabaseCount('question_vocabulary', 0);
    }

    public function test_explicit_metadata_takes_priority_over_detection(): void
    {
        [$question, $hello, $water, $item] = $this->fixture();
        $item['vocabulary_mode'] = 'after_vocabulary';
        $item['required_vocabulary_words'] = ['water'];
        $this->assertTrue(app(QuestionVocabularyImporter::class)->apply($question, $item));
        $this->assertSame([$water->id], $question->vocabularies()->pluck('vocabularies.id')->all());
    }

    public function test_review_weather_image_imports_as_lesson_end_without_unrelated_prerequisites(): void
    {
        $content = require database_path('seeders/data/english.php');
        $review = $content['Review & Daily Conversation'];
        $items = $review['exercises']['image_choice'];
        $items = isset($items['question']) ? [$items] : $items;
        $item = $items[1];

        $this->assertSame('lesson_end', $item['vocabulary_mode']);
        $this->assertSame([], $item['required_vocabulary_words']);

        $lesson = $this->createContentLesson();
        $lesson->update(['title' => 'Review & Daily Conversation']);
        $lesson->unit->course->language->update(['name' => 'English']);

        foreach ($review['vocabulary'] as $vocabulary) {
            Vocabulary::factory()->create(['lesson_id' => $lesson->id, 'word' => $vocabulary[0]]);
        }

        $exercise = Exercise::factory()->create(['lesson_id' => $lesson->id, 'type' => 'image_choice']);
        $question = Question::factory()->for($exercise)->create(['question' => $item['question'], 'vocabulary_mode' => 'pending']);
        $importer = app(QuestionVocabularyImporter::class);

        $this->assertTrue($importer->apply($question, $item));
        $this->assertSame('lesson_end', $question->fresh()->vocabulary_mode);
        $this->assertSame(0, $question->vocabularies()->count());
        $this->assertFalse($importer->apply($question, $item));
        $this->assertDatabaseCount('question_vocabulary', 0);
    }
}
