<?php

namespace Tests\Feature;

use App\Models\Answer;
use App\Models\Exercise;
use App\Models\Lesson;
use App\Models\Question;
use App\Models\User;
use App\Models\Vocabulary;
use Database\Seeders\ChineseContentSeeder;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\Support\CreatesContentLesson;
use Tests\TestCase;

class ChineseContentSeederTest extends TestCase
{
    use CreatesContentLesson;
    use LazilyRefreshDatabase;

    public function test_content_is_attached_to_existing_lessons_with_valid_answers_and_media(): void
    {
        $lessons = $this->createChineseLessons();

        $this->seed(ChineseContentSeeder::class);

        foreach ($lessons as $title => $lesson) {
            $this->assertCount($title === 'Numbers' ? 8 : 6, $lesson->vocabularies);

            foreach ($lesson->vocabularies as $word) {
                $this->assertTrue($word->lesson->is($lesson));
                $this->assertNotEmpty($word->pinyin);
                $this->assertNotEmpty($word->meaning);
            }

            $this->assertEqualsCanonicalizing(
                ['multiple_choice', 'fill_blank', 'listening', 'image_choice'],
                $lesson->exercises->pluck('type')->all()
            );

            foreach ($lesson->exercises as $exercise) {
                $this->assertTrue($exercise->lesson->is($lesson));

                $question = $exercise->questions->sole();

                $this->assertCount(
                    1,
                    $question->answers->where('is_correct', true)
                );

                $this->assertCount(
                    in_array($exercise->type, ['multiple_choice', 'image_choice']) ? 4 : 1,
                    $question->answers
                );

                foreach ($question->answers as $answer) {
                    $this->assertIsBool($answer->is_correct);
                    $this->assertTrue($answer->question->is($question));
                }

                if ($exercise->type === 'listening') {
                    $this->assertStringStartsWith(
                        'audio/chinese/',
                        $question->audio_path
                    );

                    $this->assertStringEndsWith(
                        '.mp3',
                        $question->audio_path
                    );

                    $this->assertNull($question->image_path);
                } elseif ($exercise->type === 'image_choice') {
                    $this->assertStringStartsWith(
                        'images/chinese/',
                        $question->image_path
                    );

                    $this->assertStringEndsWith(
                        '.jpg',
                        $question->image_path
                    );

                    $this->assertNull($question->audio_path);
                } else {
                    $this->assertNull($question->audio_path);
                    $this->assertNull($question->image_path);
                }
            }
        }

        $this->assertDatabaseHas('vocabularies', [
            'word' => '你好',
            'pinyin' => 'nǐ hǎo',
            'meaning' => 'สวัสดี',
        ]);

        $this->assertDatabaseCount('vocabularies', 20);
        $this->assertDatabaseCount('exercises', 12);
        $this->assertDatabaseCount('questions', 12);
        $this->assertDatabaseCount('answers', 30);

        $this->assertSame([], DB::select('PRAGMA foreign_key_check'));
    }

    public function test_rerunning_preserves_ids_and_unrelated_data_without_creating_structure(): void
    {
        $lessons = $this->createChineseLessons();

        $unrelatedLesson = $this->createContentLesson();
        $unrelatedLesson->forceFill([
            'title' => 'Greetings',
        ])->save();

        $unrelatedWord = Vocabulary::factory()
            ->for($unrelatedLesson)
            ->create();

        $unrelatedExercise = Exercise::factory()
            ->for($lessons['Greetings'])
            ->has(
                Question::factory()->has(
                    Answer::factory()
                )
            )
            ->create([
                'title' => 'Other team content',
            ]);

        $user = User::factory()->create();

        $structure = $this->snapshot([
            'languages',
            'courses',
            'units',
            'lessons',
            'users',
        ]);

        $this->seed(ChineseContentSeeder::class);

        $content = $this->snapshot([
            'vocabularies',
            'exercises',
            'questions',
            'answers',
        ]);

        $this->seed(ChineseContentSeeder::class);

        $this->assertSame(
            $content,
            $this->snapshot([
                'vocabularies',
                'exercises',
                'questions',
                'answers',
            ])
        );

        $this->assertSame(
            $structure,
            $this->snapshot([
                'languages',
                'courses',
                'units',
                'lessons',
                'users',
            ])
        );

        $this->assertEquals(
            $unrelatedWord->getAttributes(),
            $unrelatedWord->fresh()->getAttributes()
        );

        $this->assertEquals(
            $unrelatedExercise->getAttributes(),
            $unrelatedExercise->fresh()->getAttributes()
        );

        $this->assertModelExists($user);
        $this->assertCount(1, $unrelatedLesson->vocabularies);
        $this->assertCount(0, $unrelatedLesson->exercises);
    }

    public function test_database_seeder_runs_on_fresh_sqlite_with_learning_structure_and_chinese_content(): void
    {
        $this->assertSame(
            'sqlite',
            DB::connection()->getDriverName()
        );

        $this->assertSame(
            ':memory:',
            DB::connection()->getDatabaseName()
        );

        $this->seed(DatabaseSeeder::class);

        $this->assertDatabaseHas('languages', [
            'name' => 'Chinese',
        ]);

        $this->assertDatabaseHas('courses', [
            'title' => 'Chinese Beginner',
        ]);

        $this->assertDatabaseHas('units', [
            'title' => 'Unit 1: Basics',
        ]);

        $this->assertDatabaseHas('lessons', [
            'title' => 'Greetings',
        ]);

        $this->assertDatabaseHas('lessons', [
            'title' => 'Self Introduction',
        ]);

        $this->assertDatabaseHas('lessons', [
            'title' => 'Numbers',
        ]);

        $this->assertDatabaseCount('languages', 1);
        $this->assertDatabaseCount('courses', 1);
        $this->assertDatabaseCount('units', 1);
        $this->assertDatabaseCount('lessons', 3);

        $this->assertDatabaseCount('vocabularies', 20);
        $this->assertDatabaseCount('exercises', 12);
        $this->assertDatabaseCount('questions', 12);
        $this->assertDatabaseCount('answers', 30);

        $this->assertDatabaseHas('users', [
            'email' => 'test@example.com',
        ]);

        $this->assertSame([], DB::select('PRAGMA foreign_key_check'));
    }

    public function test_missing_lessons_do_not_prevent_seeding_an_existing_match(): void
    {
        $lesson = $this->createContentLesson();

        $lesson->unit->course->language->update([
            'name' => 'Chinese',
        ]);

        $lesson->forceFill([
            'title' => 'Greetings',
        ])->save();

        $this->seed(ChineseContentSeeder::class);

        $this->assertDatabaseCount('lessons', 1);
        $this->assertDatabaseCount('vocabularies', 6);
        $this->assertDatabaseCount('exercises', 4);
    }

    public function test_ambiguous_chinese_lesson_titles_are_skipped_instead_of_choosing_a_parent(): void
    {
        $lessons = $this->createChineseLessons();

        $duplicate = $this->createContentLesson();

        $duplicate->unit->course->language->update([
            'name' => 'Chinese',
        ]);

        $duplicate->forceFill([
            'title' => 'Greetings',
        ])->save();

        $this->seed(ChineseContentSeeder::class);

        $this->assertCount(
            0,
            $lessons['Greetings']->vocabularies
        );

        $this->assertCount(
            0,
            $duplicate->vocabularies
        );

        $this->assertCount(
            0,
            $lessons['Greetings']->exercises
        );

        $this->assertCount(
            0,
            $duplicate->exercises
        );

        $this->assertDatabaseCount('vocabularies', 14);
        $this->assertDatabaseCount('exercises', 8);
    }

    /** @return array<string, Lesson> */
    private function createChineseLessons(): array
    {
        $greetings = $this->createContentLesson();

        $greetings->unit->course->language->update([
            'name' => 'Chinese',
        ]);

        $greetings->forceFill([
            'title' => 'Greetings',
        ])->save();

        return [
            'Greetings' => $greetings,

            'Self Introduction' => Lesson::forceCreate([
                'unit_id' => $greetings->unit_id,
                'title' => 'Self Introduction',
            ]),

            'Numbers' => Lesson::forceCreate([
                'unit_id' => $greetings->unit_id,
                'title' => 'Numbers',
            ]),
        ];
    }

    private function snapshot(array $tables): array
    {
        $snapshot = [];

        foreach ($tables as $table) {
            $snapshot[$table] = DB::table($table)
                ->orderBy('id')
                ->get()
                ->map(fn ($row) => (array) $row)
                ->all();
        }

        return $snapshot;
    }
}