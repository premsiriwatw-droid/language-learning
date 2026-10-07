<?php

namespace Tests\Feature;

use App\Models\Lesson;
use App\Models\Question;
use Database\Seeders\ChineseContentSeeder;
use Database\Seeders\ChineseQuestionVocabularySeeder;
use Database\Seeders\LearningStructureSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ChineseQuestionVocabularySeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_backfill_matches_content_and_is_idempotent(): void
    {
        $this->seed(LearningStructureSeeder::class);
        $this->seed(ChineseContentSeeder::class);

        // จำลองข้อมูลเดิมที่ยังไม่ได้จัดความสัมพันธ์
        DB::table('question_vocabulary')->delete();
        DB::table('questions')->update(['vocabulary_mode' => 'pending']);
        $counts = $this->contentCounts();

        $this->seed(ChineseQuestionVocabularySeeder::class);

        foreach (require database_path('seeders/data/chinese.php') as $title => $content) {
            $lesson = Lesson::where('title', $title)
                ->whereHas('unit.course.language', fn ($query) => $query->where('name', 'Chinese'))
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

                    $this->assertSame($item['vocabulary_mode'], $question->vocabulary_mode);
                    $this->assertEqualsCanonicalizing(
                        $item['required_vocabulary_words'],
                        $question->vocabularies->pluck('word')->all()
                    );

                    foreach ($question->vocabularies as $word) {
                        $this->assertSame((int) $lesson->id, (int) $word->lesson_id);
                    }
                }
            }
        }

        $links = $this->links();
        $modes = Question::orderBy('id')->pluck('vocabulary_mode', 'id')->all();

        $this->seed(ChineseQuestionVocabularySeeder::class);

        $this->assertSame($links, $this->links());
        $this->assertSame($modes, Question::orderBy('id')->pluck('vocabulary_mode', 'id')->all());
        $this->assertSame($counts, $this->contentCounts());
    }

    public function test_reseeding_preserves_a_mapping_changed_to_lesson_end(): void
    {
        $this->seed(LearningStructureSeeder::class);
        $this->seed(ChineseContentSeeder::class);

        $question = Question::where('vocabulary_mode', 'after_vocabulary')
            ->orderBy('id')
            ->firstOrFail();

        // จำลองผลการจัด mapping ผ่าน Admin
        $question->vocabularies()->detach();
        $question->update(['vocabulary_mode' => 'lesson_end']);

        $this->seed(ChineseQuestionVocabularySeeder::class);
        $this->seed(ChineseContentSeeder::class);

        $question->refresh();

        $this->assertSame('lesson_end', $question->vocabulary_mode);
        $this->assertCount(0, $question->vocabularies);
    }

    private function contentCounts(): array
    {
        $counts = [];

        foreach (['languages', 'courses', 'units', 'lessons', 'vocabularies', 'exercises', 'questions', 'answers'] as $table) {
            $counts[$table] = DB::table($table)->count();
        }

        return $counts;
    }

    private function links(): array
    {
        return DB::table('question_vocabulary')
            ->orderBy('question_id')
            ->orderBy('vocabulary_id')
            ->get(['question_id', 'vocabulary_id'])
            ->map(fn ($row) => [(int) $row->question_id, (int) $row->vocabulary_id])
            ->all();
    }
}
