<?php

namespace App\Services\Content;

class QuestionVocabularyResolver
{
    /** Match lesson vocabulary in the prompt, correct answers and audio script. */
    public function resolve(array $item, array $vocabularyWords): array
    {
        $dictionary = array_values(array_unique(array_filter(
            $vocabularyWords,
            fn ($word) => is_string($word) && preg_match('/^\p{Han}+$/u', $word)
        )));

        // Match the longest word first: 年龄 must not be reduced to 年.
        usort($dictionary, fn ($left, $right) =>
            mb_strlen($right) <=> mb_strlen($left) ?: strcmp($left, $right)
        );

        $texts = [(string) ($item['question'] ?? '')];

        foreach ($item['answers'] ?? [] as $answer) {
            if ((bool) ($answer[1] ?? false)) {
                $texts[] = (string) $answer[0];
                if (str_contains($texts[0], '___')) {
                    $texts[] = preg_replace('/_{2,}/u', (string) $answer[0], $texts[0], 1);
                }
            }
        }

        if (!empty($item['audio_script'])) {
            $texts[] = (string) $item['audio_script'];
        }

        // Older listening content puts the spoken Chinese inside quotation marks.
        // Do not scan the whole explanation, which may discuss wrong options.
        preg_match_all('/[“「]([\p{Han}。，、！？\s]+)[”」]/u', (string) ($item['explanation'] ?? ''), $quoted);
        foreach ($quoted[1] as $spoken) {
            $texts[] = $spoken;
        }

        $matched = [];

        foreach ($texts as $text) {
            preg_match_all('/\p{Han}+/u', $text, $runs);

            foreach ($runs[0] as $run) {
                while ($run !== '') {
                    $found = null;

                    foreach ($dictionary as $word) {
                        if (str_starts_with($run, $word)) {
                            $found = $word;
                            break;
                        }
                    }

                    if ($found !== null) {
                        $matched[$found] = true;
                        $run = mb_substr($run, mb_strlen($found));
                    } else {
                        $run = mb_substr($run, 1);
                    }
                }
            }
        }

        return array_values(array_filter(
            $vocabularyWords,
            fn ($word) => isset($matched[$word])
        ));
    }
}
