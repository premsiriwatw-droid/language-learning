<?php

namespace Database\Seeders;

use App\Models\Lesson;
use App\Services\Content\QuestionVocabularyImporter;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class EnglishContentSeeder extends Seeder
{
    public function run(): void
    {
        $importer = app(QuestionVocabularyImporter::class);

        foreach (require __DIR__.'/data/english.php' as $title => $content) {
            $lessons = Lesson::where('title', $title)
                ->whereHas(
                    'unit.course.language',
                    fn ($query) => $query->where('name', 'English')
                )
                ->get();

            if ($lessons->count() !== 1) {
                $this->command?->warn(
                    "English content skipped: expected one English lesson titled [{$title}], found {$lessons->count()}."
                );

                continue;
            }

            $lesson = $lessons->sole();

            DB::transaction(function () use ($lesson, $content, $importer) {
                foreach (
                    $content['vocabulary']
                    as [$word, $pronunciation, $meaning, $example, $examplePronunciation, $exampleMeaning]
                ) {
                    $lesson->vocabularies()->updateOrCreate(
                        ['word' => $word],
                        [
                            'pinyin' => $pronunciation,
                            'meaning' => $meaning,
                            'example_sentence' => $example,
                            'example_pinyin' => $examplePronunciation,
                            'example_meaning' => $exampleMeaning,
                        ]
                    );
                }

                foreach ($content['exercises'] as $type => $items) {
                    $exercise = $lesson->exercises()->firstOrCreate([
                        'type' => $type,
                        'title' => 'English MVP: '.$type,
                    ]);

                    if (isset($items['question'])) {
                        $items = [$items];
                    }

                    foreach ($items as $item) {
                        $question = $exercise->questions()->updateOrCreate(
                            ['question' => $item['question']],
                            [
                                'explanation' => $item['explanation'] ?? null,
                                'audio_path' => $item['audio_path'] ?? null,
                                'image_path' => $item['image_path'] ?? null,
                            ]
                        );

                        foreach ($item['answers'] as [$answer, $correct]) {
                            $question->answers()->updateOrCreate(
                                ['answer' => $answer],
                                ['is_correct' => $correct]
                            );
                        }

                        $importer->apply($question, $item);
                    }
                }
            });
        }
    }
}