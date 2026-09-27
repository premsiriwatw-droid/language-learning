<?php

namespace Tests\Feature;

use App\Models\Lesson;
use Database\Seeders\ChineseContentSeeder;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ChineseContentSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_chinese_content_seeder_creates_expected_content(): void
    {
        $this->seed(DatabaseSeeder::class);

        $lessons = $this->getChineseLessons();

        /*
        |--------------------------------------------------------------------------
        | Learning Structure
        |--------------------------------------------------------------------------
        */

        $this->assertCount(11, $lessons);

        $expectedLessonTitles = [
            'Greetings',
            'Self Introduction',
            'Numbers',
            'Family',
            'Age',
            'Time',
            'Food & Drinks',
            'Shopping',
            'Transportation',
            'Places & Directions',
            'Hobbies',
        ];

        $this->assertEqualsCanonicalizing(
            $expectedLessonTitles,
            $lessons->pluck('title')->all()
        );

        $greetings = $lessons->firstWhere('title', 'Greetings');
        $selfIntroduction = $lessons->firstWhere('title', 'Self Introduction');
        $numbers = $lessons->firstWhere('title', 'Numbers');

        $this->assertNotNull($greetings);
        $this->assertNotNull($selfIntroduction);
        $this->assertNotNull($numbers);

        $contentLessons = collect([
            $greetings,
            $selfIntroduction,
            $numbers,
        ]);

        /*
        |--------------------------------------------------------------------------
        | Vocabulary
        |--------------------------------------------------------------------------
        */

        $this->assertCount(12, $greetings->vocabularies);
        $this->assertCount(12, $selfIntroduction->vocabularies);
        $this->assertCount(15, $numbers->vocabularies);

        $this->assertEquals(
            39,
            $contentLessons->sum(
                fn ($lesson) => $lesson->vocabularies->count()
            )
        );

        /*
        |--------------------------------------------------------------------------
        | Exercises
        |--------------------------------------------------------------------------
        */

        foreach ($contentLessons as $lesson) {
            $this->assertCount(4, $lesson->exercises);

            $this->assertEqualsCanonicalizing(
                [
                    'multiple_choice',
                    'fill_blank',
                    'listening',
                    'image_choice',
                ],
                $lesson->exercises
                    ->pluck('type')
                    ->all()
            );
        }

        $this->assertEquals(
            12,
            $contentLessons->sum(
                fn ($lesson) => $lesson->exercises->count()
            )
        );

        /*
        |--------------------------------------------------------------------------
        | Questions
        |--------------------------------------------------------------------------
        */

        $questions = $contentLessons
            ->flatMap(
                fn ($lesson) => $lesson->exercises
            )
            ->flatMap(
                fn ($exercise) => $exercise->questions
            );

        $this->assertCount(51, $questions);

        $this->assertLessonQuestionStructure(
            $greetings,
            total: 15,
            multipleChoice: 7,
            fillBlank: 6
        );

        $this->assertLessonQuestionStructure(
            $selfIntroduction,
            total: 16,
            multipleChoice: 8,
            fillBlank: 6
        );

        $this->assertLessonQuestionStructure(
            $numbers,
            total: 20,
            multipleChoice: 13,
            fillBlank: 5
        );

        /*
        |--------------------------------------------------------------------------
        | Answers
        |--------------------------------------------------------------------------
        */

        $answers = $questions
            ->flatMap(
                fn ($question) => $question->answers
            );

        $this->assertCount(204, $answers);

        foreach ($questions as $question) {
            $this->assertCount(
                4,
                $question->answers
            );

            $this->assertCount(
                1,
                $question->answers
                    ->where('is_correct', true)
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Media
        |--------------------------------------------------------------------------
        */

        $greetingsListening = $greetings->exercises
            ->firstWhere('type', 'listening');

        $this->assertNotNull($greetingsListening);

        $this->assertEquals(
            'audio/chinese/greetings/ni-hao.mp3',
            $greetingsListening->questions->first()->audio_path
        );

        $greetingsImageChoice = $greetings->exercises
            ->firstWhere('type', 'image_choice');

        $this->assertNotNull($greetingsImageChoice);

        $this->assertEquals(
            'images/chinese/greetings/goodbye.jpg',
            $greetingsImageChoice->questions->first()->image_path
        );

        $selfIntroductionListening = $selfIntroduction->exercises
            ->firstWhere('type', 'listening');

        $this->assertNotNull($selfIntroductionListening);

        $this->assertEquals(
            'audio/chinese/self-introduction/lao-shi.mp3',
            $selfIntroductionListening->questions->first()->audio_path
        );

        $selfIntroductionImageChoice = $selfIntroduction->exercises
            ->firstWhere('type', 'image_choice');

        $this->assertNotNull($selfIntroductionImageChoice);

        $this->assertEquals(
            'images/chinese/self-introduction/teacher.jpg',
            $selfIntroductionImageChoice->questions->first()->image_path
        );

        $numbersListening = $numbers->exercises
            ->firstWhere('type', 'listening');

        $this->assertNotNull($numbersListening);

        $this->assertEquals(
            'audio/chinese/numbers/wu.mp3',
            $numbersListening->questions->first()->audio_path
        );

        $numbersImageChoice = $numbers->exercises
            ->firstWhere('type', 'image_choice');

        $this->assertNotNull($numbersImageChoice);

        $this->assertEquals(
            'images/chinese/numbers/three-apples.jpg',
            $numbersImageChoice->questions->first()->image_path
        );
    }

    public function test_chinese_content_seeder_is_idempotent(): void
    {
        $this->seed(DatabaseSeeder::class);

        /*
         * Run the Chinese content seeder again.
         * It must update/reuse existing records instead of creating duplicates.
         */
        $this->seed(ChineseContentSeeder::class);

        $lessons = $this->getChineseLessons();

        /*
        |--------------------------------------------------------------------------
        | Learning Structure
        |--------------------------------------------------------------------------
        */

        $this->assertCount(11, $lessons);

        $greetings = $lessons->firstWhere('title', 'Greetings');
        $selfIntroduction = $lessons->firstWhere('title', 'Self Introduction');
        $numbers = $lessons->firstWhere('title', 'Numbers');

        $this->assertNotNull($greetings);
        $this->assertNotNull($selfIntroduction);
        $this->assertNotNull($numbers);

        $contentLessons = collect([
            $greetings,
            $selfIntroduction,
            $numbers,
        ]);

        /*
        |--------------------------------------------------------------------------
        | Vocabulary
        |--------------------------------------------------------------------------
        */

        $this->assertEquals(
            39,
            $contentLessons->sum(
                fn ($lesson) => $lesson->vocabularies->count()
            )
        );

        /*
        |--------------------------------------------------------------------------
        | Exercises
        |--------------------------------------------------------------------------
        */

        $this->assertEquals(
            12,
            $contentLessons->sum(
                fn ($lesson) => $lesson->exercises->count()
            )
        );

        /*
        |--------------------------------------------------------------------------
        | Questions
        |--------------------------------------------------------------------------
        */

        $questions = $contentLessons
            ->flatMap(
                fn ($lesson) => $lesson->exercises
            )
            ->flatMap(
                fn ($exercise) => $exercise->questions
            );

        $this->assertCount(51, $questions);

        $this->assertLessonQuestionStructure(
            $greetings,
            total: 15,
            multipleChoice: 7,
            fillBlank: 6
        );

        $this->assertLessonQuestionStructure(
            $selfIntroduction,
            total: 16,
            multipleChoice: 8,
            fillBlank: 6
        );

        $this->assertLessonQuestionStructure(
            $numbers,
            total: 20,
            multipleChoice: 13,
            fillBlank: 5
        );

        /*
        |--------------------------------------------------------------------------
        | Answers
        |--------------------------------------------------------------------------
        */

        $answers = $questions
            ->flatMap(
                fn ($question) => $question->answers
            );

        $this->assertCount(204, $answers);

        foreach ($questions as $question) {
            $this->assertCount(
                4,
                $question->answers
            );

            $this->assertCount(
                1,
                $question->answers
                    ->where('is_correct', true)
            );
        }
    }

    private function getChineseLessons()
    {
        return Lesson::with([
            'vocabularies',
            'exercises.questions.answers',
        ])
            ->whereHas(
                'unit.course.language',
                fn ($query) => $query->where('name', 'Chinese')
            )
            ->get();
    }

    private function assertLessonQuestionStructure(
        Lesson $lesson,
        int $total,
        int $multipleChoice,
        int $fillBlank
    ): void {
        $lessonQuestions = $lesson->exercises
            ->flatMap(
                fn ($exercise) => $exercise->questions
            );

        $this->assertCount(
            $total,
            $lessonQuestions
        );

        $multipleChoiceExercise = $lesson->exercises
            ->firstWhere('type', 'multiple_choice');

        $fillBlankExercise = $lesson->exercises
            ->firstWhere('type', 'fill_blank');

        $listeningExercise = $lesson->exercises
            ->firstWhere('type', 'listening');

        $imageChoiceExercise = $lesson->exercises
            ->firstWhere('type', 'image_choice');

        $this->assertNotNull($multipleChoiceExercise);
        $this->assertNotNull($fillBlankExercise);
        $this->assertNotNull($listeningExercise);
        $this->assertNotNull($imageChoiceExercise);

        $this->assertCount(
            $multipleChoice,
            $multipleChoiceExercise->questions
        );

        $this->assertCount(
            $fillBlank,
            $fillBlankExercise->questions
        );

        $this->assertCount(
            1,
            $listeningExercise->questions
        );

        $this->assertCount(
            1,
            $imageChoiceExercise->questions
        );
    }
}