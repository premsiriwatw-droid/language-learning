<?php

namespace Database\Seeders;

use App\Models\Lesson;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class ChineseQuestionVocabularySeeder extends Seeder
{
    public function run(): void
    {
        // แต่ละรายการ:
        // [ประเภท, ข้อความคำถาม, คำศัพท์ที่ต้องเรียนก่อน]
        $mappings = [
            'Greetings' => [
                [
                    'multiple_choice',
                    '谢谢 แปลว่าอะไร?',
                    ['谢谢'],
                ],
                [
                    'multiple_choice',
                    '对不起 แปลว่าอะไร?',
                    ['对不起'],
                ],
                [
                    'multiple_choice',
                    '晚安 ใช้พูดในความหมายใด?',
                    ['晚安'],
                ],
                [
                    'multiple_choice',
                    'ถ้ามีคนพูด 谢谢 ควรตอบว่าอะไร?',
                    ['谢谢', '不客气'],
                ],
                [
                    'fill_blank',
                    'เติมคำทักทาย: 你___！',
                    ['你好'],
                ],
                [
                    'fill_blank',
                    'เติมคำ: 明天___！',
                    ['明天见'],
                ],
                [
                    'fill_blank',
                    'เติมคำ: ___关系。',
                    ['没关系'],
                ],
                [
                    'fill_blank',
                    'เติมคำ: ___上好！ ใช้ทักทายตอนเช้า',
                    ['早上好'],
                ],
                [
                    'listening',
                    'คุณได้ยินคำว่าอะไร?',
                    ['你好'],
                ],
                [
                    'image_choice',
                    'เลือกคำที่ตรงกับภาพ',
                    ['再见'],
                ],
            ],

            'Self Introduction' => [
                [
                    'multiple_choice',
                    '我 แปลว่าอะไร?',
                    ['我'],
                ],
                [
                    'multiple_choice',
                    '老师 แปลว่าอะไร?',
                    ['老师'],
                ],
                [
                    'multiple_choice',
                    '名字 แปลว่าอะไร?',
                    ['名字'],
                ],
                [
                    'multiple_choice',
                    '泰国 หมายถึงประเทศใด?',
                    ['泰国'],
                ],
                [
                    'fill_blank',
                    'เติมคำ: 我___学生。',
                    ['我', '是', '学生'],
                ],
                [
                    'fill_blank',
                    'เติมคำ: 我___小明。',
                    ['我', '叫'],
                ],
                [
                    'fill_blank',
                    'เติมคำ: 你叫什么___？',
                    ['你', '叫', '什么', '名字'],
                ],
                [
                    'fill_blank',
                    'เติมคำ: ___是老师。 เมื่อต้องการพูดว่า “เธอเป็นครู”',
                    ['她', '是', '老师'],
                ],
                [
                    'listening',
                    'คุณได้ยินคำว่าอะไร?',
                    ['老师'],
                ],
                [
                    'image_choice',
                    'เลือกคำที่ตรงกับภาพ',
                    ['老师'],
                ],
            ],

            'Numbers' => [
                [
                    'multiple_choice',
                    '三 คือเลขอะไร?',
                    ['三'],
                ],
                [
                    'multiple_choice',
                    '九 คือเลขอะไร?',
                    ['九'],
                ],
                [
                    'multiple_choice',
                    '十 คือเลขอะไร?',
                    ['十'],
                ],
                [
                    'multiple_choice',
                    '零 คือเลขอะไร?',
                    ['零'],
                ],
                [
                    'fill_blank',
                    'เติมตัวเลข: 一、二、___、四',
                    ['一', '二', '三', '四'],
                ],
                [
                    'fill_blank',
                    'เติมตัวเลข: 六、七、___、九',
                    ['六', '七', '八', '九'],
                ],
                [
                    'fill_blank',
                    'เติมตัวเลข: 七、八、九、___',
                    ['七', '八', '九', '十'],
                ],
                [
                    'fill_blank',
                    'เติมคำ: ___个人。 หมายถึง “สองคน”',
                    ['两', '个'],
                ],
                [
                    'listening',
                    'คุณได้ยินตัวเลขอะไร?',
                    ['五'],
                ],
                [
                    'image_choice',
                    'ในภาพมีแอปเปิลกี่ลูก?',
                    ['三'],
                ],
            ],
        ];

        $linkedQuestions = 0;

        // ถ้าพบข้อมูลไม่ตรง จะย้อนกลับการผูกทั้งหมดของการรันนี้
        DB::transaction(function () use ($mappings, &$linkedQuestions) {
            foreach ($mappings as $lessonTitle => $items) {
                $lessons = Lesson::query()
                    ->where('title', $lessonTitle)
                    ->whereHas(
                        'unit.course.language',
                        fn ($query) => $query->where('name', 'Chinese')
                    )
                    ->get();

                if ($lessons->count() !== 1) {
                    throw new RuntimeException(
                        "ต้องมีบทภาษาจีนชื่อ [{$lessonTitle}] เพียงหนึ่งบท "
                        . "แต่พบ {$lessons->count()} บท"
                    );
                }

                $lesson = $lessons->sole();

                foreach ($items as [$type, $questionText, $words]) {
                    // ค้นหาคำถามใน Exercise ที่ ChineseContentSeeder สร้าง
                    $exercises = $lesson->exercises()
                        ->where('type', $type)
                        ->where('title', 'Chinese MVP: ' . $type)
                        ->get();

                    if ($exercises->count() !== 1) {
                        throw new RuntimeException(
                            "ไม่พบ Exercise ที่ตรงเพียงหนึ่งรายการ: "
                            . "[{$lessonTitle}] [{$type}]"
                        );
                    }

                    $questions = $exercises->sole()
                        ->questions()
                        ->where('question', $questionText)
                        ->get();

                    if ($questions->count() !== 1) {
                        throw new RuntimeException(
                            "ไม่พบคำถามที่ตรงเพียงหนึ่งข้อ: "
                            . "[{$lessonTitle}] [{$questionText}]"
                        );
                    }

                    $vocabularyIds = [];

                    foreach ($words as $word) {
                        // ใช้เฉพาะคำศัพท์ของบทเดียวกัน
                        $vocabularies = $lesson->vocabularies()
                            ->where('word', $word)
                            ->get();

                        if ($vocabularies->count() !== 1) {
                            throw new RuntimeException(
                                "ไม่พบคำศัพท์ที่ตรงเพียงหนึ่งคำ: "
                                . "[{$lessonTitle}] [{$word}]"
                            );
                        }

                        $vocabularyIds[] = $vocabularies->sole()->id;
                    }

                    // รันซ้ำได้ ไม่สร้างความสัมพันธ์ซ้ำ
                    $questions->sole()
                        ->vocabularies()
                        ->sync($vocabularyIds);

                    $linkedQuestions++;
                }
            }
        });

        $this->command?->info(
            "ผูกคำถามกับคำศัพท์เรียบร้อย {$linkedQuestions} ข้อ"
        );
    }
}