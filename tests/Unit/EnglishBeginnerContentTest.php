<?php

namespace Tests\Unit;

use App\Services\Content\QuestionVocabularyResolver;
use PHPUnit\Framework\TestCase;

class EnglishBeginnerContentTest extends TestCase
{
    private function content(): array
    {
        return require dirname(__DIR__, 2).'/database/seeders/data/english.php';
    }

    private function questions(array $items): array
    {
        return isset($items['question']) ? [$items] : $items;
    }

    public function test_source_bank_counts_and_answer_integrity(): void
    {
        $content = $this->content();
        $counts = ['lessons' => count($content), 'vocabulary' => 0, 'exercises' => 0, 'questions' => 0, 'answers' => 0];

        foreach ($content as $title => $lesson) {
            $counts['vocabulary'] += count($lesson['vocabulary']);
            $counts['exercises'] += count($lesson['exercises']);

            foreach ($lesson['exercises'] as $type => $items) {
                $questions = $this->questions($items);
                $this->assertCount(count($questions), array_unique(array_column($questions, 'question')), $title.' '.$type.' has duplicate seeder question keys');

                foreach ($questions as $index => $item) {
                    $context = $title.' '.$type.' #'.($index + 1);
                    $this->assertNotSame('', trim($item['question']), $context);
                    $this->assertCount(4, $item['answers'], $context);

                    foreach ($item['answers'] as $answer) {
                        $this->assertCount(2, $answer, $context);
                        $this->assertIsString($answer[0], $context);
                        $this->assertNotSame('', trim($answer[0]), $context);
                        $this->assertIsBool($answer[1], $context);
                    }

                    $this->assertCount(4, array_unique(array_column($item['answers'], 0)), $context);
                    $this->assertSame(1, count(array_filter($item['answers'], fn ($answer) => $answer[1] === true)), $context);
                    $counts['questions']++;
                    $counts['answers'] += count($item['answers']);
                }
            }
        }

        $this->assertSame(['lessons' => 19, 'vocabulary' => 295, 'exercises' => 76, 'questions' => 400, 'answers' => 1600], $counts);
    }

    public function test_every_question_has_valid_same_lesson_prerequisites_or_explicit_lesson_end(): void
    {
        $resolver = new QuestionVocabularyResolver;

        foreach ($this->content() as $title => $lesson) {
            $words = array_column($lesson['vocabulary'], 0);
            $this->assertCount(count($words), array_unique($words), $title);

            foreach ($lesson['exercises'] as $type => $items) {
                foreach ($this->questions($items) as $index => $item) {
                    $context = $title.' '.$type.' #'.($index + 1);
                    $hasMode = array_key_exists('vocabulary_mode', $item);
                    $hasWords = array_key_exists('required_vocabulary_words', $item);
                    $this->assertSame($hasMode, $hasWords, $context.' requires both metadata fields');

                    if (! $hasMode) {
                        $this->assertNotEmpty($resolver->resolve($item, $words), $context.' would remain pending');

                        continue;
                    }

                    $this->assertContains($item['vocabulary_mode'], ['after_vocabulary', 'lesson_end'], $context);
                    $required = $item['required_vocabulary_words'];
                    $this->assertIsArray($required, $context);
                    $this->assertTrue(array_is_list($required), $context);
                    $this->assertCount(count($required), array_unique($required), $context);

                    if ($item['vocabulary_mode'] === 'lesson_end') {
                        $this->assertSame([], $required, $context);
                    } else {
                        $this->assertNotEmpty($required, $context);
                    }

                    foreach ($required as $word) {
                        $this->assertIsString($word, $context);
                        $this->assertTrue(in_array($word, $words, true), $context.' references missing or cross-lesson vocabulary: '.$word);
                    }
                }
            }
        }
    }

    public function test_all_listening_and_image_paths_are_unique_and_exist(): void
    {
        $paths = ['listening' => [], 'image_choice' => []];
        $media = ['listening' => ['audio_path', 'audio/english/', '.mp3'], 'image_choice' => ['image_path', 'images/english/', '.jpg']];

        foreach ($this->content() as $title => $lesson) {
            foreach ($media as $type => [$field, $prefix, $extension]) {
                foreach ($this->questions($lesson['exercises'][$type]) as $item) {
                    $path = $item[$field] ?? '';
                    $this->assertStringStartsWith($prefix, $path, $title);
                    $this->assertStringEndsWith($extension, $path, $title);
                    $this->assertStringNotContainsString('..', $path, $title);
                    $this->assertFileExists(dirname(__DIR__, 2).'/public/'.$path, $title);
                    $paths[$type][] = $path;
                }
            }
        }

        foreach ($paths as $type => $values) {
            $this->assertCount(38, $values, $type);
            $this->assertCount(38, array_unique($values), $type.' contains duplicate paths');
        }
    }
}
