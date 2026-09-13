<?php

return [
    'Greetings' => [
        'vocabulary' => [
            ['你好', 'nǐ hǎo', 'สวัสดี', '你好！', 'Nǐ hǎo!', 'สวัสดี!'],
            ['谢谢', 'xièxie', 'ขอบคุณ', '谢谢你！', 'Xièxie nǐ!', 'ขอบคุณ!'],
            ['再见', 'zàijiàn', 'ลาก่อน', '老师，再见！', 'Lǎoshī, zàijiàn!', 'คุณครู ลาก่อน!'],
            ['对不起', 'duìbuqǐ', 'ขอโทษ', '对不起！', 'Duìbuqǐ!', 'ขอโทษ!'],
            ['没关系', 'méi guānxi', 'ไม่เป็นไร', '没关系。', 'Méi guānxi.', 'ไม่เป็นไร'],
            ['早上好', 'zǎoshang hǎo', 'สวัสดีตอนเช้า', '老师，早上好！', 'Lǎoshī, zǎoshang hǎo!', 'สวัสดีตอนเช้า คุณครู!'],
        ],
        'exercises' => [
            'multiple_choice' => [
                'question' => '谢谢 แปลว่าอะไร?',
                'explanation' => '谢谢 (xièxie) แปลว่า ขอบคุณ',
                'answers' => [['สวัสดี', false], ['ขอบคุณ', true], ['ลาก่อน', false], ['ขอโทษ', false]],
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
                'question' => 'ฟังเสียงแล้วพิมพ์คำทักทายที่ได้ยิน',
                'explanation' => '你好 (nǐ hǎo) แปลว่า สวัสดี',
                'audio_path' => 'audio/chinese/greetings/ni-hao.mp3',
                'answers' => [['你好', true]],
            ],
            'image_choice' => [
                'question' => 'ภาพคนโบกมือลาจากกันตรงกับคำใด?',
                'explanation' => '再见 (zàijiàn) ใช้กล่าวลา',
                'image_path' => 'images/chinese/greetings/goodbye.jpg',
                'answers' => [['谢谢', false], ['再见', true], ['对不起', false], ['早上好', false]],
            ],
        ],
    ],
    'Self Introduction' => [
        'vocabulary' => [
            ['我', 'wǒ', 'ฉัน', '我是学生。', 'Wǒ shì xuésheng.', 'ฉันเป็นนักเรียน'],
            ['你', 'nǐ', 'คุณ', '你是老师吗？', 'Nǐ shì lǎoshī ma?', 'คุณเป็นครูไหม?'],
            ['叫', 'jiào', 'ชื่อว่า', '我叫小明。', 'Wǒ jiào Xiǎomíng.', 'ฉันชื่อเสี่ยวหมิง'],
            ['是', 'shì', 'เป็น / คือ', '我是老师。', 'Wǒ shì lǎoshī.', 'ฉันเป็นครู'],
            ['学生', 'xuésheng', 'นักเรียน', '你是学生吗？', 'Nǐ shì xuésheng ma?', 'คุณเป็นนักเรียนไหม?'],
            ['老师', 'lǎoshī', 'ครู', '老师，你好！', 'Lǎoshī, nǐ hǎo!', 'สวัสดีคุณครู!'],
        ],
        'exercises' => [
            'multiple_choice' => [
                'question' => '我 แปลว่าอะไร?',
                'explanation' => '我 (wǒ) แปลว่า ฉัน',
                'answers' => [['คุณ', false], ['ครู', false], ['ฉัน', true], ['นักเรียน', false]],
            ],
            'fill_blank' => [
                'question' => 'เติมคำให้หมายถึง ฉันเป็นนักเรียน: 我___学生。',
                'explanation' => '我是学生。 (Wǒ shì xuésheng.) แปลว่า ฉันเป็นนักเรียน',
                'answers' => [
                    ['是', true],
                    ['叫', false],
                    ['我', false],
                    ['你', false],
                ],
            ],
            'listening' => [
                'question' => 'ฟังเสียงแล้วพิมพ์คำที่ได้ยิน',
                'explanation' => '老师 (lǎoshī) แปลว่า ครู',
                'audio_path' => 'audio/chinese/self-introduction/lao-shi.mp3',
                'answers' => [['老师', true]],
            ],
            'image_choice' => [
                'question' => 'ภาพครูกำลังสอนตรงกับคำใด?',
                'explanation' => '老师 (lǎoshī) แปลว่า ครู',
                'image_path' => 'images/chinese/self-introduction/teacher.jpg',
                'answers' => [['学生', false], ['老师', true], ['我', false], ['你', false]],
            ],
        ],
    ],
    'Numbers' => [
        'vocabulary' => [
            ['一', 'yī', 'หนึ่ง', '一、二、三。', 'Yī, èr, sān.', 'หนึ่ง สอง สาม'],
            ['二', 'èr', 'สอง', null, null, null],
            ['三', 'sān', 'สาม', null, null, null],
            ['四', 'sì', 'สี่', null, null, null],
            ['五', 'wǔ', 'ห้า', null, null, null],
            ['六', 'liù', 'หก', null, null, null],
            ['七', 'qī', 'เจ็ด', null, null, null],
            ['八', 'bā', 'แปด', null, null, null],
        ],
        'exercises' => [
            'multiple_choice' => [
                'question' => '三 คือเลขอะไร?',
                'explanation' => '三 (sān) คือเลขสาม',
                'answers' => [['หนึ่ง', false], ['สอง', false], ['สาม', true], ['สี่', false]],
            ],
            'fill_blank' => [
                'question' => 'เติมตัวเลขที่หายไป: 一、二、___、四。',
                'explanation' => '三 (sān) อยู่ระหว่าง 二 และ 四',
                'answers' => [
                    ['三', true],
                    ['一', false],
                    ['五', false],
                    ['八', false],
                ],
            ],
            'listening' => [
                'question' => 'ฟังเสียงแล้วพิมพ์ตัวเลขที่ได้ยินเป็นภาษาจีน',
                'explanation' => '五 (wǔ) คือเลขห้า',
                'audio_path' => 'audio/chinese/numbers/wu.mp3',
                'answers' => [['五', true]],
            ],
            'image_choice' => [
                'question' => 'เลือกตัวเลขภาษาจีนที่ตรงกับจำนวนแอปเปิลในภาพ',
                'explanation' => 'ภาพแอปเปิลสามลูกตรงกับ 三 (sān)',
                'image_path' => 'images/chinese/numbers/three-apples.jpg',
                'answers' => [['一', false], ['二', false], ['三', true], ['四', false]],
            ],
        ],
    ],
];
