<?php

namespace Tests\Feature;

use App\Models\Exercise;
use App\Models\Question;
use App\Models\Vocabulary;
use App\Services\Content\QuestionVocabularyImporter;
use App\Services\Content\QuestionVocabularyResolver;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\CreatesContentLesson;
use Tests\TestCase;

class AutomaticVocabularyBatchTest extends TestCase
{
    use RefreshDatabase;
    use CreatesContentLesson;

    public function test_resolver_reconstructs_blanks_and_ignores_wrong_choices(): void
    {
        $resolver = new QuestionVocabularyResolver();

        $this->assertSame(['你好'], $resolver->resolve([
            'question' => 'เติมคำ: 你___！',
            'answers' => [['好', true], ['谢谢', false]],
        ], ['你好', '谢谢']));

        $this->assertSame(['年龄'], $resolver->resolve([
            'question' => '年龄 หมายถึงอะไร?',
            'answers' => [['อายุ', true], ['去年', false]],
        ], ['年', '年龄', '去年']));

        $this->assertSame([], $resolver->resolve([
            'question' => 'คำถามที่ไม่มีคำจีน',
            'answers' => [['คำตอบไทย', true], ['谢谢', false]],
        ], ['谢谢']));
    }

    public function test_importer_fills_pending_mapping_and_preserves_admin_changes(): void
    {
        $lesson = $this->createContentLesson();
        $word = Vocabulary::factory()->create(['lesson_id' => $lesson->id, 'word' => '你好']);
        $exercise = Exercise::factory()->create(['lesson_id' => $lesson->id, 'type' => 'fill_blank']);
        $question = Question::factory()->for($exercise)->create(['vocabulary_mode' => 'pending']);
        $item = ['question' => 'เติมคำ: 你___！', 'answers' => [['好', true]]];
        $importer = app(QuestionVocabularyImporter::class);

        $this->assertTrue($importer->apply($question, $item));
        $this->assertSame('after_vocabulary', $question->fresh()->vocabulary_mode);
        $this->assertDatabaseHas('question_vocabulary', ['question_id' => $question->id, 'vocabulary_id' => $word->id]);
        $this->assertFalse($importer->apply($question, $item));

        $question->vocabularies()->detach();
        $question->update(['vocabulary_mode' => 'lesson_end']);
        $this->assertFalse($importer->apply($question, $item));
        $this->assertSame('lesson_end', $question->fresh()->vocabulary_mode);
        $this->assertCount(0, $question->fresh()->vocabularies);
    }

    public function test_five_word_groups_alternate_with_related_questions_without_skipping_prerequisites(): void
    {
        config()->set('learning.vocabulary_batch_size', 5);
        config()->set('learning.group_related_vocabulary', true);
        $lesson = $this->createContentLesson();
        $words = Vocabulary::factory()->count(10)->create(['lesson_id' => $lesson->id])->sortBy('id')->values();
        $exercise = Exercise::factory()->create(['lesson_id' => $lesson->id, 'type' => 'multiple_choice']);
        $answers = [];

        foreach ([[$words[9]->id], [$words[4]->id], $words->modelKeys()] as $required) {
            $question = Question::factory()->for($exercise)->create(['vocabulary_mode' => 'after_vocabulary']);
            $question->vocabularies()->sync($required);
            $answer = $question->answers()->create(['answer' => 'ถูก', 'is_correct' => true]);
            $question->answers()->create(['answer' => 'ผิด', 'is_correct' => false]);
            $answers[$question->id] = $answer->id;
        }

        $this->get(route('lessons.learn', $lesson))->assertRedirect();
        $learned = [];
        $played = [];
        $vocabularyRun = 0;
        $groups = [];

        for ($step = 1; $step <= 13; $step++) {
            $response = $this->get(route('lessons.learn.step', ['lesson' => $lesson->id, 'step' => $step]))
                ->assertOk()->assertViewHas('total', 13);
            $current = $response->viewData('current');
            $payload = [];

            if ($current['type'] === 'vocabulary') {
                $learned[] = $current['vocabulary']->id;
                $vocabularyRun++;
            } else {
                if ($vocabularyRun > 0) {
                    $groups[] = $vocabularyRun;
                    $vocabularyRun = 0;
                }

                $question = $current['question'];
                $this->assertSame([], array_values(array_diff($question->vocabularies->modelKeys(), $learned)));
                $played[] = $question->id;
                $payload['answer'] = $answers[$question->id];
            }

            $this->post(route('lessons.learn.submit', ['lesson' => $lesson->id, 'step' => $step]), $payload)
                ->assertRedirect()->assertSessionHasNoErrors();
        }

        $this->assertSame([5, 5], $groups);
        $this->assertEqualsCanonicalizing($words->modelKeys(), $learned);
        $this->assertEqualsCanonicalizing(array_keys($answers), $played);
        $this->get(route('lessons.learn.summary', $lesson))->assertOk()
            ->assertViewHas('summary', fn ($summary) => $summary['question_count'] === 3 && $summary['score_percent'] === 100.0);
    }
}
