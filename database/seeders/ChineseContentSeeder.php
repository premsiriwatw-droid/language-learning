<?php

namespace Database\Seeders;

use App\Models\Lesson;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ChineseContentSeeder extends Seeder
{
    public function run(): void
    {
        foreach (require __DIR__.'/data/chinese.php' as $title => $content) {
            $lessons = Lesson::where('title', $title)
                ->whereHas('unit.course.language', fn ($query) => $query->where('name', 'Chinese'))
                ->get();

            if ($lessons->count() !== 1) {
                $this->command?->warn(
                    "Chinese content skipped: expected one Chinese lesson titled [{$title}], found {$lessons->count()}."
                );

                continue;
            }

            $lesson = $lessons->sole();

            DB::transaction(function () use ($lesson, $content) {
                /*
                |--------------------------------------------------------------------------
                | Vocabulary
                |--------------------------------------------------------------------------
                */

                foreach (
                    $content['vocabulary']
                    as [$word, $pinyin, $meaning, $example, $examplePinyin, $exampleMeaning]
                ) {
                    $lesson->vocabularies()->updateOrCreate(
                        [
                            'word' => $word,
                        ],
                        [
                            'pinyin' => $pinyin,
                            'meaning' => $meaning,
                            'example_sentence' => $example,
                            'example_pinyin' => $examplePinyin,
                            'example_meaning' => $exampleMeaning,
                        ]
                    );
                }

                /*
                |--------------------------------------------------------------------------
                | Exercises
                |--------------------------------------------------------------------------
                |
                | Supports both formats:
                |
                | Old format:
                | 'multiple_choice' => [
                |     'question' => '...',
                |     'answers' => [...]
                | ]
                |
                | New format:
                | 'multiple_choice' => [
                |     [
                |         'question' => '...',
                |         'answers' => [...]
                |     ],
                |     [
                |         'question' => '...',
                |         'answers' => [...]
                |     ],
                | ]
                |
                */

                foreach ($content['exercises'] as $type => $items) {
                    $exercise = $lesson->exercises()->firstOrCreate([
                        'type' => $type,
                        'title' => 'Chinese MVP: '.$type,
                    ]);

                    // Keep backward compatibility with the original data format.
                    if (isset($items['question'])) {
                        $items = [$items];
                    }

                    foreach ($items as $item) {
                        $question = $exercise->questions()->updateOrCreate(
                            [
                                'question' => $item['question'],
                            ],
                            [
                                'explanation' => $item['explanation'] ?? null,
                                'audio_path' => $item['audio_path'] ?? null,
                                'image_path' => $item['image_path'] ?? null,
                            ]
                        );

                        foreach ($item['answers'] as [$answer, $correct]) {
                            $question->answers()->updateOrCreate(
                                [
                                    'answer' => $answer,
                                ],
                                [
                                    'is_correct' => $correct,
                                ]
                            );
                        }
                    }
                }
            });
        }
    }
}