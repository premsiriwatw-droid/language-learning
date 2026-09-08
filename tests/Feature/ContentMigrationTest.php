<?php

namespace Tests\Feature;

use App\Models\Answer;
use App\Models\Exercise;
use App\Models\Question;
use App\Models\Vocabulary;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\Support\CreatesContentLesson;
use Tests\TestCase;

class ContentMigrationTest extends TestCase
{
    use CreatesContentLesson;
    use DatabaseMigrations;

    public function test_integration_migrations_roll_back_and_reapply_without_losing_content(): void
    {
        $answer = Answer::factory()->for(
            Question::factory()->for(Exercise::factory()->for($this->createContentLesson()))
        )->create();
        $question = $answer->question;
        $exercise = $question->exercise;
        $vocabulary = Vocabulary::factory()->create(['lesson_id' => $exercise->lesson_id]);

        $this->artisan('migrate:rollback', ['--step' => 2, '--force' => true])->assertSuccessful();

        $this->assertSame([], Schema::getForeignKeys('vocabularies'));
        $this->assertSame([], Schema::getForeignKeys('exercises'));
        $this->assertFalse(Schema::hasColumn('questions', 'audio_path'));
        $this->assertFalse(Schema::hasColumn('questions', 'image_path'));

        $this->artisan('migrate', ['--force' => true])->assertSuccessful();

        foreach ([$answer, $question, $exercise, $vocabulary] as $model) {
            $this->assertModelExists($model);
        }

        $this->assertCount(1, Schema::getForeignKeys('vocabularies'));
        $this->assertCount(1, Schema::getForeignKeys('exercises'));
        $this->assertNull($question->fresh()->audio_path);
        $this->assertNull($question->fresh()->image_path);
        $this->assertSame([], DB::select('PRAGMA foreign_key_check'));
    }
}
