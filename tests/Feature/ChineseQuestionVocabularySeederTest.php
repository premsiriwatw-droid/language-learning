<?php

namespace Tests\Feature;

use App\Models\Lesson;
use App\Models\Question;
use App\Services\Content\QuestionVocabularyImporter;
use Database\Seeders\ChineseContentSeeder;
use Database\Seeders\ChineseQuestionVocabularySeeder;
use Database\Seeders\LearningStructureSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ChineseQuestionVocabularySeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_backfill_supports_content_without_metadata_and_is_idempotent(): void
    {
        $this->seed(LearningStructureSeeder::class);
        $this->seed(ChineseContentSeeder::class);

        // จำลองฐานข้อมูลเดิมที่ยังไม่ได้จัดความสัมพันธ์
        DB::table('question_vocabulary')->delete();
        DB::table('questions')->update([
            'vocabulary_mode' => 'pending',
        ]);

        $counts = $this->contentCounts();

        $this->seed(ChineseQuestionVocabularySeeder::class);

        foreach (
            require database_path('seeders/data/chinese.php')
            as $title => $content
        ) {
            $lesson = Lesson::where('title', $title)
                ->whereHas(
                    'unit.course.language',
                    fn ($query) => $query->where('name', 'Chinese')
                )
                ->sole();

            foreach ($content['exercises'] as $type => $items) {
                if (isset($items['question'])) {
                    $items = [$items];
                }

                $exercise = $lesson->exercises()
                    ->where('type', $type)
                    ->where('title', 'Chinese MVP: '.$type)
                    ->sole();

                foreach ($items as $item) {
                    $question = $exercise->questions()
                        ->where('question', $item['question'])
                        ->sole();

                    $hasMetadata = array_key_exists(
                        'vocabulary_mode',
                        $item
                    );

                    if (!$hasMetadata) {
                        $resolved = app(\App\Services\Content\QuestionVocabularyResolver::class)->resolve(
                            $item,
                            $lesson->vocabularies()->orderBy('id')->pluck('word')->all()
                        );

                        $this->assertSame(
                            $resolved === [] ? 'pending' : 'after_vocabulary',
                            $question->vocabulary_mode
                        );
                        $this->assertEqualsCanonicalizing(
                            $resolved,
                            $question->vocabularies->pluck('word')->all()
                        );
                        foreach ($question->vocabularies as $word) {
                            $this->assertSame((int) $lesson->id, (int) $word->lesson_id);
                        }
                        continue;
                    }

                    $this->assertSame(
                        $item['vocabulary_mode'],
                        $question->vocabulary_mode
                    );

                    $this->assertEqualsCanonicalizing(
                        $item['required_vocabulary_words'],
                        $question->vocabularies->pluck('word')->all()
                    );

                    foreach ($question->vocabularies as $word) {
                        $this->assertSame(
                            (int) $lesson->id,
                            (int) $word->lesson_id
                        );
                    }
                }
            }
        }

        $links = $this->links();
        $modes = $this->modes();

        $this->seed(ChineseQuestionVocabularySeeder::class);

        $this->assertSame($links, $this->links());
        $this->assertSame($modes, $this->modes());
        $this->assertSame($counts, $this->contentCounts());
    }

    public function test_reseeding_preserves_admin_lesson_end_selection(): void
    {
        $this->seed(LearningStructureSeeder::class);
        $this->seed(ChineseContentSeeder::class);

        $question = Question::orderBy('id')->firstOrFail();

        // จำลองการจัดเป็นท้ายบทผ่าน Admin
        $question->vocabularies()->detach();
        $question->update([
            'vocabulary_mode' => 'lesson_end',
        ]);

        $this->seed(ChineseQuestionVocabularySeeder::class);
        $this->seed(ChineseContentSeeder::class);

        $question->refresh();

        $this->assertSame(
            'lesson_end',
            $question->vocabulary_mode
        );

        $this->assertCount(0, $question->vocabularies);
    }

    public function test_reseeding_preserves_admin_vocabulary_selection(): void
    {
        $this->seed(LearningStructureSeeder::class);
        $this->seed(ChineseContentSeeder::class);

        $question = Question::orderBy('id')->firstOrFail();

        $word = $question->exercise->lesson
            ->vocabularies()
            ->orderBy('id')
            ->firstOrFail();

        // จำลองการเลือกศัพท์ผ่าน Admin
        $question->vocabularies()->sync([$word->id]);

        $question->update([
            'vocabulary_mode' => 'after_vocabulary',
        ]);

        $this->seed(ChineseQuestionVocabularySeeder::class);
        $this->seed(ChineseContentSeeder::class);

        $question->refresh();

        $this->assertSame(
            'after_vocabulary',
            $question->vocabulary_mode
        );

        $this->assertSame(
            [$word->id],
            $question->vocabularies()
                ->pluck('vocabularies.id')
                ->all()
        );
    }

    public function test_importer_accepts_explicit_metadata_without_editing_source_data(): void
    {
        $this->seed(LearningStructureSeeder::class);
        $this->seed(ChineseContentSeeder::class);

        $question = Question::orderBy('id')->firstOrFail();

        $word = $question->exercise->lesson
            ->vocabularies()
            ->orderBy('id')
            ->firstOrFail();

        $question->vocabularies()->detach();

        $question->update([
            'vocabulary_mode' => 'pending',
        ]);

        $item = [
            'vocabulary_mode' => 'after_vocabulary',
            'required_vocabulary_words' => [$word->word],
        ];

        $importer = app(QuestionVocabularyImporter::class);

        $this->assertTrue($importer->apply($question, $item));

        $question->refresh();

        $this->assertSame(
            'after_vocabulary',
            $question->vocabulary_mode
        );

        $this->assertSame(
            [$word->id],
            $question->vocabularies()
                ->pluck('vocabularies.id')
                ->all()
        );

        // รันซ้ำไม่เปลี่ยน mapping ที่จัดแล้ว
        $this->assertFalse($importer->apply($question, $item));
    }

    private function contentCounts(): array
    {
        $counts = [];

        foreach ([
            'languages',
            'courses',
            'units',
            'lessons',
            'vocabularies',
            'exercises',
            'questions',
            'answers',
        ] as $table) {
            $counts[$table] = DB::table($table)->count();
        }

        return $counts;
    }

    private function modes(): array
    {
        return Question::orderBy('id')
            ->pluck('vocabulary_mode', 'id')
            ->all();
    }

    private function links(): array
    {
        return DB::table('question_vocabulary')
            ->orderBy('question_id')
            ->orderBy('vocabulary_id')
            ->get(['question_id', 'vocabulary_id'])
            ->map(fn ($row) => [
                (int) $row->question_id,
                (int) $row->vocabulary_id,
            ])
            ->all();
    }
}