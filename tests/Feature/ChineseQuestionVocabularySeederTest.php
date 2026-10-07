<?php

namespace Tests\Feature;

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

    public function test_existing_content_can_be_linked_without_creating_duplicate_content(): void
    {
        // จำลองฐานข้อมูลเดิม: มีเนื้อหาแล้ว แต่ยังไม่มีความสัมพันธ์
        $this->seed(LearningStructureSeeder::class);
        $this->seed(ChineseContentSeeder::class);

        DB::table('question_vocabulary')->delete();

        $this->assertDatabaseCount('question_vocabulary', 0);

        $contentCounts = $this->contentCounts();

        $questionIds = Question::query()
            ->whereHas('exercise.lesson.unit.course.language', function ($query) {
                $query->where('name', 'Chinese');
            })
            ->orderBy('id')
            ->pluck('id');

        $this->assertCount(30, $questionIds);

        // ขั้นตอนอัปเดตข้อมูลเดิมหลัง migrate
        $this->seed(ChineseQuestionVocabularySeeder::class);

        $questions = Question::with([
            'exercise',
            'vocabularies',
        ])
            ->whereIn('id', $questionIds)
            ->get();

        foreach ($questions as $question) {
            $this->assertNotEmpty(
                $question->vocabularies,
                "คำถาม {$question->id} ต้องมีคำศัพท์ที่ต้องเรียนก่อน"
            );

            foreach ($question->vocabularies as $vocabulary) {
                $this->assertSame(
                    (int) $question->exercise->lesson_id,
                    (int) $vocabulary->lesson_id,
                    'คำถามต้องผูกกับคำศัพท์ในบทเดียวกัน'
                );
            }
        }

        $this->assertSame($contentCounts, $this->contentCounts());

        $linksBefore = $this->links();

        // รันซ้ำต้องได้ความสัมพันธ์เดิม และไม่เพิ่มเนื้อหาซ้ำ
        $this->seed(ChineseQuestionVocabularySeeder::class);

        $this->assertSame($linksBefore, $this->links());
        $this->assertSame($contentCounts, $this->contentCounts());
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