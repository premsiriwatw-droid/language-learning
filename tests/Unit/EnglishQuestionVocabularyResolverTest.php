<?php

namespace Tests\Unit;

use App\Services\Content\QuestionVocabularyResolver;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class EnglishQuestionVocabularyResolverTest extends TestCase
{
    #[DataProvider('matchingCases')]
    public function test_matching(array $item, array $words, array $expected): void
    {
        $this->assertSame($expected, (new QuestionVocabularyResolver)->resolve($item, $words));
    }

    public static function matchingCases(): array
    {
        return [
            'correct answer only' => [['question' => 'What do you say when you meet someone?', 'answers' => [['Hello', true], ['student', false]]], ['hello', 'goodbye', 'student', 'water'], ['hello']],
            'uppercase prompt' => [['question' => 'HELLO!'], ['hello'], ['hello']],
            'phrase' => [['answers' => [['Good morning', true], ['Good night', false]]], ['good morning', 'good night', 'thank you'], ['good morning']],
            'phrase whitespace' => [['question' => "At the FRONT   DESK."], ['front desk'], ['front desk']],
            'boundaries' => [['question' => 'the catch students he2 2he _he he_'], ['he', 'cat', 'student'], []],
            'whole words' => [['question' => 'He owns a cat.'], ['he', 'cat'], ['he', 'cat']],
            'single blank' => [['question' => 'Good _', 'answers' => [['morning', true], ['night', false]]], ['good morning', 'good night'], ['good morning']],
            'multiple underscores' => [['question' => 'Thank ___', 'answers' => [['you', true]]], ['thank you'], ['thank you']],
            'audio script' => [['question' => 'Listen.', 'audio_script' => 'Drink WATER.'], ['water'], ['water']],
            'ignore wrong and explanation' => [['question' => 'Choose.', 'answers' => [['Unknown', true], ['water', false]], 'explanation' => 'water is incorrect'], ['water'], []],
            'preserve stored spelling and order' => [['question' => 'water then hello'], ['Hello', 'Water'], ['Hello', 'Water']],
            'literal punctuation' => [['question' => 'fooXbar'], ['foo.bar'], []],
            'Chinese longest match' => [['question' => '年龄'], ['年', '年龄'], ['年龄']],
            'Chinese blank' => [['question' => '我喝___。', 'answers' => [['水', true], ['茶', false]]], ['水', '茶'], ['水']],
            'Chinese quoted audio' => [['explanation' => '听到“你好”。'], ['你好'], ['你好']],
            'mixed languages' => [['question' => 'Hello 你好'], ['你好', 'hello'], ['你好', 'hello']],
        ];
    }
}
