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

            [
                '晚上好',
                'wǎnshang hǎo',
                'สวัสดีตอนเย็น',
                '大家晚上好！',
                'Dàjiā wǎnshang hǎo!',
                'สวัสดีตอนเย็นทุกคน!',
            ],

            [
                '晚安',
                'wǎn\'ān',
                'ราตรีสวัสดิ์',
                '晚安，明天见！',
                'Wǎn\'ān, míngtiān jiàn!',
                'ราตรีสวัสดิ์ เจอกันพรุ่งนี้!',
            ],

            [
                '请',
                'qǐng',
                'กรุณา / เชิญ',
                '请坐。',
                'Qǐng zuò.',
                'เชิญนั่ง',
            ],

            [
                '不客气',
                'bú kèqi',
                'ไม่เป็นไร / ด้วยความยินดี',
                '不客气！',
                'Bú kèqi!',
                'ด้วยความยินดี!',
            ],

            [
                '明天见',
                'míngtiān jiàn',
                'เจอกันพรุ่งนี้',
                '再见，明天见！',
                'Zàijiàn, míngtiān jiàn!',
                'ลาก่อน เจอกันพรุ่งนี้!',
            ],

            [
                '欢迎',
                'huānyíng',
                'ยินดีต้อนรับ',
                '欢迎你！',
                'Huānyíng nǐ!',
                'ยินดีต้อนรับคุณ!',
            ],
        ],

        'exercises' => [

            'multiple_choice' => [

                [
                    'question' => '谢谢 แปลว่าอะไร?',
                    'explanation' => '谢谢 (xièxie) แปลว่า ขอบคุณ',
                    'answers' => [
                        ['สวัสดี', false],
                        ['ขอบคุณ', true],
                        ['ลาก่อน', false],
                        ['ขอโทษ', false],
                    ],
                ],

                [
                    'question' => '对不起 แปลว่าอะไร?',
                    'explanation' => '对不起 (duìbuqǐ) แปลว่า ขอโทษ',
                    'answers' => [
                        ['ขอบคุณ', false],
                        ['ขอโทษ', true],
                        ['ไม่เป็นไร', false],
                        ['ยินดีต้อนรับ', false],
                    ],
                ],

                [
                    'question' => '晚安 ใช้พูดในความหมายใด?',
                    'explanation' => '晚安 (wǎn\'ān) ใช้กล่าวราตรีสวัสดิ์',
                    'answers' => [
                        ['สวัสดีตอนเช้า', false],
                        ['ราตรีสวัสดิ์', true],
                        ['ขอบคุณ', false],
                        ['ลาก่อน', false],
                    ],
                ],

                [
                    'question' => 'ถ้ามีคนพูด 谢谢 ควรตอบว่าอะไร?',
                    'explanation' => '不客气 (bú kèqi) ใช้ตอบรับคำขอบคุณ หมายถึง ด้วยความยินดี',
                    'answers' => [
                        ['不客气', true],
                        ['对不起', false],
                        ['早上好', false],
                        ['再见', false],
                    ],
                ],

                [
                    'question' => '晚上好 แปลว่าอะไร?',
                    'explanation' => '晚上好 (wǎnshang hǎo) แปลว่า สวัสดีตอนเย็น',
                    'answers' => [
                        ['สวัสดีตอนเช้า', false],
                        ['สวัสดีตอนเย็น', true],
                        ['ราตรีสวัสดิ์', false],
                        ['เจอกันพรุ่งนี้', false],
                    ],
                ],

                [
                    'question' => '请 แปลว่าอะไร?',
                    'explanation' => '请 (qǐng) แปลว่า กรุณา หรือ เชิญ',
                    'answers' => [
                        ['กรุณา / เชิญ', true],
                        ['ขอบคุณ', false],
                        ['ลาก่อน', false],
                        ['ไม่เป็นไร', false],
                    ],
                ],

                [
                    'question' => '欢迎 แปลว่าอะไร?',
                    'explanation' => '欢迎 (huānyíng) แปลว่า ยินดีต้อนรับ',
                    'answers' => [
                        ['ยินดีต้อนรับ', true],
                        ['ขอโทษ', false],
                        ['ราตรีสวัสดิ์', false],
                        ['เจอกันพรุ่งนี้', false],
                    ],
                ],
                [
                    'question' => 'คุณเผลอชนเพื่อนแล้วพูดว่า 对不起 เพื่อนตอบว่าไม่เป็นไร ควรเลือกคำใด?',
                    'answers' => [
                        ['欢迎', false],
                        ['晚安', false],
                        ['没关系', true],
                        ['不客气', false],
                    ],
                    'explanation' => '没关系 ใช้ตอบคำขอโทษว่าไม่เป็นไร ส่วน 不客气 ใช้ตอบคำขอบคุณ',
                ],
            ],

            'fill_blank' => [

                [
                    'question' => 'เติมคำทักทาย: 你___！',
                    'explanation' => '你好 (nǐ hǎo) ใช้กล่าวสวัสดี',
                    'answers' => [
                        ['好', true],
                        ['谢', false],
                        ['再', false],
                        ['对', false],
                    ],
                ],

                [
                    'question' => 'เติมคำ: 明天___！',
                    'explanation' => '明天见 (míngtiān jiàn) แปลว่า เจอกันพรุ่งนี้',
                    'answers' => [
                        ['见', true],
                        ['好', false],
                        ['请', false],
                        ['谢', false],
                    ],
                ],

                [
                    'question' => 'เติมคำ: ___关系。',
                    'explanation' => '没关系 (méi guānxi) แปลว่า ไม่เป็นไร',
                    'answers' => [
                        ['没', true],
                        ['不', false],
                        ['请', false],
                        ['再', false],
                    ],
                ],

                [
                    'question' => 'เติมคำ: ___上好！ ใช้ทักทายตอนเช้า',
                    'explanation' => '早上好 (zǎoshang hǎo) แปลว่า สวัสดีตอนเช้า',
                    'answers' => [
                        ['早', true],
                        ['晚', false],
                        ['明', false],
                        ['再', false],
                    ],
                ],

                [
                    'question' => 'เติมคำ: ___客气！ ใช้ตอบรับคำขอบคุณ',
                    'explanation' => '不客气 (bú kèqi) ใช้ตอบรับคำขอบคุณ',
                    'answers' => [
                        ['不', true],
                        ['没', false],
                        ['再', false],
                        ['请', false],
                    ],
                ],

                [
                    'question' => 'เติมคำ: ___迎你！ หมายถึง “ยินดีต้อนรับคุณ”',
                    'explanation' => '欢迎你！ (Huānyíng nǐ!) แปลว่า ยินดีต้อนรับคุณ',
                    'answers' => [
                        ['欢', true],
                        ['早', false],
                        ['晚', false],
                        ['明', false],
                    ],
                ],
            ],

            'listening' => [

                [
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
                [
                    'question' => 'ฟังเสียงแล้วเลือกว่าผู้พูดนัดเจอกันอีกเมื่อไร',
                    'answers' => [
                        ['晚上见', false],
                        ['早上见', false],
                        ['明天见', true],
                        ['今天见', false],
                    ],
                    'explanation' => 'เสียงพูดว่า 再见，明天见！ ผู้พูดกล่าวลาและนัดเจอกันพรุ่งนี้',
                    'audio_path' => 'audio/chinese/greetings/zai-jian-mingtian-jian.mp3',
                    'audio_script' => '再见，明天见！',
                    'audio_pinyin' => 'Zàijiàn, míngtiān jiàn!',
                    'audio_meaning' => 'ลาก่อน เจอกันพรุ่งนี้',
                ],
            ],

            'image_choice' => [

                [
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
                [
                    'question' => 'ภาพเป็นช่วงเช้า นักเรียนควรใช้คำใดทักทายครู?',
                    'answers' => [
                        ['再见', false],
                        ['早上好', true],
                        ['晚上好', false],
                        ['晚安', false],
                    ],
                    'explanation' => '早上好 เป็นคำทักทายตอนเช้า ใช้เมื่อพบครูในช่วงเริ่มวัน',
                    'image_path' => 'images/chinese/greetings/morning-greeting.jpg',
                    'image_description' => 'นักเรียนพบครูที่ประตูโรงเรียนในตอนเช้า ยิ้มและโค้งทักทาย แสงอาทิตย์เช้าชัดเจน หลีกเลี่ยงท่าโบกมือลา ไม่มีข้อความ ป้าย ตัวเลข บอลลูนบทสนทนา หรือคำตอบ',
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

            [
                '名字',
                'míngzi',
                'ชื่อ',
                '你叫什么名字？',
                'Nǐ jiào shénme míngzi?',
                'คุณชื่ออะไร?',
            ],

            [
                '什么',
                'shénme',
                'อะไร',
                '你叫什么名字？',
                'Nǐ jiào shénme míngzi?',
                'คุณชื่ออะไร?',
            ],

            [
                '他',
                'tā',
                'เขา (ผู้ชาย)',
                '他是学生。',
                'Tā shì xuésheng.',
                'เขาเป็นนักเรียน',
            ],

            [
                '她',
                'tā',
                'เธอ (ผู้หญิง)',
                '她是老师。',
                'Tā shì lǎoshī.',
                'เธอเป็นครู',
            ],

            [
                '来自',
                'láizì',
                'มาจาก',
                '我来自中国。',
                'Wǒ láizì Zhōngguó.',
                'ฉันมาจากประเทศจีน',
            ],

            [
                '泰国',
                'Tàiguó',
                'ประเทศไทย',
                '我来自泰国。',
                'Wǒ láizì Tàiguó.',
                'ฉันมาจากประเทศไทย',
            ],
        ],

        'exercises' => [

            'multiple_choice' => [

                [
                    'question' => '我 แปลว่าอะไร?',
                    'explanation' => '我 (wǒ) แปลว่า ฉัน',
                    'answers' => [
                        ['ฉัน', true],
                        ['คุณ', false],
                        ['ครู', false],
                        ['นักเรียน', false],
                    ],
                ],

                [
                    'question' => '老师 แปลว่าอะไร?',
                    'explanation' => '老师 (lǎoshī) แปลว่า ครู',
                    'answers' => [
                        ['นักเรียน', false],
                        ['ครู', true],
                        ['ชื่อ', false],
                        ['ประเทศ', false],
                    ],
                ],

                [
                    'question' => '名字 แปลว่าอะไร?',
                    'explanation' => '名字 (míngzi) แปลว่า ชื่อ',
                    'answers' => [
                        ['ชื่อ', true],
                        ['อะไร', false],
                        ['คุณ', false],
                        ['ฉัน', false],
                    ],
                ],

                [
                    'question' => '泰国 หมายถึงประเทศใด?',
                    'explanation' => '泰国 (Tàiguó) หมายถึง ประเทศไทย',
                    'answers' => [
                        ['ประเทศจีน', false],
                        ['ประเทศไทย', true],
                        ['ประเทศญี่ปุ่น', false],
                        ['ประเทศเกาหลี', false],
                    ],
                ],

                [
                    'question' => '你 แปลว่าอะไร?',
                    'explanation' => '你 (nǐ) แปลว่า คุณ',
                    'answers' => [
                        ['ฉัน', false],
                        ['คุณ', true],
                        ['เขา', false],
                        ['เธอ', false],
                    ],
                ],

                [
                    'question' => '学生 แปลว่าอะไร?',
                    'explanation' => '学生 (xuésheng) แปลว่า นักเรียน',
                    'answers' => [
                        ['นักเรียน', true],
                        ['ครู', false],
                        ['ชื่อ', false],
                        ['ประเทศจีน', false],
                    ],
                ],

                [
                    'question' => '什么 แปลว่าอะไร?',
                    'explanation' => '什么 (shénme) แปลว่า อะไร',
                    'answers' => [
                        ['ใคร', false],
                        ['อะไร', true],
                        ['ที่ไหน', false],
                        ['เมื่อไร', false],
                    ],
                ],

                [
                    'question' => '来自 ในประโยค “我来自中国。” หมายถึงอะไร?',
                    'explanation' => '来自 (láizì) แปลว่า มาจาก ประโยค 我来自中国。 หมายถึง ฉันมาจากประเทศจีน',
                    'answers' => [
                        ['เป็น', false],
                        ['มาจาก', true],
                        ['ชื่อว่า', false],
                        ['อะไร', false],
                    ],
                ],
            ],

            'fill_blank' => [

                [
                    'question' => 'เติมคำ: 我___学生。',
                    'explanation' => '我是学生。 แปลว่า ฉันเป็นนักเรียน',
                    'answers' => [
                        ['是', true],
                        ['叫', false],
                        ['我', false],
                        ['你', false],
                    ],
                ],

                [
                    'question' => 'เติมคำ: 我___小明。',
                    'explanation' => '我叫小明。 แปลว่า ฉันชื่อเสี่ยวหมิง',
                    'answers' => [
                        ['叫', true],
                        ['是', false],
                        ['你', false],
                        ['他', false],
                    ],
                ],

                [
                    'question' => 'เติมคำ: 你叫什么___？',
                    'explanation' => '你叫什么名字？ แปลว่า คุณชื่ออะไร?',
                    'answers' => [
                        ['名字', true],
                        ['学生', false],
                        ['老师', false],
                        ['中国', false],
                    ],
                ],

                [
                    'question' => 'เติมคำ: ___是老师。 เมื่อต้องการพูดว่า “เธอเป็นครู”',
                    'explanation' => '她是老师。 แปลว่า เธอเป็นครู',
                    'answers' => [
                        ['她', true],
                        ['他', false],
                        ['我', false],
                        ['你', false],
                    ],
                ],

                [
                    'question' => 'เติมคำ: ___是学生。 เมื่อต้องการพูดว่า “เขาเป็นนักเรียน”',
                    'explanation' => '他是学生。 แปลว่า เขาเป็นนักเรียน',
                    'answers' => [
                        ['他', true],
                        ['她', false],
                        ['我', false],
                        ['你', false],
                    ],
                ],

                [
                    'question' => 'เติมคำ: 你叫___名字？',
                    'explanation' => '你叫什么名字？ แปลว่า คุณชื่ออะไร?',
                    'answers' => [
                        ['什么', true],
                        ['学生', false],
                        ['老师', false],
                        ['泰国', false],
                    ],
                ],
            ],

            'listening' => [

                [
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
                [
                    'question' => 'ฟังเสียงแล้วเลือกประเทศที่ผู้พูดมาจาก',
                    'answers' => [
                        ['韩国', false],
                        ['泰国', true],
                        ['中国', false],
                        ['日本', false],
                    ],
                    'explanation' => 'เสียงพูดว่า 我来自泰国。 ผู้พูดมาจากประเทศไทย',
                    'audio_path' => 'audio/chinese/self-introduction/wo-laizi-taiguo.mp3',
                    'audio_script' => '我来自泰国。',
                    'audio_pinyin' => 'Wǒ láizì Tàiguó.',
                    'audio_meaning' => 'ฉันมาจากประเทศไทย',
                ],
            ],

            'image_choice' => [

                [
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
                [
                    'question' => 'ดูภาพแล้วเลือกบทบาทของคนที่กำลังนั่งเรียน',
                    'answers' => [
                        ['老师', false],
                        ['爸爸', false],
                        ['妈妈', false],
                        ['学生', true],
                    ],
                    'explanation' => 'คนที่นั่งโต๊ะเรียนเปิดหนังสือเรียนในภาพมีบทบาทเป็น 学生 นักเรียน',
                    'image_path' => 'images/chinese/self-introduction/student-studying.jpg',
                    'image_description' => 'นักเรียนวัยรุ่นหนึ่งคนนั่งที่โต๊ะในห้องเรียน มีสมุดและหนังสือเปิดอยู่ กำลังจดโน้ต บริบทการเรียนชัดเจน ไม่มีครูหรือผู้ใหญ่อื่น ไม่มีตัวหนังสือที่อ่านได้บนกระดาน/หนังสือ ไม่มีป้าย ชื่อ หรือคำตอบ',
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
                'หนึ่ง (1)',
                '一个人。',
                'Yí ge rén.',
                'หนึ่งคน',
            ],

            [
                '二',
                'èr',
                'สอง (2)',
                '二月。',
                'Èr yuè.',
                'เดือนกุมภาพันธ์',
            ],

            [
                '三',
                'sān',
                'สาม (3)',
                '三个苹果。',
                'Sān ge píngguǒ.',
                'แอปเปิลสามลูก',
            ],

            [
                '四',
                'sì',
                'สี่ (4)',
                '四本书。',
                'Sì běn shū.',
                'หนังสือสี่เล่ม',
            ],

            [
                '五',
                'wǔ',
                'ห้า (5)',
                '五个人。',
                'Wǔ ge rén.',
                'ห้าคน',
            ],

            [
                '六',
                'liù',
                'หก (6)',
                '六杯水。',
                'Liù bēi shuǐ.',
                'น้ำหกแก้ว',
            ],

            [
                '七',
                'qī',
                'เจ็ด (7)',
                '七天。',
                'Qī tiān.',
                'เจ็ดวัน',
            ],

            [
                '八',
                'bā',
                'แปด (8)',
                '八本书。',
                'Bā běn shū.',
                'หนังสือแปดเล่ม',
            ],

            [
                '九',
                'jiǔ',
                'เก้า (9)',
                '九个人。',
                'Jiǔ ge rén.',
                'เก้าคน',
            ],

            [
                '十',
                'shí',
                'สิบ (10)',
                '十本书。',
                'Shí běn shū.',
                'หนังสือสิบเล่ม',
            ],

            [
                '零',
                'líng',
                'ศูนย์ (0)',
                '数字是零。',
                'Shùzì shì líng.',
                'ตัวเลขคือศูนย์',
            ],

            [
                '多少',
                'duōshao',
                'เท่าไร / กี่',
                '你有多少本书？',
                'Nǐ yǒu duōshao běn shū?',
                'คุณมีหนังสือกี่เล่ม?',
            ],

            [
                '个',
                'gè',
                'ลักษณนามทั่วไป',
                '三个人。',
                'Sān ge rén.',
                'สามคน',
            ],

            [
                '两',
                'liǎng',
                'สอง (ใช้หน้าลักษณนาม)',
                '两个人。',
                'Liǎng ge rén.',
                'สองคน',
            ],

            [
                '几',
                'jǐ',
                'กี่',
                '你几岁？',
                'Nǐ jǐ suì?',
                'คุณอายุเท่าไร?',
            ],
        ],

        'exercises' => [

            'multiple_choice' => [

                [
                    'question' => '三 คือเลขอะไร?',
                    'explanation' => '三 (sān) คือเลข 3',
                    'answers' => [
                        ['1', false],
                        ['2', false],
                        ['3', true],
                        ['4', false],
                    ],
                ],

                [
                    'question' => '九 คือเลขอะไร?',
                    'explanation' => '九 (jiǔ) คือเลข 9',
                    'answers' => [
                        ['6', false],
                        ['7', false],
                        ['8', false],
                        ['9', true],
                    ],
                ],

                [
                    'question' => '十 คือเลขอะไร?',
                    'explanation' => '十 (shí) คือเลข 10',
                    'answers' => [
                        ['7', false],
                        ['8', false],
                        ['9', false],
                        ['10', true],
                    ],
                ],

                [
                    'question' => '零 คือเลขอะไร?',
                    'explanation' => '零 (líng) คือเลข 0',
                    'answers' => [
                        ['0', true],
                        ['1', false],
                        ['2', false],
                        ['10', false],
                    ],
                ],

                [
                    'question' => '一 คือเลขอะไร?',
                    'explanation' => '一 (yī) คือเลข 1',
                    'answers' => [
                        ['1', true],
                        ['2', false],
                        ['3', false],
                        ['4', false],
                    ],
                ],

                [
                    'question' => '二 คือเลขอะไร?',
                    'explanation' => '二 (èr) คือเลข 2',
                    'answers' => [
                        ['1', false],
                        ['2', true],
                        ['3', false],
                        ['4', false],
                    ],
                ],

                [
                    'question' => '四 คือเลขอะไร?',
                    'explanation' => '四 (sì) คือเลข 4',
                    'answers' => [
                        ['2', false],
                        ['3', false],
                        ['4', true],
                        ['5', false],
                    ],
                ],

                [
                    'question' => '六 คือเลขอะไร?',
                    'explanation' => '六 (liù) คือเลข 6',
                    'answers' => [
                        ['5', false],
                        ['6', true],
                        ['7', false],
                        ['8', false],
                    ],
                ],

                [
                    'question' => '七 คือเลขอะไร?',
                    'explanation' => '七 (qī) คือเลข 7',
                    'answers' => [
                        ['5', false],
                        ['6', false],
                        ['7', true],
                        ['8', false],
                    ],
                ],

                [
                    'question' => '八 คือเลขอะไร?',
                    'explanation' => '八 (bā) คือเลข 8',
                    'answers' => [
                        ['6', false],
                        ['7', false],
                        ['8', true],
                        ['9', false],
                    ],
                ],

                [
                    'question' => '多少 แปลว่าอะไร?',
                    'explanation' => '多少 (duōshao) ใช้ถามจำนวนหรือปริมาณ หมายถึง เท่าไร หรือ กี่',
                    'answers' => [
                        ['เท่าไร / กี่', true],
                        ['หนึ่ง', false],
                        ['สอง', false],
                        ['สิบ', false],
                    ],
                ],

                [
                    'question' => '个 ทำหน้าที่อะไร?',
                    'explanation' => '个 (gè) เป็นลักษณนามทั่วไปในภาษาจีน',
                    'answers' => [
                        ['คำทักทาย', false],
                        ['ลักษณนามทั่วไป', true],
                        ['คำบอกลา', false],
                        ['คำขอบคุณ', false],
                    ],
                ],

                [
                    'question' => '几 แปลว่าอะไร?',
                    'explanation' => '几 (jǐ) แปลว่า กี่ และใช้ถามจำนวน',
                    'answers' => [
                        ['กี่', true],
                        ['ศูนย์', false],
                        ['สิบ', false],
                        ['สอง', false],
                    ],
                ],
            ],

            'fill_blank' => [

                [
                    'question' => 'เติมตัวเลข: 一、二、___、四',
                    'explanation' => 'ลำดับคือ 一、二、三、四',
                    'answers' => [
                        ['三', true],
                        ['一', false],
                        ['五', false],
                        ['八', false],
                    ],
                ],

                [
                    'question' => 'เติมตัวเลข: 六、七、___、九',
                    'explanation' => 'ลำดับคือ 六、七、八、九',
                    'answers' => [
                        ['八', true],
                        ['五', false],
                        ['九', false],
                        ['十', false],
                    ],
                ],

                [
                    'question' => 'เติมตัวเลข: 七、八、九、___',
                    'explanation' => 'หลัง 九 (9) คือ 十 (10)',
                    'answers' => [
                        ['十', true],
                        ['六', false],
                        ['八', false],
                        ['零', false],
                    ],
                ],

                [
                    'question' => 'เติมคำ: ___个人。 หมายถึง “สองคน”',
                    'explanation' => '两个人 (liǎng ge rén) แปลว่า สองคน',
                    'answers' => [
                        ['两', true],
                        ['二', false],
                        ['三', false],
                        ['十', false],
                    ],
                ],

                [
                    'question' => 'เติมคำ: 三___人。 หมายถึง “สามคน”',
                    'explanation' => '三个人 (sān ge rén) แปลว่า สามคน โดย 个 เป็นลักษณนาม',
                    'answers' => [
                        ['个', true],
                        ['几', false],
                        ['两', false],
                        ['十', false],
                    ],
                ],
            ],

            'listening' => [

                [
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
                [
                    'question' => 'ฟังเสียงแล้วเลือกจำนวนแอปเปิล',
                    'answers' => [
                        ['六个', true],
                        ['四个', false],
                        ['五个', false],
                        ['七个', false],
                    ],
                    'explanation' => 'เสียงพูดว่า 我有六个苹果。 คือฉันมีแอปเปิลหกลูก',
                    'audio_path' => 'audio/chinese/numbers/wo-you-liu-ge-pingguo.mp3',
                    'audio_script' => '我有六个苹果。',
                    'audio_pinyin' => 'Wǒ yǒu liù ge píngguǒ.',
                    'audio_meaning' => 'ฉันมีแอปเปิลหกลูก',
                ],
            ],

            'image_choice' => [

                [
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
                [
                    'question' => '图片中有几个苹果？',
                    'answers' => [
                        ['五个', false],
                        ['六个', false],
                        ['八个', false],
                        ['七个', true],
                    ],
                    'explanation' => 'นับแอปเปิลในภาพได้เจ็ดลูก จึงตอบ 七个',
                    'image_path' => 'images/chinese/numbers/seven-apples.jpg',
                    'image_description' => 'แอปเปิลเจ็ดลูกพอดีบนพื้นหลังเรียบ แยกวางแต่ละลูกชัดเจนไม่ซ้อนบังกัน ไม่มีภาพสะท้อนหรือผลไม้อื่น ไม่มีตัวเลข ตัวหนังสือ ป้าย สัญลักษณ์การนับ หรือคำตอบ',
                ],
            ],
        ],
    ],
];
