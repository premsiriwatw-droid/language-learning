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
                $this->command?->warn("Chinese content skipped: expected one Chinese lesson titled [{$title}], found {$lessons->count()}.");

                continue;
            }

            $lesson = $lessons->sole();

            DB::transaction(function () use ($lesson, $content) {
                foreach ($content['vocabulary'] as [$word, $pinyin, $meaning, $example, $examplePinyin, $exampleMeaning]) {
                    $lesson->vocabularies()->updateOrCreate(['word' => $word], [
                        'pinyin' => $pinyin,
                        'meaning' => $meaning,
                        'example_sentence' => $example,
                        'example_pinyin' => $examplePinyin,
                        'example_meaning' => $exampleMeaning,
                    ]);
                }

                foreach ($content['exercises'] as $type => $item) {
                    $exercise = $lesson->exercises()->firstOrCreate([
                        'type' => $type,
                        'title' => 'Chinese MVP: '.$type,
                    ]);
                    $question = $exercise->questions()->updateOrCreate(['question' => $item['question']], [
                        'explanation' => $item['explanation'],
                        'audio_path' => $item['audio_path'] ?? null,
                        'image_path' => $item['image_path'] ?? null,
                    ]);

                    foreach ($item['answers'] as [$answer, $correct]) {
                        $question->answers()->updateOrCreate(['answer' => $answer], ['is_correct' => $correct]);
                    }
                }
            });
        }
    }
}
