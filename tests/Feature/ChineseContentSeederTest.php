<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\Lesson;
use Database\Seeders\ChineseContentSeeder;
use Database\Seeders\LearningStructureSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ChineseContentSeederTest extends TestCase
{
    use RefreshDatabase;

    private const PLAN = [
        'Unit 1: Basics' => ['Greetings', 'Self Introduction', 'Numbers'],
        'Unit 2: Daily Life' => ['Family', 'Age', 'Time', 'Food & Drinks'],
        'Unit 3: Everyday Conversation' => ['Shopping', 'Transportation', 'Places & Directions', 'Hobbies'],
        'Unit 4: Social & Activities' => ['Weather', 'Daily Routine', 'School & Study', 'Friends & Social Life'],
        'Unit 5: Practical Chinese' => ['Health & Body', 'Travel & Hotel', 'Asking for Help', 'Review & Daily Conversation'],
    ];

    private function seedChineseCourse(): void
    {
        $this->seed([LearningStructureSeeder::class, ChineseContentSeeder::class]);
    }

    public function test_chinese_course_has_five_units_and_nineteen_lessons_in_plan_order(): void
    {
        $this->seedChineseCourse();

        $course = Course::with('units.lessons')
            ->where('title', 'Chinese Beginner')
            ->whereHas('language', fn ($query) => $query->where('name', 'Chinese'))
            ->sole();

        $this->assertCount(5, $course->units);
        $this->assertSame(array_keys(self::PLAN), $course->units->pluck('title')->all());
        $this->assertSame(range(1, 5), $course->units->pluck('position')->all());

        foreach ($course->units as $unit) {
            $titles = self::PLAN[$unit->title];
            $this->assertSame($titles, $unit->lessons->pluck('title')->all());
            $this->assertSame(range(1, count($titles)), $unit->lessons->pluck('position')->all());
        }

        $titles = array_merge(...array_values(self::PLAN));
        $this->assertCount(19, $this->getChineseLessons());
        $this->assertSame($titles, array_keys(require database_path('seeders/data/chinese.php')));
    }

    public function test_every_lesson_seeds_complete_vocabulary_questions_and_supported_media(): void
    {
        $this->seedChineseCourse();
        $source = require database_path('seeders/data/chinese.php');
        $lessons = $this->getChineseLessons()->keyBy('title');
        $listeningCount = 0;
        $imageCount = 0;

        foreach ($source as $title => $content) {
            $lesson = $lessons->get($title);
            $this->assertNotNull($lesson, $title);
            $this->assertGreaterThanOrEqual(12, $lesson->vocabularies->count(), $title);
            $this->assertLessThanOrEqual(18, $lesson->vocabularies->count(), $title);
            $this->assertCount(count($content['vocabulary']), $lesson->vocabularies, $title);
            $this->assertCount(4, $lesson->exercises, $title);
            $this->assertEqualsCanonicalizing(
                ['multiple_choice', 'fill_blank', 'listening', 'image_choice'],
                $lesson->exercises->pluck('type')->all(),
                $title
            );

            $corpus = [];
            foreach ($lesson->exercises as $exercise) {
                $items = $content['exercises'][$exercise->type];
                $items = isset($items['question']) ? [$items] : $items;
                $this->assertCount(count($items), $exercise->questions, $title.' '.$exercise->type);
                $this->assertCount($exercise->questions->count(), $exercise->questions->unique('question'));
                $this->assertGreaterThan(0, $exercise->questions->count(), $title);

                if (in_array($exercise->type, ['listening', 'image_choice'], true)) {
                    $this->assertGreaterThanOrEqual(2, $exercise->questions->count(), $title.' '.$exercise->type);
                }

                foreach ($exercise->questions as $question) {
                    $this->assertNotEmpty(trim($question->question));
                    $this->assertNotEmpty(trim($question->explanation ?? ''), $title.' '.$question->question);
                    $this->assertCount(4, $question->answers, $title.' '.$question->question);
                    $this->assertCount(4, $question->answers->unique('answer'));
                    $this->assertCount(1, $question->answers->where('is_correct', true));
                    $corpus[] = $question->question;
                    foreach ($question->answers as $answer) {
                        $this->assertNotSame('', trim($answer->answer));
                        $corpus[] = $answer->answer;
                    }

                    $item = collect($items)->firstWhere('question', $question->question);
                    $this->assertNotNull($item);
                    $this->assertSame($item['explanation'], $question->explanation);
                    $this->assertSame($item['answers'], $question->answers->sortBy('id')
                        ->map(fn ($answer) => [$answer->answer, $answer->is_correct])->values()->all());

                    if ($exercise->type === 'listening') {
                        $listeningCount++;
                        $this->assertMatchesRegularExpression('#^audio/chinese/[a-z0-9-]+/[a-z0-9-]+\.mp3$#', $question->audio_path ?? '');
                        $this->assertSame($item['audio_path'], $question->audio_path);
                    } elseif ($exercise->type === 'image_choice') {
                        $imageCount++;
                        $this->assertMatchesRegularExpression('#^images/chinese/[a-z0-9-]+/[a-z0-9-]+\.jpg$#', $question->image_path ?? '');
                        $this->assertSame($item['image_path'], $question->image_path);
                    }
                }
            }

            $questionText = implode(' ', $corpus);
            foreach ($content['vocabulary'] as $row) {
                $this->assertCount(6, $row);
                $vocabulary = $lesson->vocabularies->firstWhere('word', $row[0]);
                $this->assertNotNull($vocabulary);
                foreach (['word', 'pinyin', 'meaning', 'example_sentence', 'example_pinyin', 'example_meaning'] as $index => $field) {
                    $this->assertNotSame('', trim($vocabulary->{$field}), $title.' '.$field);
                    $this->assertSame($row[$index], $vocabulary->{$field});
                }
                $this->assertStringContainsString($row[0], $questionText, $title.' unused vocabulary '.$row[0]);
            }
        }

        $this->assertGreaterThanOrEqual(38, $listeningCount);
        $this->assertGreaterThanOrEqual(38, $imageCount);
    }

    public function test_existing_and_locked_media_paths_are_preserved(): void
    {
        $this->seedChineseCourse();
        $questions = $this->getChineseLessons()->flatMap->exercises->flatMap->questions;
        foreach ([
            'audio/chinese/greetings/ni-hao.mp3',
            'audio/chinese/self-introduction/lao-shi.mp3',
            'audio/chinese/numbers/wu.mp3',
            'audio/chinese/family/wo-you-yi-ge-gege.mp3',
            'audio/chinese/family/ta-shi-wo-de-mama.mp3',
            'audio/chinese/age/wo-jinnian-shiba-sui.mp3',
            'audio/chinese/age/mingnian-ershiyi-sui.mp3',
            'audio/chinese/time/xianzai-badianban.mp3',
            'audio/chinese/time/xiawu-sandian-shangke.mp3',
        ] as $path) {
            $this->assertContains($path, $questions->pluck('audio_path')->all());
        }
        foreach ([
            'images/chinese/greetings/goodbye.jpg',
            'images/chinese/self-introduction/teacher.jpg',
            'images/chinese/numbers/three-apples.jpg',
            'images/chinese/family/family-mother.jpg',
            'images/chinese/family/family-group.jpg',
            'images/chinese/age/age-birthday-cake.jpg',
            'images/chinese/age/age-child-and-adult.jpg',
            'images/chinese/time/time-wall-clock.jpg',
            'images/chinese/time/time-morning-breakfast.jpg',
        ] as $path) {
            $this->assertContains($path, $questions->pluck('image_path')->all());
        }
    }

    public function test_chinese_content_seeder_is_idempotent_for_the_whole_course(): void
    {
        $this->seedChineseCourse();
        $before = $this->contentSnapshot();
        $this->seed(ChineseContentSeeder::class);
        $this->assertSame($before, $this->contentSnapshot());
        $this->seed(ChineseContentSeeder::class);
        $this->assertSame($before, $this->contentSnapshot());
    }

    private function getChineseLessons()
    {
        return Lesson::with(['vocabularies', 'exercises.questions.answers'])
            ->whereHas('unit.course.language', fn ($query) => $query->where('name', 'Chinese'))
            ->orderBy('id')->get();
    }

    private function contentSnapshot(): array
    {
        $lessonIds = $this->getChineseLessons()->pluck('id');
        $exerciseIds = DB::table('exercises')->whereIn('lesson_id', $lessonIds)->pluck('id');
        $questionIds = DB::table('questions')->whereIn('exercise_id', $exerciseIds)->pluck('id');
        $snapshot = [];
        foreach ([
            'vocabularies' => ['lesson_id', $lessonIds],
            'exercises' => ['lesson_id', $lessonIds],
            'questions' => ['exercise_id', $exerciseIds],
            'answers' => ['question_id', $questionIds],
        ] as $table => [$column, $ids]) {
            $snapshot[$table] = DB::table($table)->whereIn($column, $ids)->orderBy('id')->get()
                ->map(function ($record) {
                    $fields = (array) $record;
                    unset($fields['created_at'], $fields['updated_at']);
                    return $fields;
                })->all();
        }

        return $snapshot;
    }
}
