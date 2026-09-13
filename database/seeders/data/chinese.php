<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Greetings
    |--------------------------------------------------------------------------
    */

    'Greetings' => [

        'vocabulary' => [

            [
                '你好',
                'nǐ hǎo',
                'สวัสดี',
                '你好！',
                'Nǐ hǎo!',
                'สวัสดี!',
            ],

            [
                '谢谢',
                'xièxie',
                'ขอบคุณ',
                '谢谢你！',
                'Xièxie nǐ!',
                'ขอบคุณ!',
            ],

            [
                '再见',
                'zàijiàn',
                'ลาก่อน',
                '老师，再见！',
                'Lǎoshī, zàijiàn!',
                'คุณครู ลาก่อน!',
            ],

            [
                '对不起',
                'duìbuqǐ',
                'ขอโทษ',
                '对不起！',
                'Duìbuqǐ!',
                'ขอโทษ!',
            ],

            [
                '没关系',
                'méi guānxi',
                'ไม่เป็นไร',
                '没关系。',
                'Méi guānxi.',
                'ไม่เป็นไร',
            ],

            [
                '早上好',
                'zǎoshang hǎo',
                'สวัสดีตอนเช้า',
                '老师，早上好！',
                'Lǎoshī, zǎoshang hǎo!',
                'คุณครู สวัสดีตอนเช้า!',
            ],
        ],

        'exercises' => [

            'multiple_choice' => [
                'question' => '谢谢 แปลว่าอะไร?',
                'explanation' => '谢谢 (xièxie) แปลว่า ขอบคุณ',
                'answers' => [
                    ['สวัสดี', false],
                    ['ขอบคุณ', true],
                    ['ลาก่อน', false],
                    ['ขอโทษ', false],
                ],
            ],

            'fill_blank' => [
                'question' => 'เติมคำทักทาย: 你___！',
                'explanation' => '你好 (nǐ hǎo) ใช้กล่าวสวัสดี',
                'answers' => [
                    ['好', true],
                    ['谢', false],
                    ['再', false],
                    ['对', false],
                ],
            ],

            'listening' => [
                'question' => 'คุณได้ยินคำว่าอะไร?',
                'explanation' => '你好 (nǐ hǎo) แปลว่า สวัสดี',
                'audio_path' => 'audio/chinese/greetings/ni-hao.mp3',
                'answers' => [
                    ['你好', true],
                    ['谢谢', false],
                    ['再见', false],
                    ['对不起', false],
                ],
            ],

            'image_choice' => [
                'question' => 'เลือกคำที่ตรงกับภาพ',
                'explanation' => '再见 (zàijiàn) ใช้กล่าวลา',
                'image_path' => 'images/chinese/greetings/goodbye.jpg',
                'answers' => [
                    ['你好', false],
                    ['谢谢', false],
                    ['再见', true],
                    ['对不起', false],
                ],
            ],
        ],
    ],


    /*
    |--------------------------------------------------------------------------
    | Self Introduction
    |--------------------------------------------------------------------------
    */

    'Self Introduction' => [

        'vocabulary' => [

            [
                '我',
                'wǒ',
                'ฉัน',
                '我是学生。',
                'Wǒ shì xuésheng.',
                'ฉันเป็นนักเรียน',
            ],

            [
                '你',
                'nǐ',
                'คุณ',
                '你是老师吗？',
                'Nǐ shì lǎoshī ma?',
                'คุณเป็นครูไหม?',
            ],

            [
                '叫',
                'jiào',
                'ชื่อว่า',
                '我叫小明。',
                'Wǒ jiào Xiǎomíng.',
                'ฉันชื่อเสี่ยวหมิง',
            ],

            [
                '是',
                'shì',
                'เป็น / คือ',
                '我是学生。',
                'Wǒ shì xuésheng.',
                'ฉันเป็นนักเรียน',
            ],

            [
                '学生',
                'xuésheng',
                'นักเรียน',
                '我是学生。',
                'Wǒ shì xuésheng.',
                'ฉันเป็นนักเรียน',
            ],

            [
                '老师',
                'lǎoshī',
                'ครู',
                '她是老师。',
                'Tā shì lǎoshī.',
                'เธอเป็นครู',
            ],
        ],

        'exercises' => [

            'multiple_choice' => [
                'question' => '我 แปลว่าอะไร?',
                'explanation' => '我 (wǒ) แปลว่า ฉัน',
                'answers' => [
                    ['ฉัน', true],
                    ['คุณ', false],
                    ['ครู', false],
                    ['นักเรียน', false],
                ],
            ],

            'fill_blank' => [
                'question' => 'เติมคำ: 我___学生。',
                'explanation' => '我是学生。 แปลว่า ฉันเป็นนักเรียน',
                'answers' => [
                    ['是', true],
                    ['叫', false],
                    ['我', false],
                    ['你', false],
                ],
            ],

            'listening' => [
                'question' => 'คุณได้ยินคำว่าอะไร?',
                'explanation' => '老师 (lǎoshī) แปลว่า ครู',
                'audio_path' => 'audio/chinese/self-introduction/lao-shi.mp3',
                'answers' => [
                    ['老师', true],
                    ['学生', false],
                    ['我', false],
                    ['你', false],
                ],
            ],

            'image_choice' => [
                'question' => 'เลือกคำที่ตรงกับภาพ',
                'explanation' => '老师 (lǎoshī) แปลว่า ครู',
                'image_path' => 'images/chinese/self-introduction/teacher.jpg',
                'answers' => [
                    ['老师', true],
                    ['学生', false],
                    ['我', false],
                    ['你', false],
                ],
            ],
        ],
    ],


    /*
    |--------------------------------------------------------------------------
    | Numbers
    |--------------------------------------------------------------------------
    */

    'Numbers' => [

        'vocabulary' => [

            [
                '一',
                'yī',
                '1',
                '一个人。',
                'Yí ge rén.',
                'หนึ่งคน',
            ],

            [
                '二',
                'èr',
                '2',
                '两个人。',
                'Liǎng ge rén.',
                'สองคน',
            ],

            [
                '三',
                'sān',
                '3',
                '三个苹果。',
                'Sān ge píngguǒ.',
                'แอปเปิลสามลูก',
            ],

            [
                '四',
                'sì',
                '4',
                '四本书。',
                'Sì běn shū.',
                'หนังสือสี่เล่ม',
            ],

            [
                '五',
                'wǔ',
                '5',
                '五个人。',
                'Wǔ ge rén.',
                'ห้าคน',
            ],

            [
                '六',
                'liù',
                '6',
                '六杯水。',
                'Liù bēi shuǐ.',
                'น้ำหกแก้ว',
            ],

            [
                '七',
                'qī',
                '7',
                '七天。',
                'Qī tiān.',
                'เจ็ดวัน',
            ],

            [
                '八',
                'bā',
                '8',
                '八本书。',
                'Bā běn shū.',
                'หนังสือแปดเล่ม',
            ],
        ],

        'exercises' => [

            'multiple_choice' => [
                'question' => '三 คือเลขอะไร?',
                'explanation' => '三 (sān) คือเลข 3',
                'answers' => [
                    ['1', false],
                    ['2', false],
                    ['3', true],
                    ['4', false],
                ],
            ],

            'fill_blank' => [
                'question' => 'เติมตัวเลข: 一、二、___、四',
                'explanation' => 'ลำดับคือ 一、二、三、四',
                'answers' => [
                    ['三', true],
                    ['一', false],
                    ['五', false],
                    ['八', false],
                ],
            ],

            'listening' => [
                'question' => 'คุณได้ยินตัวเลขอะไร?',
                'explanation' => '五 (wǔ) คือเลข 5',
                'audio_path' => 'audio/chinese/numbers/wu.mp3',
                'answers' => [
                    ['五', true],
                    ['三', false],
                    ['六', false],
                    ['八', false],
                ],
            ],

            'image_choice' => [
                'question' => 'ในภาพมีแอปเปิลกี่ลูก?',
                'explanation' => '三 (sān) คือเลข 3',
                'image_path' => 'images/chinese/numbers/three-apples.jpg',
                'answers' => [
                    ['一', false],
                    ['二', false],
                    ['三', true],
                    ['四', false],
                ],
            ],
        ],
    ],
];