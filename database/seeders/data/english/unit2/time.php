<?php

return [
    'Time' => [
        'vocabulary' => [
            [
                'time',
                'ไทม์',
                'เวลา',
                'What time is it?',
                'วอต ไทม์ อิซ อิต',
                'ตอนนี้กี่โมง',
            ],
            [
                'clock',
                'คล็อก',
                'นาฬิกาตั้งโต๊ะหรือนาฬิกาแขวน',
                'The clock is on the wall.',
                'เดอะ คล็อก อิซ ออน เดอะ วอล',
                'นาฬิกาอยู่บนผนัง',
            ],
            [
                'watch',
                'วอตช์',
                'นาฬิกาข้อมือ',
                'This is my watch.',
                'ดิส อิซ มาย วอตช์',
                'นี่คือนาฬิกาข้อมือของฉัน',
            ],
            [
                'hour',
                'อาวเออร์',
                'ชั่วโมง',
                'The lesson is one hour long.',
                'เดอะ เลสเซิน อิซ วัน เอาเออร์ ลอง',
                'บทเรียนใช้เวลาหนึ่งชั่วโมง',
            ],
            [
                'minute',
                'มินิต',
                'นาที',
                'Please wait one minute.',
                'พลีซ เวท วัน มินิต',
                'กรุณารอหนึ่งนาที',
            ],
            [
                'second',
                'เซเคินด์',
                'วินาที',
                'Wait one second, please.',
                'เวท วัน เซเคินด์ พลีซ',
                'กรุณารอสักหนึ่งวินาที',
            ],
            [
                'morning',
                'มอร์นิง',
                'ตอนเช้า',
                'I study in the morning.',
                'ไอ สตัดดี อิน เดอะ มอร์นิง',
                'ฉันเรียนตอนเช้า',
            ],
            [
                'afternoon',
                'แอฟเทอร์นูน',
                'ตอนบ่าย',
                'We meet in the afternoon.',
                'วี มีต อิน ดิ แอฟเทอร์นูน',
                'เราพบกันตอนบ่าย',
            ],
            [
                'evening',
                'อีฟนิง',
                'ตอนเย็น',
                'I read in the evening.',
                'ไอ รีด อิน ดิ อีฟนิง',
                'ฉันอ่านหนังสือตอนเย็น',
            ],
            [
                'night',
                'ไนต์',
                'กลางคืน',
                'I sleep at night.',
                'ไอ สลีพ แอท ไนต์',
                'ฉันนอนตอนกลางคืน',
            ],
            [
                'today',
                'ทูเดย์',
                'วันนี้',
                'Today is Friday.',
                'ทูเดย์ อิซ ฟรายเดย์',
                'วันนี้เป็นวันศุกร์',
            ],
            [
                'tomorrow',
                'ทูมอโร',
                'พรุ่งนี้',
                'We have a class tomorrow.',
                'วี แฮฟ อะ คลาส ทูมอร์โรว์',
                'เรามีเรียนพรุ่งนี้',
            ],
            [
                'yesterday',
                'เยสเทอร์เดย์',
                'เมื่อวาน',
                'I was at home yesterday.',
                'ไอ วอซ แอท โฮม เยสเตอร์เดย์',
                'เมื่อวานฉันอยู่ที่บ้าน',
            ],
            [
                'now',
                'นาว',
                'ตอนนี้',
                'It is eight o\'clock now.',
                'อิต อิซ เอท อะคล็อก นาว',
                'ตอนนี้แปดโมง',
            ],
            [
                'early',
                'เออร์ลี',
                'เช้า; ก่อนเวลา',
                'I arrive early.',
                'ไอ อะไรฟ์ เออร์ลี',
                'ฉันมาถึงก่อนเวลา',
            ],
            [
                'late',
                'เลท',
                'สาย; ช้ากว่าเวลา',
                'Sorry, I am late.',
                'ซอรี ไอ แอม เลท',
                'ขอโทษ ฉันมาสาย',
            ],
        ],
        'exercises' => [
            'multiple_choice' => [
                [
                    'question' => 'What does "What time is it?" ask?',
                    'explanation' => 'What time is it? ใช้ถามเวลา',
                    'answers' => [
                        [
                            'ตอนนี้กี่โมง',
                            true,
                        ],
                        [
                            'คุณชื่ออะไร',
                            false,
                        ],
                        [
                            'คุณอยู่ที่ไหน',
                            false,
                        ],
                        [
                            'คุณอายุเท่าไร',
                            false,
                        ],
                    ],
                ],
                [
                    'question' => 'One hour has how many minutes?',
                    'explanation' => 'หนึ่งชั่วโมงหรือ one hour มีหกสิบนาที',
                    'answers' => [
                        [
                            'ten',
                            false,
                        ],
                        [
                            'sixty',
                            true,
                        ],
                        [
                            'twenty',
                            false,
                        ],
                        [
                            'thirty',
                            false,
                        ],
                    ],
                ],
                [
                    'question' => 'Which word means วินาที?',
                    'explanation' => 'second เป็นหน่วยเวลา หมายถึง วินาที',
                    'answers' => [
                        [
                            'week',
                            false,
                        ],
                        [
                            'year',
                            false,
                        ],
                        [
                            'second',
                            true,
                        ],
                        [
                            'hour',
                            false,
                        ],
                    ],
                ],
                [
                    'question' => 'Class starts at nine. Ben arrives at eight thirty. Ben is ___.',
                    'explanation' => 'Ben มาถึงก่อนเวลาเริ่มเรียน จึงเป็น early',
                    'answers' => [
                        [
                            'thirsty',
                            false,
                        ],
                        [
                            'late',
                            false,
                        ],
                        [
                            'hungry',
                            false,
                        ],
                        [
                            'early',
                            true,
                        ],
                    ],
                ],
                [
                    'question' => 'Class starts at nine. Anna arrives at nine fifteen. Anna is ___.',
                    'explanation' => 'Anna มาถึงหลังเวลาเริ่มเรียน จึงเป็น late',
                    'answers' => [
                        [
                            'late',
                            true,
                        ],
                        [
                            'early',
                            false,
                        ],
                        [
                            'young',
                            false,
                        ],
                        [
                            'new',
                            false,
                        ],
                    ],
                ],
                [
                    'question' => 'Today is Friday. Which day is tomorrow?',
                    'explanation' => 'tomorrow คือพรุ่งนี้ หลังวันศุกร์คือวันเสาร์',
                    'answers' => [
                        [
                            'Thursday',
                            false,
                        ],
                        [
                            'Saturday',
                            true,
                        ],
                        [
                            'Monday',
                            false,
                        ],
                        [
                            'Wednesday',
                            false,
                        ],
                    ],
                ],
                [
                    'question' => 'Today is Friday. Which day was yesterday?',
                    'explanation' => 'yesterday คือเมื่อวาน ก่อนวันศุกร์คือวันพฤหัสบดี',
                    'answers' => [
                        [
                            'Sunday',
                            false,
                        ],
                        [
                            'Monday',
                            false,
                        ],
                        [
                            'Thursday',
                            true,
                        ],
                        [
                            'Saturday',
                            false,
                        ],
                    ],
                ],
                [
                    'question' => 'A friend asks, "What time is it?" Which reply answers the question?',
                    'explanation' => 'It\'s two o\'clock. บอกเวลา จึงตอบคำถาม What time is it?',
                    'answers' => [
                        [
                            'I am fine.',
                            false,
                        ],
                        [
                            'My name is Ben.',
                            false,
                        ],
                        [
                            'I live in Bangkok.',
                            false,
                        ],
                        [
                            'It\'s two o\'clock.',
                            true,
                        ],
                    ],
                ],
            ],
            'fill_blank' => [
                [
                    'question' => 'It is 8 a.m. We say, "Good ___!"',
                    'explanation' => '8 a.m. เป็นเวลาเช้า จึงใช้ Good morning',
                    'answers' => [
                        [
                            'morning',
                            true,
                        ],
                        [
                            'afternoon',
                            false,
                        ],
                        [
                            'night',
                            false,
                        ],
                        [
                            'late',
                            false,
                        ],
                    ],
                ],
                [
                    'question' => 'It is 2 p.m. We say, "Good ___!"',
                    'explanation' => '2 p.m. เป็นเวลาบ่าย จึงใช้ Good afternoon',
                    'answers' => [
                        [
                            'morning',
                            false,
                        ],
                        [
                            'afternoon',
                            true,
                        ],
                        [
                            'night',
                            false,
                        ],
                        [
                            'early',
                            false,
                        ],
                    ],
                ],
                [
                    'question' => 'It is 7 p.m. We greet a friend: "Good ___!"',
                    'explanation' => '7 p.m. เป็นช่วงเย็น ใช้ Good evening เพื่อทักทาย',
                    'answers' => [
                        [
                            'afternoon',
                            false,
                        ],
                        [
                            'early',
                            false,
                        ],
                        [
                            'evening',
                            true,
                        ],
                        [
                            'morning',
                            false,
                        ],
                    ],
                ],
                [
                    'question' => 'เติมคำว่ากลางคืน: "I sleep at ___."',
                    'explanation' => 'at night หมายถึง ตอนกลางคืน',
                    'answers' => [
                        [
                            'watch',
                            false,
                        ],
                        [
                            'clock',
                            false,
                        ],
                        [
                            'minute',
                            false,
                        ],
                        [
                            'night',
                            true,
                        ],
                    ],
                ],
                [
                    'question' => 'เติมคำว่าตอนนี้: "What time is it ___?"',
                    'explanation' => 'now หมายถึง ตอนนี้',
                    'answers' => [
                        [
                            'now',
                            true,
                        ],
                        [
                            'hour',
                            false,
                        ],
                        [
                            'minute',
                            false,
                        ],
                        [
                            'second',
                            false,
                        ],
                    ],
                ],
                [
                    'question' => 'Please wait for one ___.',
                    'explanation' => 'one ใช้กับหน่วยเวลาเอกพจน์ minute หมายถึง หนึ่งนาที',
                    'answers' => [
                        [
                            'minutes',
                            false,
                        ],
                        [
                            'minute',
                            true,
                        ],
                        [
                            'hours',
                            false,
                        ],
                        [
                            'seconds',
                            false,
                        ],
                    ],
                ],
                [
                    'question' => 'เติมคำว่านาฬิกาข้อมือ: "This is my ___."',
                    'explanation' => 'watch หมายถึง นาฬิกาข้อมือ',
                    'answers' => [
                        [
                            'hour',
                            false,
                        ],
                        [
                            'night',
                            false,
                        ],
                        [
                            'watch',
                            true,
                        ],
                        [
                            'clock',
                            false,
                        ],
                    ],
                ],
                [
                    'question' => 'เติมคำว่านาฬิกาแขวน: "The ___ is on the wall."',
                    'explanation' => 'clock ใช้เรียกนาฬิกาตั้งโต๊ะหรือนาฬิกาแขวน',
                    'answers' => [
                        [
                            'morning',
                            false,
                        ],
                        [
                            'watch',
                            false,
                        ],
                        [
                            'minute',
                            false,
                        ],
                        [
                            'clock',
                            true,
                        ],
                    ],
                ],
            ],
            'listening' => [
                [
                    'audio_path' => 'audio/english/time/eight-thirty-in-morning.mp3',
                    'question' => 'จากเสียง ตอนนี้กี่โมง?',
                    'vocabulary_mode' => 'after_vocabulary',
                    'required_vocabulary_words' => ['morning'],
                    'explanation' => 'eight thirty in the morning หมายถึง แปดโมงครึ่งตอนเช้า',
                    'answers' => [
                        [
                            '8:30 a.m.',
                            true,
                        ],
                        [
                            '8:00 a.m.',
                            false,
                        ],
                        [
                            '3:30 p.m.',
                            false,
                        ],
                        [
                            '8:30 p.m.',
                            false,
                        ],
                    ],
                ],
                [
                    'audio_path' => 'audio/english/time/class-at-two-fifteen.mp3',
                    'question' => 'จากเสียง เริ่มเรียนกี่โมง?',
                    'vocabulary_mode' => 'after_vocabulary',
                    'required_vocabulary_words' => ['afternoon'],
                    'explanation' => 'two fifteen in the afternoon หมายถึง บ่ายสองโมงสิบห้านาที',
                    'answers' => [
                        [
                            '2:15 a.m.',
                            false,
                        ],
                        [
                            '2:15 p.m.',
                            true,
                        ],
                        [
                            '3:15 p.m.',
                            false,
                        ],
                        [
                            '2:50 p.m.',
                            false,
                        ],
                    ],
                ],
            ],
            'image_choice' => [
                [
                    'image_path' => 'images/english/time/round-wall-clock.jpg',
                    'question' => 'สิ่งของในภาพเรียกว่าอะไร?',
                    'explanation' => 'clock หมายถึง นาฬิกาตั้งโต๊ะหรือนาฬิกาแขวน ตรงกับภาพนาฬิกาบนผนัง',
                    'answers' => [
                        [
                            'watch',
                            false,
                        ],
                        [
                            'book',
                            false,
                        ],
                        [
                            'clock',
                            true,
                        ],
                        [
                            'bag',
                            false,
                        ],
                    ],
                ],
                [
                    'image_path' => 'images/english/time/wristwatch-on-wrist.jpg',
                    'question' => 'สิ่งของที่ข้อมือในภาพเรียกว่าอะไร?',
                    'explanation' => 'watch หมายถึง นาฬิกาข้อมือ ตรงกับภาพ',
                    'answers' => [
                        [
                            'chair',
                            false,
                        ],
                        [
                            'cup',
                            false,
                        ],
                        [
                            'clock',
                            false,
                        ],
                        [
                            'watch',
                            true,
                        ],
                    ],
                ],
            ],
        ],
    ],
];
