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
use Tests\TestCase;

class ContentDataTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_clean_sqlite_migrations_create_the_content_schema(): void
    {
        $this->assertSame('sqlite', DB::connection()->getDriverName());
        $this->assertSame(':memory:', DB::connection()->getDatabaseName());

        $tables = [
            'vocabularies' => ['id', 'lesson_id', 'word', 'pinyin', 'meaning', 'example_sentence', 'example_pinyin', 'example_meaning', 'created_at', 'updated_at'],
            'exercises' => ['id', 'lesson_id', 'type', 'title', 'created_at', 'updated_at'],
            'questions' => ['id', 'exercise_id', 'question', 'explanation', 'created_at', 'updated_at'],
            'answers' => ['id', 'question_id', 'answer', 'is_correct', 'created_at', 'updated_at'],
        ];

        foreach ($tables as $table => $columns) {
            $this->assertEqualsCanonicalizing($columns, Schema::getColumnListing($table));
        }

        foreach (['vocabularies' => 'lesson_id', 'exercises' => 'lesson_id', 'questions' => 'exercise_id', 'answers' => 'question_id'] as $table => $column) {
            $this->assertTrue(Schema::hasIndex($table, [$column]));
        }

        $this->assertSame([], Schema::getForeignKeys('vocabularies'));
        $this->assertSame([], Schema::getForeignKeys('exercises'));

        foreach (['questions' => ['exercise_id', 'exercises'], 'answers' => ['question_id', 'questions']] as $table => [$column, $parent]) {
            $foreignKeys = Schema::getForeignKeys($table);

            $this->assertCount(1, $foreignKeys);
            $this->assertSame([$column], $foreignKeys[0]['columns']);
            $this->assertSame($parent, $foreignKeys[0]['foreign_table']);
            $this->assertSame(['id'], $foreignKeys[0]['foreign_columns']);
            $this->assertSame('cascade', strtolower($foreignKeys[0]['on_delete']));
        }
    }

    public function test_vocabulary_persists_chinese_pinyin_and_thai_content(): void
    {
        $attributes = [
            'lesson_id' => 42,
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
        $vocabulary = Vocabulary::create([
            'lesson_id' => 42,
            'word' => '你好',
            'meaning' => 'สวัสดี',
        ])->fresh();
        $question = Question::create([
            'exercise_id' => Exercise::factory()->create()->id,
            'question' => '你好 แปลว่าอะไร?',
        ])->fresh();

        foreach (['pinyin', 'example_sentence', 'example_pinyin', 'example_meaning'] as $column) {
            $this->assertNull($vocabulary->{$column});
        }

        $this->assertNull($question->explanation);
    }

    #[DataProvider('exerciseTypes')]
    public function test_exercise_types_are_stored_as_strings(string $type): void
    {
        $exercise = Exercise::create([
            'lesson_id' => 42,
            'type' => $type,
            'title' => 'Greetings Practice',
        ])->fresh();

        $this->assertSame($type, $exercise->type);
        $this->assertSame('Greetings Practice', $exercise->title);
        $this->assertSame(42, $exercise->lesson_id);
    }

    public static function exerciseTypes(): array
    {
        return [
            'multiple choice' => ['multiple_choice'],
            'fill blank' => ['fill_blank'],
            'translation' => ['translation'],
            'arrange words' => ['arrange_words'],
        ];
    }

    public function test_question_bank_relationships_and_boolean_casts(): void
    {
        $exercise = Exercise::factory()->create(['type' => 'multiple_choice']);
        $question = $exercise->questions()->create([
            'question' => '你好 แปลว่าอะไร?',
            'explanation' => '你好 ใช้กล่าวทักทาย',
        ]);
        $choices = ['ขอบคุณ' => false, 'สวัสดี' => true, 'ลาก่อน' => false, 'ขอโทษ' => false];

        foreach ($choices as $text => $correct) {
            $question->answers()->create(['answer' => $text, 'is_correct' => $correct]);
        }

        $otherQuestion = Question::factory()->create();
        Answer::factory()->for($otherQuestion)->create();

        $exercise = $exercise->fresh('questions.answers');
        $this->assertCount(1, $exercise->questions);
        $this->assertTrue($exercise->questions->first()->is($question));
        $this->assertTrue($question->fresh()->exercise->is($exercise));
        $this->assertSame('你好 ใช้กล่าวทักทาย', $question->fresh()->explanation);
        $this->assertCount(4, $exercise->questions->first()->answers);

        foreach ($exercise->questions->first()->answers as $answer) {
            $this->assertTrue($answer->question->is($question));
            $this->assertIsBool($answer->is_correct);
            $this->assertSame($choices[$answer->answer], $answer->is_correct);
        }
    }

    public function test_arrange_words_content_can_be_stored(): void
    {
        $exercise = Exercise::factory()->create(['type' => 'arrange_words']);
        $question = $exercise->questions()->create([
            'question' => 'เรียงคำให้ถูกต้อง: 我 / 学生 / 是',
        ]);
        $answer = $question->answers()->create([
            'answer' => '我是学生',
            'is_correct' => true,
        ])->fresh();

        $this->assertSame('เรียงคำให้ถูกต้อง: 我 / 学生 / 是', $question->fresh()->question);
        $this->assertSame('我是学生', $answer->answer);
        $this->assertTrue($answer->is_correct);
    }

    public function test_deleting_an_exercise_cascades_to_its_questions_and_answers(): void
    {
        $exercise = Exercise::factory()->has(
            Question::factory()->count(2)->has(Answer::factory()->count(2))
        )->create();
        $questionIds = $exercise->questions()->pluck('id');
        $answerIds = Answer::whereIn('question_id', $questionIds)->pluck('id');
        $unrelatedAnswer = Answer::factory()->create();

        DB::table('exercises')->where('id', $exercise->id)->delete();

        $this->assertDatabaseMissing('exercises', ['id' => $exercise->id]);
        $this->assertSame(0, Question::whereIn('id', $questionIds)->count());
        $this->assertSame(0, Answer::whereIn('id', $answerIds)->count());
        $this->assertModelExists($unrelatedAnswer);
        $this->assertModelExists($unrelatedAnswer->question);
        $this->assertModelExists($unrelatedAnswer->question->exercise);
    }

    public function test_deleting_a_question_cascades_only_to_its_answers(): void
    {
        $exercise = Exercise::factory()->create();
        $question = Question::factory()->for($exercise)->has(Answer::factory()->count(2))->create();
        $sibling = Question::factory()->for($exercise)->has(Answer::factory())->create();
        $answerIds = $question->answers()->pluck('id');

        DB::table('questions')->where('id', $question->id)->delete();

        $this->assertDatabaseMissing('questions', ['id' => $question->id]);
        $this->assertSame(0, Answer::whereIn('id', $answerIds)->count());
        $this->assertModelExists($exercise);
        $this->assertModelExists($sibling);
        $this->assertModelExists($sibling->answers->first());
    }

    public function test_questions_require_an_existing_exercise(): void
    {
        $this->expectException(QueryException::class);

        Question::create(['exercise_id' => 999, 'question' => '你好 แปลว่าอะไร?']);
    }

    public function test_answers_require_an_existing_question(): void
    {
        $this->expectException(QueryException::class);

        Answer::create(['question_id' => 999, 'answer' => 'สวัสดี', 'is_correct' => true]);
    }
}
