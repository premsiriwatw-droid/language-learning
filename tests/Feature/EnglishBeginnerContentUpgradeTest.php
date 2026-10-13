<?php

namespace Tests\Feature;

use App\Models\Exercise;
use App\Models\LearningAttempt;
use App\Models\Lesson;
use App\Models\LessonProgress;
use App\Models\Question;
use App\Models\User;
use App\Services\Content\EnglishBeginnerContentUpgrade;
use App\Services\Content\QuestionVocabularyImporter;
use Database\Seeders\EnglishContentSeeder;
use Database\Seeders\LearningStructureSeeder;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use PHPUnit\Framework\Attributes\DataProvider;
use RuntimeException;
use Tests\TestCase;

class EnglishBeginnerContentUpgradeTest extends TestCase
{
    use RefreshDatabase;

    private const MIGRATION = '2026_10_13_100000_upgrade_english_beginner_content_in_place';

    public function test_existing_installation_migrates_in_place_and_reseeds_without_losing_ids_or_admin_mappings(): void
    {
        $this->seedOldEnglish();
        $this->assertEnglishCounts();
        $revisions = $this->revisions();
        $this->assertCount(90, $revisions);
        $this->assertCount(89, array_filter($revisions, fn ($row) => ! $row['make_lesson_end']));
        $adminMapped = $this->revisionQuestion('Family', 'multiple_choice', 6);
        $father = $adminMapped->exercise->lesson->vocabularies()->where('word', 'father')->sole();
        $adminMapped->vocabularies()->sync([$father->id]);
        $adminLessonEnd = $this->revisionQuestion('Family', 'multiple_choice', 1);
        $adminLessonEnd->vocabularies()->detach();
        $adminLessonEnd->update(['vocabulary_mode' => 'lesson_end']);
        $revisionIds = array_map(fn ($row) => $this->oldQuestion($row)->id, $revisions);
        $unchanged = $this->englishQuestions()->whereNotIn('questions.id', $revisionIds)->firstOrFail();
        $customWord = $unchanged->exercise->lesson->vocabularies()->orderByDesc('id')->firstOrFail();
        $unchanged->vocabularies()->sync([$customWord->id]);
        $unchanged->update(['vocabulary_mode' => 'after_vocabulary']);
        $this->createProtectedHistory($adminMapped);
        $this->createChineseSentinel($revisions[0]);
        $before = $this->snapshot();
        $review = $this->revisionQuestion('Review & Daily Conversation', 'image_choice', 2);
        $this->assertSame('after_vocabulary', $review->vocabulary_mode);
        $this->assertSame(['need'], $review->vocabularies()->pluck('word')->all());

        $this->migrateUpgrade();
        $this->assertUpgraded($before, $revisions, $review->id);
        $this->assertDatabaseHas('migrations', ['migration' => self::MIGRATION]);
        $this->assertSame([$father->id], $adminMapped->fresh()->vocabularies()->pluck('vocabularies.id')->all());
        $this->assertSame('lesson_end', $adminLessonEnd->fresh()->vocabulary_mode);
        $this->assertSame([$customWord->id], $unchanged->fresh()->vocabularies()->pluck('vocabularies.id')->all());
        $this->seed(EnglishContentSeeder::class);
        $this->assertUpgraded($before, $revisions, $review->id);
        $after = $this->snapshot();
        $this->seed(EnglishContentSeeder::class);
        $this->assertSame($after, $this->snapshot());
    }

    public function test_seeder_alone_upgrades_old_questions_before_question_text_lookup_and_is_idempotent(): void
    {
        $this->seedOldEnglish();
        $this->assertEnglishCounts();
        $this->assertDatabaseCount('question_vocabulary', 521);
        $before = $this->snapshot();
        $reviewId = $this->revisionQuestion('Review & Daily Conversation', 'image_choice', 2)->id;
        $this->seed(EnglishContentSeeder::class);
        $this->assertUpgraded($before, $this->revisions(), $reviewId);
        $this->assertDatabaseCount('question_vocabulary', 520);
        $after = $this->snapshot();
        $this->seed(EnglishContentSeeder::class);
        $this->assertSame($after, $this->snapshot());
    }

    public function test_empty_installation_and_already_revised_content_are_safe_to_run_repeatedly(): void
    {
        // The upgrade also runs while RefreshDatabase migrates a fresh installation.
        app(EnglishBeginnerContentUpgrade::class)->apply();
        $this->assertDatabaseCount('questions', 0);
        $this->seed([LearningStructureSeeder::class, EnglishContentSeeder::class]);
        $this->assertEnglishCounts();
        $this->assertSame(399, $this->englishQuestions()->where('vocabulary_mode', 'after_vocabulary')->count());
        $this->assertSame(1, $this->englishQuestions()->where('vocabulary_mode', 'lesson_end')->count());
        $before = $this->snapshot();
        app(EnglishBeginnerContentUpgrade::class)->apply();
        $this->seed(EnglishContentSeeder::class);
        $this->assertSame($before, $this->snapshot());
    }

    public function test_mixed_old_and_revised_rows_with_review_already_at_lesson_end_upgrade_without_replacing_ids(): void
    {
        $this->seedOldEnglish();
        $revisions = $this->revisions();
        foreach ($revisions as $index => $revision) {
            if ($index % 2 === 0 && ! $revision['make_lesson_end']) {
                $this->oldQuestion($revision)->update(['question' => $revision['new_question']]);
            }
        }
        $review = $this->revisionQuestion('Review & Daily Conversation', 'image_choice', 2);
        $review->vocabularies()->detach();
        $review->update(['vocabulary_mode' => 'lesson_end']);
        $before = $this->snapshot();

        $this->migrateUpgrade();
        $this->assertUpgraded($before, $revisions, $review->id);
        $after = $this->snapshot();
        $this->seed(EnglishContentSeeder::class);
        app(EnglishBeginnerContentUpgrade::class)->apply();
        $this->assertSame($after, $this->snapshot());
    }

    #[DataProvider('ambiguousQuestionStates')]
    public function test_ambiguous_or_missing_existing_question_aborts_without_partial_updates(string $state): void
    {
        $this->seedOldEnglish();
        // A later failed match must not leave earlier Greetings wording applied.
        $target = $this->revisionQuestion('Family', 'multiple_choice', 6);
        $revision = collect($this->revisions())->first(
            fn ($row) => $row['lesson'] === 'Family' && $row['type'] === 'multiple_choice' && $row['index'] === 6
        );
        if ($state === 'missing') {
            $target->update(['question' => 'Custom question replacing this seeded target.']);
        } elseif ($state === 'duplicate_exercise') {
            $target->exercise->replicate()->save();
        } elseif ($state === 'payload_correct_flag') {
            $answer = $target->answers()->orderBy('id')->firstOrFail();
            $answer->update(['is_correct' => ! $answer->is_correct]);
        } elseif (str_starts_with($state, 'payload_')) {
            $field = substr($state, strlen('payload_'));
            $target->update([$field => 'Unexpected custom '.$field]);
        } else {
            $duplicate = $target->replicate();
            if ($state === 'old_and_new') {
                $duplicate->question = $revision['new_question'];
            }
            $duplicate->save();
            foreach ($target->answers as $answer) {
                $duplicate->answers()->create(['answer' => $answer->answer, 'is_correct' => $answer->is_correct]);
            }
            $duplicate->vocabularies()->sync($target->vocabularies()->pluck('vocabularies.id')->all());
        }
        $this->assertUpgradeRejected($this->snapshot(), $state === 'old_and_new');
    }

    public static function ambiguousQuestionStates(): array
    {
        return [
            'both old and revised rows exist' => ['old_and_new'],
            'duplicate old rows exist' => ['duplicate_old'],
            'target missing from populated exercise' => ['missing'],
            'duplicate canonical exercise exists' => ['duplicate_exercise'],
            'question explanation differs from seeded payload' => ['payload_explanation'],
            'question audio path differs from seeded payload' => ['payload_audio_path'],
            'question image path differs from seeded payload' => ['payload_image_path'],
            'answer correctness flag differs from seeded payload' => ['payload_correct_flag'],
        ];
    }

    #[DataProvider('customReviewStates')]
    public function test_review_custom_mapping_blocks_the_upgrade_and_preserves_every_row(string $state): void
    {
        $this->seedOldEnglish();
        $review = $this->revisionQuestion('Review & Daily Conversation', 'image_choice', 2);
        $lesson = $review->exercise->lesson;
        $need = $lesson->vocabularies()->where('word', 'need')->sole();
        $custom = $lesson->vocabularies()->where('word', 'remember')->sole();
        $review->vocabularies()->sync($state === 'additional' ? [$need->id, $custom->id] : [$custom->id]);
        $this->createProtectedHistory($review);
        $this->assertUpgradeRejected($this->snapshot());
    }

    public static function customReviewStates(): array
    {
        return [
            'Admin added a prerequisite alongside need' => ['additional'],
            'Admin replaced need with another prerequisite' => ['replacement'],
        ];
    }

    private function seedOldEnglish(): void
    {
        $fixture = $this->oldFixture();
        $this->assertSame('43be146c41e912cfa85d5188e2c51db9a398851c', $fixture['source_commit']);
        $this->assertCount(19, $fixture['data']);
        $this->seed(LearningStructureSeeder::class);
        $importer = app(QuestionVocabularyImporter::class);
        foreach ($fixture['data'] as $title => $content) {
            $lesson = $this->englishLesson($title);
            foreach ($content['vocabulary'] as [$word, $pinyin, $meaning, $example, $examplePinyin, $exampleMeaning]) {
                $lesson->vocabularies()->create([
                    'word' => $word, 'pinyin' => $pinyin, 'meaning' => $meaning,
                    'example_sentence' => $example, 'example_pinyin' => $examplePinyin,
                    'example_meaning' => $exampleMeaning,
                ]);
            }
            foreach ($content['exercises'] as $type => $items) {
                $exercise = $lesson->exercises()->create(['type' => $type, 'title' => 'English MVP: '.$type]);
                foreach ($items as $item) {
                    $question = $exercise->questions()->create([
                        'question' => $item['question'], 'explanation' => $item['explanation'] ?? null,
                        'audio_path' => $item['audio_path'] ?? null, 'image_path' => $item['image_path'] ?? null,
                        'vocabulary_mode' => 'pending',
                    ]);
                    foreach ($item['answers'] as [$answer, $correct]) {
                        $question->answers()->create(['answer' => $answer, 'is_correct' => $correct]);
                    }
                    $importer->apply($question, $item);
                }
            }
        }
        $this->assertSame(400, $this->englishQuestions()->where('vocabulary_mode', 'after_vocabulary')->count());
    }

    private function oldFixture(): array
    {
        return json_decode(file_get_contents(base_path('tests/Support/english_before_beginner_revision.json')),
            true, flags: JSON_THROW_ON_ERROR);
    }

    private function revisions(): array
    {
        $current = require database_path('seeders/data/english.php');
        $revisions = [];
        foreach ($this->oldFixture()['data'] as $title => $lesson) {
            foreach ($lesson['exercises'] as $type => $items) {
                $newItems = $current[$title]['exercises'][$type];
                $newItems = isset($newItems['question']) ? [$newItems] : $newItems;
                foreach ($items as $index => $item) {
                    $new = $newItems[$index];
                    $makeLessonEnd = $title === 'Review & Daily Conversation' && $type === 'image_choice' && $index === 1;
                    if ($item['question'] !== $new['question'] || $makeLessonEnd) {
                        $revisions[] = [
                            'lesson' => $title, 'type' => $type, 'index' => $index + 1,
                            'old_question' => $item['question'], 'new_question' => $new['question'],
                            'make_lesson_end' => $makeLessonEnd,
                        ];
                    }
                }
            }
        }

        return $revisions;
    }

    private function englishLesson(string $title): Lesson
    {
        return Lesson::where('title', $title)->whereHas('unit.course', fn ($query) => $query
            ->where('title', 'English Beginner')->whereHas('language', fn ($query) => $query->where('name', 'English')))->sole();
    }

    private function englishQuestions(): Builder
    {
        return Question::whereHas('exercise.lesson.unit.course', fn ($query) => $query
            ->where('title', 'English Beginner')->whereHas('language', fn ($query) => $query->where('name', 'English')));
    }

    private function oldQuestion(array $revision): Question
    {
        $lessonId = $this->englishLesson($revision['lesson'])->id;

        return Question::where('question', $revision['old_question'])->whereHas('exercise', fn ($query) => $query
            ->where('lesson_id', $lessonId)->where('type', $revision['type'])
            ->where('title', 'English MVP: '.$revision['type']))->sole();
    }

    private function revisionQuestion(string $lesson, string $type, int $index): Question
    {
        $revision = collect($this->revisions())->first(fn ($row) => $row['lesson'] === $lesson
            && $row['type'] === $type && $row['index'] === $index);
        $this->assertNotNull($revision);

        return $this->oldQuestion($revision);
    }

    private function createProtectedHistory(Question $question): void
    {
        $user = User::factory()->create();
        LessonProgress::create([
            'user_id' => $user->id, 'lesson_id' => $question->exercise->lesson_id,
            'last_step' => 8, 'xp' => 70, 'stars' => 3, 'completed_at' => now(),
            'runtime_state' => ['question_id' => $question->id, 'answer_id' => $question->answers->first()->id],
        ]);
        LearningAttempt::create([
            'user_id' => $user->id, 'lesson_id' => $question->exercise->lesson_id,
            'run_key' => 'before-beginner-content-upgrade', 'lesson_title' => $question->exercise->lesson->title,
            'language_name' => 'English', 'vocabulary_count' => 15, 'question_count' => 1,
            'correct_count' => 1, 'wrong_count' => 0, 'wrong_attempts' => 0,
            'score_percent' => 100, 'elapsed_seconds' => 40, 'started_at' => now()->subSeconds(40),
            'completed_at' => now(), 'results' => [[
                'question_id' => $question->id, 'question' => $question->question,
                'answer_id' => $question->answers->first()->id,
            ]],
        ]);
    }

    private function createChineseSentinel(array $revision): void
    {
        $lesson = Lesson::where('title', $revision['lesson'])->whereHas(
            'unit.course.language', fn ($query) => $query->where('name', 'Chinese'))->sole();
        $exercise = $lesson->exercises()->create(['type' => $revision['type'], 'title' => 'Chinese MVP: '.$revision['type']]);
        $vocabulary = $lesson->vocabularies()->create(['word' => '你好', 'pinyin' => 'nǐ hǎo', 'meaning' => 'สวัสดี']);
        $question = $exercise->questions()->create([
            'question' => $revision['old_question'], 'explanation' => 'Unrelated Chinese content.',
            'vocabulary_mode' => 'after_vocabulary',
        ]);
        $question->answers()->create(['answer' => '你好', 'is_correct' => true]);
        $question->vocabularies()->sync([$vocabulary->id]);
    }

    private function assertEnglishCounts(): void
    {
        $lessonIds = Lesson::whereHas('unit.course', fn ($query) => $query->where('title', 'English Beginner')
            ->whereHas('language', fn ($query) => $query->where('name', 'English')))->pluck('id');
        $this->assertCount(19, $lessonIds);
        $this->assertSame(295, DB::table('vocabularies')->whereIn('lesson_id', $lessonIds)->count());
        $this->assertSame(76, Exercise::whereIn('lesson_id', $lessonIds)->count());
        $questionIds = $this->englishQuestions()->pluck('id');
        $this->assertCount(400, $questionIds);
        $this->assertSame(1600, DB::table('answers')->whereIn('question_id', $questionIds)->count());
    }

    private function snapshot(): array
    {
        $result = [];
        foreach (['languages', 'courses', 'units', 'lessons', 'vocabularies', 'exercises', 'questions', 'answers',
            'users', 'lesson_progress', 'learning_attempts'] as $table) {
            $result[$table] = DB::table($table)->orderBy('id')->get()->map(fn ($row) => (array) $row)->all();
        }
        $result['question_vocabulary'] = DB::table('question_vocabulary')->orderBy('question_id')->orderBy('vocabulary_id')
            ->get()->map(fn ($row) => (array) $row)->all();

        return $result;
    }

    private function assertUpgraded(array $before, array $revisions, int $reviewId): void
    {
        $this->assertEnglishCounts();
        $after = $this->snapshot();
        foreach ($before as $table => $rows) {
            if (! in_array($table, ['questions', 'question_vocabulary'], true)) {
                $this->assertSame($rows, $after[$table], $table.' rows changed');
            }
        }
        $this->assertSame(array_column($before['questions'], 'id'), array_column($after['questions'], 'id'));
        $expected = array_column($before['questions'], null, 'id');
        $revisedIds = [];
        foreach ($revisions as $revision) {
            $exercise = Exercise::where('lesson_id', $this->englishLesson($revision['lesson'])->id)
                ->where('type', $revision['type'])->where('title', 'English MVP: '.$revision['type'])->sole();
            $old = collect($before['questions'])->where('exercise_id', $exercise->id)
                ->filter(fn ($row) => in_array($row['question'], [$revision['old_question'], $revision['new_question']], true))->sole();
            $question = $exercise->questions()->where('question', $revision['new_question'])->sole();
            $this->assertSame($old['id'], $question->id, $revision['lesson'].' '.$revision['type'].' '.$revision['index']);
            $revisedIds[] = $question->id;
            $expected[$question->id]['question'] = $revision['new_question'];
            if ($revision['make_lesson_end']) {
                $expected[$question->id]['vocabulary_mode'] = 'lesson_end';
                $this->assertSame($reviewId, $question->id);
                $this->assertSame(0, $question->vocabularies()->count());
            } else {
                $this->assertSame(0, $exercise->questions()->where('question', $revision['old_question'])->count());
            }
        }
        foreach ($after['questions'] as $row) {
            $expectedRow = $expected[$row['id']];
            if (in_array($row['id'], $revisedIds, true)) {
                unset($expectedRow['updated_at'], $row['updated_at']);
            }
            $this->assertSame($expectedRow, $row);
        }
        $expectedPivots = array_values(array_filter($before['question_vocabulary'], fn ($row) => $row['question_id'] !== $reviewId));
        $this->assertSame($expectedPivots, $after['question_vocabulary']);
    }

    private function assertUpgradeRejected(array $before, bool $throughSeeder = false): void
    {
        try {
            if ($throughSeeder) {
                app(EnglishContentSeeder::class)->run();
            } else {
                app(EnglishBeginnerContentUpgrade::class)->apply();
            }
            $this->fail('Conflicting existing content must not be overwritten or arbitrarily deleted.');
        } catch (RuntimeException $exception) {
            $this->assertNotSame('', $exception->getMessage());
        }
        $this->assertSame($before, $this->snapshot(), 'A rejected upgrade must leave every captured row unchanged.');
    }

    private function migrateUpgrade(): void
    {
        // RefreshDatabase ran this migration on an empty database. Mark only this
        // data migration pending to exercise the normal existing-installation path.
        DB::table('migrations')->where('migration', self::MIGRATION)->delete();
        $this->artisan('migrate', [
            '--path' => ['database/migrations/'.self::MIGRATION.'.php'], '--force' => true,
        ])->assertExitCode(0);
    }
}
