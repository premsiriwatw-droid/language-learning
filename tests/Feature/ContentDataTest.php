<?php

namespace Tests\Feature;

use App\Models\Answer;
use App\Models\Exercise;
use App\Models\Question;
use App\Models\Vocabulary;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\Support\CreatesContentLesson;
use Tests\TestCase;

class ContentDataTest extends TestCase
{
    use LazilyRefreshDatabase;
    use CreatesContentLesson;

    public function test_clean_sqlite_migrations_create_the_content_schema(): void
    {
        $this->assertSame('sqlite', DB::connection()->getDriverName());
        $this->assertSame(':memory:', DB::connection()->getDatabaseName());

        $tables = [
            'vocabularies' => [
                'id',
                'lesson_id',
                'word',
                'pinyin',
                'meaning',
                'example_sentence',
                'example_pinyin',
                'example_meaning',
                'created_at',
                'updated_at',
            ],
            'exercises' => [
                'id',
                'lesson_id',
                'type',
                'title',
                'created_at',
                'updated_at',
            ],
            'questions' => [
                'id',
                'exercise_id',
                'question',
                'explanation',
                'audio_path',
                'image_path',
                'created_at',
                'updated_at',
            ],
            'answers' => [
                'id',
                'question_id',
                'answer',
                'is_correct',
                'created_at',
                'updated_at',
            ],
        ];

        foreach ($tables as $table => $columns) {
            $this->assertEqualsCanonicalizing(
                $columns,
                Schema::getColumnListing($table)
            );
        }

        foreach ([
            'vocabularies' => 'lesson_id',
            'exercises' => 'lesson_id',
            'questions' => 'exercise_id',
            'answers' => 'question_id',
        ] as $table => $column) {
            $this->assertTrue(
                Schema::hasIndex($table, [$column])
            );
        }

        foreach ([
            'vocabularies' => ['lesson_id', 'lessons'],
            'exercises' => ['lesson_id', 'lessons'],
            'questions' => ['exercise_id', 'exercises'],
            'answers' => ['question_id', 'questions'],
        ] as $table => [$column, $parent]) {
            $foreignKeys = Schema::getForeignKeys($table);

            $matchingForeignKey = collect($foreignKeys)->first(
                fn (array $foreignKey) =>
                    $foreignKey['columns'] === [$column]
                    && $foreignKey['foreign_table'] === $parent
            );

            $this->assertNotNull(
                $matchingForeignKey,
                "Expected foreign key {$table}.{$column} -> {$parent}.id"
            );
        }
    }

    public function test_vocabulary_persists_chinese_pinyin_and_thai_content(): void
    {
        $lesson = $this->createContentLesson();

        $attributes = [
            'lesson_id' => $lesson->id,
            'word' => '你好',
            'pinyin' => 'nǐ hǎo',
            'meaning' => 'สวัสดี',
            'example_sentence' => '你好，我叫 Tom。',
            'example_pinyin' => 'nǐ hǎo, wǒ jiào Tom.',
            'example_meaning' => 'สวัสดี ฉันชื่อ Tom',
        ];

        $vocabulary = Vocabulary::create($attributes)->fresh();

        foreach ($attributes as $column => $value) {
            $this->assertSame($value, $vocabulary->{$column});
        }
    }

    public function test_optional_content_fields_can_be_omitted(): void
    {
        $lesson = $this->createContentLesson();

        $vocabulary = Vocabulary::create([
            'lesson_id' => $lesson->id,
            'word' => '你好',
            'meaning' => 'สวัสดี',
        ])->fresh();

        $exercise = Exercise::factory()->create([
            'lesson_id' => $lesson->id,
        ]);

        $question = Question::create([
            'exercise_id' => $exercise->id,
            'question' => 'คำทักทายคืออะไร?',
        ])->fresh();

        foreach ([
            'pinyin',
            'example_sentence',
            'example_pinyin',
            'example_meaning',
        ] as $column) {
            $this->assertNull($vocabulary->{$column});
        }

        $this->assertNull($question->explanation);
        $this->assertNull($question->audio_path);
        $this->assertNull($question->image_path);
    }

    #[DataProvider('exerciseTypes')]
    public function test_exercise_types_are_stored_as_strings(string $type): void
    {
        $lesson = $this->createContentLesson();

        $exercise = Exercise::create([
            'lesson_id' => $lesson->id,
            'type' => $type,
            'title' => 'Greetings Practice',
        ])->fresh();

        $this->assertSame($type, $exercise->type);
        $this->assertSame('Greetings Practice', $exercise->title);
        $this->assertSame($lesson->id, $exercise->lesson_id);
    }

    public static function exerciseTypes(): array
    {
        return [
            'multiple choice' => ['multiple_choice'],
            'fill blank' => ['fill_blank'],
            'translation' => ['translation'],
            'arrange words' => ['arrange_words'],
            'listening' => ['listening'],
            'image choice' => ['image_choice'],
            'custom type' => ['custom_type'],
        ];
    }

    public function test_question_bank_relationships_and_boolean_casts(): void
    {
        $lesson = $this->createContentLesson();

        $exercise = Exercise::factory()->create([
            'lesson_id' => $lesson->id,
            'type' => 'multiple_choice',
        ]);

        $question = $exercise->questions()->create([
            'question' => 'คำทักทายคืออะไร?',
            'explanation' => 'ใช้สำหรับการทักทาย',
        ]);

        $choices = [
            'ขอบคุณ' => false,
            'สวัสดี' => true,
            'ลาก่อน' => false,
            'ขอโทษ' => false,
        ];

        foreach ($choices as $text => $correct) {
            $question->answers()->create([
                'answer' => $text,
                'is_correct' => $correct,
            ]);
        }

        $otherExercise = Exercise::factory()->create([
            'lesson_id' => $lesson->id,
        ]);

        $otherQuestion = Question::factory()
            ->for($otherExercise)
            ->create();

        Answer::factory()
            ->for($otherQuestion)
            ->create();

        $exercise = $exercise->fresh('questions.answers');

        $this->assertCount(1, $exercise->questions);

        $this->assertTrue(
            $exercise->questions->first()->is($question)
        );

        $this->assertTrue(
            $question->fresh()->exercise->is($exercise)
        );

        $this->assertSame(
            'ใช้สำหรับการทักทาย',
            $question->fresh()->explanation
        );

        $this->assertCount(
            4,
            $exercise->questions->first()->answers
        );

        foreach ($exercise->questions->first()->answers as $answer) {
            $this->assertTrue(
                $answer->question->is($question)
            );

            $this->assertIsBool($answer->is_correct);

            $this->assertSame(
                $choices[$answer->answer],
                $answer->is_correct
            );
        }
    }

    public function test_arrange_words_content_can_be_stored(): void
    {
        $lesson = $this->createContentLesson();

        $exercise = Exercise::factory()->create([
            'lesson_id' => $lesson->id,
            'type' => 'arrange_words',
        ]);

        $question = $exercise->questions()->create([
            'question' => 'เรียงคำให้เป็นประโยค',
        ]);

        $answer = $question->answers()->create([
            'answer' => '我喜欢学习中文',
            'is_correct' => true,
        ]);

        $this->assertSame(
            'เรียงคำให้เป็นประโยค',
            $question->fresh()->question
        );

        $this->assertSame(
            '我喜欢学习中文',
            $answer->fresh()->answer
        );

        $this->assertTrue(
            $answer->fresh()->is_correct
        );
    }

    public function test_deleting_an_exercise_cascades_to_its_questions_and_answers(): void
    {
        $lesson = $this->createContentLesson();

        $exercise = Exercise::factory()->create([
            'lesson_id' => $lesson->id,
        ]);

        $question = Question::factory()
            ->for($exercise)
            ->create();

        $answer = Answer::factory()
            ->for($question)
            ->create();

        $exercise->delete();

        $this->assertDatabaseMissing(
            'exercises',
            ['id' => $exercise->id]
        );

        $this->assertDatabaseMissing(
            'questions',
            ['id' => $question->id]
        );

        $this->assertDatabaseMissing(
            'answers',
            ['id' => $answer->id]
        );
    }

    public function test_deleting_a_question_cascades_only_to_its_answers(): void
    {
        $lesson = $this->createContentLesson();

        $exercise = Exercise::factory()->create([
            'lesson_id' => $lesson->id,
        ]);

        $question = Question::factory()
            ->for($exercise)
            ->create();

        $answer = Answer::factory()
            ->for($question)
            ->create();

        $question->delete();

        $this->assertDatabaseMissing(
            'questions',
            ['id' => $question->id]
        );

        $this->assertDatabaseMissing(
            'answers',
            ['id' => $answer->id]
        );

        $this->assertDatabaseHas(
            'exercises',
            ['id' => $exercise->id]
        );
    }

    public function test_questions_require_an_existing_exercise(): void
    {
        $this->expectException(QueryException::class);

        Question::create([
            'exercise_id' => 999,
            'question' => 'คำถามทดสอบ',
        ]);
    }

    public function test_answers_require_an_existing_question(): void
    {
        $this->expectException(QueryException::class);

        Answer::create([
            'question_id' => 999,
            'answer' => 'คำตอบทดสอบ',
            'is_correct' => true,
        ]);
    }
}