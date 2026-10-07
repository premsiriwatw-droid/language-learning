<?php

return [
    'Greetings' => [
        'vocabulary' => [
            [
                'hello',
                'həˈloʊ',
                'สวัสดี',
                'Hello, how are you?',
                'เฮลโล ฮาว อาร์ ยู',
                'สวัสดี คุณเป็นอย่างไรบ้าง',
            ],
            [
                'good morning',
                'ɡʊd ˈmɔrnɪŋ',
                'สวัสดีตอนเช้า',
                'Good morning, everyone.',
                'กุด มอร์นิง เอวรีวัน',
                'สวัสดีตอนเช้าทุกคน',
            ],
            [
                'good afternoon',
                'ɡʊd ˌæftɚˈnun',
                'สวัสดีตอนบ่าย',
                'Good afternoon, Anna.',
                'กุด แอฟเทอร์นูน แอนนา',
                'สวัสดีตอนบ่าย แอนนา',
            ],
            [
                'good night',
                'ɡʊd naɪt',
                'ราตรีสวัสดิ์',
                'Good night, Mom.',
                'กุด ไนต์ มอม',
                'ราตรีสวัสดิ์ค่ะแม่',
            ],
            [
                'goodbye',
                'ɡʊdˈbaɪ',
                'ลาก่อน',
                'Goodbye, see you tomorrow.',
                'กุดบาย ซี ยู ทูมอร์โรว์',
                'ลาก่อน แล้วพบกันพรุ่งนี้',
            ],
            [
                'thank you',
                'θæŋk ju',
                'ขอบคุณ',
                'Thank you for your help.',
                'แธงก์ ยู ฟอร์ ยัวร์ เฮลป์',
                'ขอบคุณสำหรับความช่วยเหลือ',
            ],
            [
                'please',
                'pliz',
                'กรุณา; โปรด',
                'Please sit down.',
                'พลีซ ซิต ดาวน์',
                'กรุณานั่งลง',
            ],
            [
                'sorry',
                'ˈsɑri',
                'ขอโทษ; เสียใจ',
                'Sorry, I am late.',
                'ซอรี ไอ แอม เลท',
                'ขอโทษ ฉันมาสาย',
            ],
            [
                'excuse me',
                'ɪkˈskjuz mi',
                'ขอโทษนะ; ขอรบกวน',
                'Excuse me, is this your bag?',
                'อิกสคิวซ มี อิซ ดิส ยัวร์ แบ็ก',
                'ขอโทษนะ นี่คือกระเป๋าของคุณหรือเปล่า',
            ],
            [
                'welcome',
                'ˈwɛlkəm',
                'ยินดีต้อนรับ',
                'Welcome to our school.',
                'เวลคัม ทู เอาเออร์ สคูล',
                'ยินดีต้อนรับสู่โรงเรียนของเรา',
            ],
            [
                'fine',
                'faɪn',
                'สบายดี',
                'I am fine, thank you.',
                'ไอ แอม ไฟน์ แธงก์ ยู',
                'ฉันสบายดี ขอบคุณ',
            ],
            [
                'nice to meet you',
                'naɪs tə mit ju',
                'ยินดีที่ได้รู้จัก',
                'Nice to meet you, Ben.',
                'ไนซ์ ทู มีต ยู เบน',
                'ยินดีที่ได้รู้จัก เบน',
            ],
        ],
        'exercises' => [
            'multiple_choice' => [
                [
                    'question' => 'คำว่า hello ใช้กล่าวอะไร?',
                    'explanation' => 'hello เป็นคำทักทาย หมายถึง สวัสดี',
                    'answers' => [
                        [
                            'สวัสดี',
                            true,
                        ],
                        [
                            'ลาก่อน',
                            false,
                        ],
                        [
                            'ขอโทษ',
                            false,
                        ],
                        [
                            'ราตรีสวัสดิ์',
                            false,
                        ],
                    ],
                ],
                [
                    'question' => 'คุณพบเพื่อนตอนบ่าย ควรทักทายอย่างไร?',
                    'explanation' => 'Good afternoon ใช้ทักทายตอนบ่าย',
                    'answers' => [
                        [
                            'Good morning.',
                            false,
                        ],
                        [
                            'Good afternoon.',
                            true,
                        ],
                        [
                            'Good night.',
                            false,
                        ],
                        [
                            'Goodbye.',
                            false,
                        ],
                    ],
                ],
                [
                    'question' => 'ก่อนถามคนที่ไม่รู้จัก ควรใช้คำใดเรียกความสนใจอย่างสุภาพ?',
                    'explanation' => 'Excuse me ใช้ขอรบกวนหรือเรียกความสนใจอย่างสุภาพ',
                    'answers' => [
                        [
                            'I am fine.',
                            false,
                        ],
                        [
                            'Goodbye.',
                            false,
                        ],
                        [
                            'Excuse me.',
                            true,
                        ],
                        [
                            'Good night.',
                            false,
                        ],
                    ],
                ],
                [
                    'question' => 'Which word makes "Sit down" more polite?',
                    'explanation' => 'please ช่วยทำให้คำขอสุภาพขึ้น เช่น Please sit down.',
                    'answers' => [
                        [
                            'hello',
                            false,
                        ],
                        [
                            'goodbye',
                            false,
                        ],
                        [
                            'fine',
                            false,
                        ],
                        [
                            'please',
                            true,
                        ],
                    ],
                ],
                [
                    'question' => 'A friend says, "Thank you." What is a suitable reply?',
                    'explanation' => 'You\'re welcome. ใช้ตอบรับคำขอบคุณ หมายถึง ยินดี',
                    'answers' => [
                        [
                            'You\'re welcome.',
                            true,
                        ],
                        [
                            'Good night.',
                            false,
                        ],
                        [
                            'Sorry, I am late.',
                            false,
                        ],
                        [
                            'Excuse me.',
                            false,
                        ],
                    ],
                ],
                [
                    'question' => 'Anna is a new classmate. What can you say when you meet her?',
                    'explanation' => 'Nice to meet you. หมายถึง ยินดีที่ได้รู้จัก ใช้เมื่อพบคนใหม่',
                    'answers' => [
                        [
                            'Good night.',
                            false,
                        ],
                        [
                            'Nice to meet you.',
                            true,
                        ],
                        [
                            'Goodbye.',
                            false,
                        ],
                        [
                            'See you tomorrow.',
                            false,
                        ],
                    ],
                ],
            ],
            'fill_blank' => [
                [
                    'question' => 'เติมคำบอกลา: "___, see you tomorrow."',
                    'explanation' => 'Goodbye ใช้บอกลา ประโยคนี้หมายถึง ลาก่อน แล้วพบกันพรุ่งนี้',
                    'answers' => [
                        [
                            'Hello',
                            false,
                        ],
                        [
                            'Please',
                            false,
                        ],
                        [
                            'Goodbye',
                            true,
                        ],
                        [
                            'Fine',
                            false,
                        ],
                    ],
                ],
                [
                    'question' => 'เติมคำทักทายตอนเช้า: "Good ___."',
                    'explanation' => 'Good morning เป็นคำทักทายตอนเช้า',
                    'answers' => [
                        [
                            'night',
                            false,
                        ],
                        [
                            'goodbye',
                            false,
                        ],
                        [
                            'sorry',
                            false,
                        ],
                        [
                            'morning',
                            true,
                        ],
                    ],
                ],
                [
                    'question' => 'ก่อนเข้านอน แม่พูดว่า "Good ___."',
                    'explanation' => 'Good night ใช้กล่าวราตรีสวัสดิ์ก่อนเข้านอน',
                    'answers' => [
                        [
                            'night',
                            true,
                        ],
                        [
                            'morning',
                            false,
                        ],
                        [
                            'welcome',
                            false,
                        ],
                        [
                            'afternoon',
                            false,
                        ],
                    ],
                ],
                [
                    'question' => 'เติมคำขอบคุณ: "___ you for your help."',
                    'explanation' => 'Thank you for your help. หมายถึง ขอบคุณสำหรับความช่วยเหลือ',
                    'answers' => [
                        [
                            'See',
                            false,
                        ],
                        [
                            'Thank',
                            true,
                        ],
                        [
                            'Meet',
                            false,
                        ],
                        [
                            'Sit',
                            false,
                        ],
                    ],
                ],
                [
                    'question' => 'ตอบคำถาม How are you?: "I am ___, thank you."',
                    'explanation' => 'I am fine. หมายถึง ฉันสบายดี',
                    'answers' => [
                        [
                            'hello',
                            false,
                        ],
                        [
                            'please',
                            false,
                        ],
                        [
                            'fine',
                            true,
                        ],
                        [
                            'goodbye',
                            false,
                        ],
                    ],
                ],
                [
                    'question' => 'พบเพื่อนใหม่: "Nice to ___ you."',
                    'explanation' => 'Nice to meet you. หมายถึง ยินดีที่ได้รู้จัก',
                    'answers' => [
                        [
                            'thank',
                            false,
                        ],
                        [
                            'sit',
                            false,
                        ],
                        [
                            'sleep',
                            false,
                        ],
                        [
                            'meet',
                            true,
                        ],
                    ],
                ],
                [
                    'question' => 'เติมคำต้อนรับ: "___ to our school."',
                    'explanation' => 'Welcome to our school. หมายถึง ยินดีต้อนรับสู่โรงเรียนของเรา',
                    'answers' => [
                        [
                            'Welcome',
                            true,
                        ],
                        [
                            'Goodbye',
                            false,
                        ],
                        [
                            'Fine',
                            false,
                        ],
                        [
                            'Sorry',
                            false,
                        ],
                    ],
                ],
                [
                    'question' => 'ขอโทษที่มาสาย: "___, I am late."',
                    'explanation' => 'Sorry ใช้กล่าวขอโทษ เช่น ขอโทษที่มาสาย',
                    'answers' => [
                        [
                            'Please',
                            false,
                        ],
                        [
                            'Sorry',
                            true,
                        ],
                        [
                            'Welcome',
                            false,
                        ],
                        [
                            'Fine',
                            false,
                        ],
                    ],
                ],
            ],
            'listening' => [
                [
                    'audio_path' => 'audio/english/greetings/hello-how-are-you.mp3',
                    'question' => 'จากเสียง ผู้ตอบรู้สึกอย่างไร?',
                    'explanation' => 'ผู้ตอบพูดว่า I am fine, thank you. หมายถึง สบายดี ขอบคุณ',
                    'answers' => [
                        [
                            'หิว',
                            false,
                        ],
                        [
                            'เหนื่อย',
                            false,
                        ],
                        [
                            'สบายดี',
                            true,
                        ],
                        [
                            'หนาว',
                            false,
                        ],
                    ],
                ],
                [
                    'audio_path' => 'audio/english/greetings/good-morning-nice-to-meet-you.mp3',
                    'question' => 'จากเสียง ผู้พูดทักทายในช่วงใด?',
                    'explanation' => 'เสียงใช้คำว่า Good morning ซึ่งเป็นคำทักทายตอนเช้า',
                    'answers' => [
                        [
                            'ก่อนนอน',
                            false,
                        ],
                        [
                            'เที่ยงคืน',
                            false,
                        ],
                        [
                            'ตอนบ่าย',
                            false,
                        ],
                        [
                            'ตอนเช้า',
                            true,
                        ],
                    ],
                ],
            ],
            'image_choice' => [
                [
                    'image_path' => 'images/english/greetings/waving-hello-at-door.jpg',
                    'question' => 'เลือกคำทักทายที่เหมาะกับภาพคนมาถึงและโบกมือ',
                    'explanation' => 'Hello. ใช้ทักทายเมื่อพบกัน เหมาะกับภาพคนมาถึงและโบกมือ',
                    'answers' => [
                        [
                            'Hello.',
                            true,
                        ],
                        [
                            'Sorry.',
                            false,
                        ],
                        [
                            'Thank you.',
                            false,
                        ],
                        [
                            'Good night.',
                            false,
                        ],
                    ],
                ],
                [
                    'image_path' => 'images/english/greetings/good-night-before-bed.jpg',
                    'question' => 'เลือกคำพูดที่เหมาะกับภาพก่อนเข้านอน',
                    'explanation' => 'Good night. หมายถึง ราตรีสวัสดิ์ ใช้กล่าวก่อนเข้านอน',
                    'answers' => [
                        [
                            'Excuse me.',
                            false,
                        ],
                        [
                            'Good night.',
                            true,
                        ],
                        [
                            'Welcome.',
                            false,
                        ],
                        [
                            'Good afternoon.',
                            false,
                        ],
                    ],
                ],
            ],
        ],
    ],
    'Self Introduction' => [
        'vocabulary' => [
            [
                'name',
                'neɪm',
                'ชื่อ',
                'My name is Anna.',
                'มาย เนม อิซ แอนนา',
                'ฉันชื่อแอนนา',
            ],
            [
                'my',
                'maɪ',
                'ของฉัน',
                'This is my bag.',
                'ดิส อิซ มาย แบ็ก',
                'นี่คือกระเป๋าของฉัน',
            ],
            [
                'I',
                'aɪ',
                'ฉัน',
                'I am a student.',
                'ไอ แอม อะ สตูเดนต์',
                'ฉันเป็นนักเรียน',
            ],
            [
                'you',
                'ju',
                'คุณ',
                'Are you a teacher?',
                'อาร์ ยู อะ ทีเชอร์',
                'คุณเป็นครูหรือเปล่า',
            ],
            [
                'from',
                'frəm',
                'จาก',
                'I am from Thailand.',
                'ไอ แอม ฟรอม ไทยแลนด์',
                'ฉันมาจากประเทศไทย',
            ],
            [
                'live',
                'lɪv',
                'อาศัยอยู่',
                'I live in Bangkok.',
                'ไอ ลิฟ อิน แบงค็อก',
                'ฉันอาศัยอยู่ในกรุงเทพฯ',
            ],
            [
                'student',
                'ˈstudənt',
                'นักเรียน; นักศึกษา',
                'Ben is a student.',
                'เบน อิซ อะ สตูเดนต์',
                'เบนเป็นนักเรียน',
            ],
            [
                'teacher',
                'ˈtitʃɚ',
                'ครู',
                'My mother is a teacher.',
                'มาย มาเธอร์ อิซ อะ ทีเชอร์',
                'แม่ของฉันเป็นครู',
            ],
            [
                'friend',
                'frɛnd',
                'เพื่อน',
                'Anna is my friend.',
                'แอนนา อิซ มาย เฟรนด์',
                'แอนนาเป็นเพื่อนของฉัน',
            ],
            [
                'country',
                'ˈkʌntri',
                'ประเทศ',
                'Thailand is my country.',
                'ไทยแลนด์ อิซ มาย คันทรี',
                'ประเทศไทยคือประเทศของฉัน',
            ],
            [
                'Thailand',
                'ˈtaɪlænd',
                'ประเทศไทย',
                'I come from Thailand.',
                'ไอ คัม ฟรอม ไทยแลนด์',
                'ฉันมาจากประเทศไทย',
            ],
            [
                'introduce',
                'ˌɪntrəˈdus',
                'แนะนำตัว; แนะนำให้รู้จัก',
                'Let me introduce myself.',
                'เลต มี อินโทรดูซ มายเซลฟ์',
                'ขอฉันแนะนำตัวเอง',
            ],
        ],
        'exercises' => [
            'multiple_choice' => [
                [
                    'question' => 'เมื่อถาม "What is your name?" ควรตอบอย่างไร?',
                    'explanation' => 'What is your name? ถามชื่อ จึงตอบว่า My name is Ben.',
                    'answers' => [
                        [
                            'My name is Ben.',
                            true,
                        ],
                        [
                            'I am fine.',
                            false,
                        ],
                        [
                            'Good night.',
                            false,
                        ],
                        [
                            'Thank you.',
                            false,
                        ],
                    ],
                ],
                [
                    'question' => 'คำว่า from ใน "I am from Thailand." หมายถึงอะไร?',
                    'explanation' => 'from บอกที่มา I am from Thailand. หมายถึง ฉันมาจากประเทศไทย',
                    'answers' => [
                        [
                            'กับ',
                            false,
                        ],
                        [
                            'จาก',
                            true,
                        ],
                        [
                            'ใต้',
                            false,
                        ],
                        [
                            'ก่อน',
                            false,
                        ],
                    ],
                ],
                [
                    'question' => 'A person who studies at school is a ___.',
                    'explanation' => 'student หมายถึง นักเรียนหรือผู้ที่กำลังศึกษา',
                    'answers' => [
                        [
                            'driver',
                            false,
                        ],
                        [
                            'doctor',
                            false,
                        ],
                        [
                            'student',
                            true,
                        ],
                        [
                            'teacher',
                            false,
                        ],
                    ],
                ],
                [
                    'question' => 'A person who teaches students is a ___.',
                    'explanation' => 'teacher หมายถึง ครู ผู้สอนนักเรียน',
                    'answers' => [
                        [
                            'customer',
                            false,
                        ],
                        [
                            'student',
                            false,
                        ],
                        [
                            'tourist',
                            false,
                        ],
                        [
                            'teacher',
                            true,
                        ],
                    ],
                ],
                [
                    'question' => 'Which sentence introduces a friend?',
                    'explanation' => 'This is my friend, Anna. เป็นการแนะนำเพื่อนชื่อแอนนา',
                    'answers' => [
                        [
                            'This is my friend, Anna.',
                            true,
                        ],
                        [
                            'This is my lunch.',
                            false,
                        ],
                        [
                            'I am thirsty.',
                            false,
                        ],
                        [
                            'It is seven o\'clock.',
                            false,
                        ],
                    ],
                ],
                [
                    'question' => '"Where do you live?" asks about ___.',
                    'explanation' => 'Where do you live? หมายถึง คุณอาศัยอยู่ที่ไหน',
                    'answers' => [
                        [
                            'your name',
                            false,
                        ],
                        [
                            'where your home is',
                            true,
                        ],
                        [
                            'your favorite food',
                            false,
                        ],
                        [
                            'the time',
                            false,
                        ],
                    ],
                ],
            ],
            'fill_blank' => [
                [
                    'question' => 'เติมคำแทนผู้พูด: "___ am a student."',
                    'explanation' => 'I ใช้แทนผู้พูดและใช้กับ am: I am a student.',
                    'answers' => [
                        [
                            'You',
                            false,
                        ],
                        [
                            'He',
                            false,
                        ],
                        [
                            'I',
                            true,
                        ],
                        [
                            'They',
                            false,
                        ],
                    ],
                ],
                [
                    'question' => 'เติมคำแสดงเจ้าของ: "This is ___ bag."',
                    'explanation' => 'my หมายถึง ของฉัน ใช้หน้าคำนาม เช่น my bag',
                    'answers' => [
                        [
                            'I',
                            false,
                        ],
                        [
                            'me',
                            false,
                        ],
                        [
                            'am',
                            false,
                        ],
                        [
                            'my',
                            true,
                        ],
                    ],
                ],
                [
                    'question' => 'เติมคำแทนผู้ที่เราพูดด้วย: "Are ___ a teacher?"',
                    'explanation' => 'you หมายถึง คุณ และใช้กับ are ในคำถามนี้',
                    'answers' => [
                        [
                            'you',
                            true,
                        ],
                        [
                            'my',
                            false,
                        ],
                        [
                            'am',
                            false,
                        ],
                        [
                            'I',
                            false,
                        ],
                    ],
                ],
                [
                    'question' => 'เติมคำบอกที่อยู่: "I ___ in Bangkok."',
                    'explanation' => 'live in ใช้บอกว่าอาศัยอยู่ที่ใด',
                    'answers' => [
                        [
                            'country',
                            false,
                        ],
                        [
                            'live',
                            true,
                        ],
                        [
                            'name',
                            false,
                        ],
                        [
                            'from',
                            false,
                        ],
                    ],
                ],
                [
                    'question' => 'เติมคำที่หมายถึงประเทศ: "Thailand is a ___."',
                    'explanation' => 'country หมายถึง ประเทศ ประเทศไทยเป็นประเทศหนึ่ง',
                    'answers' => [
                        [
                            'student',
                            false,
                        ],
                        [
                            'teacher',
                            false,
                        ],
                        [
                            'country',
                            true,
                        ],
                        [
                            'friend',
                            false,
                        ],
                    ],
                ],
                [
                    'question' => 'เติมชื่อประเทศ: "I am from ___." (ประเทศไทย)',
                    'explanation' => 'Thailand หมายถึง ประเทศไทย จึงเติมให้ตรงกับความหมายที่กำหนด',
                    'answers' => [
                        [
                            'Japan',
                            false,
                        ],
                        [
                            'Canada',
                            false,
                        ],
                        [
                            'France',
                            false,
                        ],
                        [
                            'Thailand',
                            true,
                        ],
                    ],
                ],
                [
                    'question' => 'เติมคำแนะนำตัว: "Let me ___ myself."',
                    'explanation' => 'introduce myself หมายถึง แนะนำตัวเอง',
                    'answers' => [
                        [
                            'introduce',
                            true,
                        ],
                        [
                            'country',
                            false,
                        ],
                        [
                            'student',
                            false,
                        ],
                        [
                            'live',
                            false,
                        ],
                    ],
                ],
                [
                    'question' => 'เติมคำถามชื่อ: "What is your ___?"',
                    'explanation' => 'What is your name? หมายถึง คุณชื่ออะไร',
                    'answers' => [
                        [
                            'introduce',
                            false,
                        ],
                        [
                            'name',
                            true,
                        ],
                        [
                            'from',
                            false,
                        ],
                        [
                            'live',
                            false,
                        ],
                    ],
                ],
            ],
            'listening' => [
                [
                    'audio_path' => 'audio/english/self-introduction/anna-from-thailand.mp3',
                    'question' => 'จากเสียง แอนนามาจากประเทศใด?',
                    'explanation' => 'แอนนาพูดว่า I am from Thailand. จึงมาจากประเทศไทย',
                    'answers' => [
                        [
                            'Japan',
                            false,
                        ],
                        [
                            'Canada',
                            false,
                        ],
                        [
                            'Thailand',
                            true,
                        ],
                        [
                            'France',
                            false,
                        ],
                    ],
                ],
                [
                    'audio_path' => 'audio/english/self-introduction/student-lives-in-bangkok.mp3',
                    'question' => 'จากเสียง ผู้พูดเป็นใคร?',
                    'explanation' => 'ผู้พูดบอกว่า I am a student. หมายถึง ฉันเป็นนักเรียน',
                    'answers' => [
                        [
                            'a doctor',
                            false,
                        ],
                        [
                            'a driver',
                            false,
                        ],
                        [
                            'a teacher',
                            false,
                        ],
                        [
                            'a student',
                            true,
                        ],
                    ],
                ],
            ],
            'image_choice' => [
                [
                    'image_path' => 'images/english/self-introduction/teacher-leading-class.jpg',
                    'question' => 'บุคคลที่กำลังสอนในภาพคือใคร?',
                    'explanation' => 'a teacher หมายถึง ครู ตรงกับผู้ที่กำลังสอนนักเรียน',
                    'answers' => [
                        [
                            'a teacher',
                            true,
                        ],
                        [
                            'a doctor',
                            false,
                        ],
                        [
                            'a driver',
                            false,
                        ],
                        [
                            'a chef',
                            false,
                        ],
                    ],
                ],
                [
                    'image_path' => 'images/english/self-introduction/student-studying-at-desk.jpg',
                    'question' => 'ภาพแสดงใครกำลังอ่านหนังสือเรียน?',
                    'explanation' => 'a student หมายถึง นักเรียน ตรงกับภาพผู้ที่กำลังเรียน',
                    'answers' => [
                        [
                            'a driver',
                            false,
                        ],
                        [
                            'a student',
                            true,
                        ],
                        [
                            'a chef',
                            false,
                        ],
                        [
                            'a doctor',
                            false,
                        ],
                    ],
                ],
            ],
        ],
    ],
    'Numbers' => [
        'vocabulary' => [
            [
                'zero',
                'ˈzɪroʊ',
                'ศูนย์',
                'There are zero apples in the box.',
                'แดร์ อาร์ ซีโร แอปเปิลส์ อิน เดอะ บ็อกซ์',
                'ในกล่องไม่มีแอปเปิลเลย',
            ],
            [
                'one',
                'wʌn',
                'หนึ่ง',
                'I have one pen.',
                'ไอ แฮฟ วัน เพน',
                'ฉันมีปากกาหนึ่งด้าม',
            ],
            [
                'two',
                'tu',
                'สอง',
                'I have two books.',
                'ไอ แฮฟ ทู บุคส์',
                'ฉันมีหนังสือสองเล่ม',
            ],
            [
                'three',
                'θri',
                'สาม',
                'There are three apples.',
                'แดร์ อาร์ ธรี แอปเปิลส์',
                'มีแอปเปิลสามผล',
            ],
            [
                'four',
                'fɔr',
                'สี่',
                'We need four chairs.',
                'วี นีด ฟอร์ แชร์ส',
                'เราต้องการเก้าอี้สี่ตัว',
            ],
            [
                'five',
                'faɪv',
                'ห้า',
                'I have five coins.',
                'ไอ แฮฟ ไฟฟ์ คอยน์ส',
                'ฉันมีเหรียญห้าเหรียญ',
            ],
            [
                'six',
                'sɪks',
                'หก',
                'There are six cups.',
                'แดร์ อาร์ ซิกซ์ คัปส์',
                'มีถ้วยหกใบ',
            ],
            [
                'seven',
                'ˈsɛvən',
                'เจ็ด',
                'I have seven apples.',
                'ไอ แฮฟ เซเวน แอปเปิลส์',
                'ฉันมีแอปเปิลเจ็ดผล',
            ],
            [
                'eight',
                'eɪt',
                'แปด',
                'There are eight students.',
                'แดร์ อาร์ เอท สตูเดนต์ส',
                'มีนักเรียนแปดคน',
            ],
            [
                'nine',
                'naɪn',
                'เก้า',
                'We have nine pencils.',
                'วี แฮฟ ไนน์ เพนซิลส์',
                'เรามีดินสอเก้าแท่ง',
            ],
            [
                'ten',
                'tɛn',
                'สิบ',
                'There are ten books.',
                'แดร์ อาร์ เทน บุคส์',
                'มีหนังสือสิบเล่ม',
            ],
            [
                'eleven',
                'ɪˈlɛvən',
                'สิบเอ็ด',
                'I have eleven cards.',
                'ไอ แฮฟ อิเลเวน คาร์ดส์',
                'ฉันมีบัตรสิบเอ็ดใบ',
            ],
            [
                'twelve',
                'twɛlv',
                'สิบสอง',
                'There are twelve months in a year.',
                'แดร์ อาร์ ทเวลฟ์ มันธส์ อิน อะ เยียร์',
                'หนึ่งปีมีสิบสองเดือน',
            ],
            [
                'number',
                'ˈnʌmbɚ',
                'ตัวเลข; จำนวน',
                'What is your phone number?',
                'วอต อิซ ยัวร์ โฟน นัมเบอร์',
                'หมายเลขโทรศัพท์ของคุณคืออะไร',
            ],
            [
                'count',
                'kaʊnt',
                'นับ',
                'Please count the apples.',
                'พลีซ เคานต์ ดิ แอปเปิลส์',
                'กรุณานับแอปเปิล',
            ],
        ],
        'exercises' => [
            'multiple_choice' => [
                [
                    'question' => 'คำว่า one หมายถึงจำนวนใด?',
                    'explanation' => 'one หมายถึง หนึ่ง',
                    'answers' => [
                        [
                            'หนึ่ง',
                            true,
                        ],
                        [
                            'สอง',
                            false,
                        ],
                        [
                            'สาม',
                            false,
                        ],
                        [
                            'สี่',
                            false,
                        ],
                    ],
                ],
                [
                    'question' => 'Which word means สาม?',
                    'explanation' => 'three หมายถึง สาม',
                    'answers' => [
                        [
                            'one',
                            false,
                        ],
                        [
                            'three',
                            true,
                        ],
                        [
                            'six',
                            false,
                        ],
                        [
                            'nine',
                            false,
                        ],
                    ],
                ],
                [
                    'question' => '"I have five coins." ผู้พูดมีเหรียญกี่เหรียญ?',
                    'explanation' => 'five coins หมายถึง เหรียญห้าเหรียญ',
                    'answers' => [
                        [
                            'เจ็ด',
                            false,
                        ],
                        [
                            'สิบ',
                            false,
                        ],
                        [
                            'ห้า',
                            true,
                        ],
                        [
                            'สอง',
                            false,
                        ],
                    ],
                ],
                [
                    'question' => 'There are seven days in a week. How many days?',
                    'explanation' => 'ประโยคบอกว่า seven days หรือเจ็ดวัน',
                    'answers' => [
                        [
                            'twelve',
                            false,
                        ],
                        [
                            'five',
                            false,
                        ],
                        [
                            'nine',
                            false,
                        ],
                        [
                            'seven',
                            true,
                        ],
                    ],
                ],
                [
                    'question' => '"We have nine pencils." คำว่า nine คือจำนวนใด?',
                    'explanation' => 'nine หมายถึง เก้า',
                    'answers' => [
                        [
                            'เก้า',
                            true,
                        ],
                        [
                            'หก',
                            false,
                        ],
                        [
                            'แปด',
                            false,
                        ],
                        [
                            'สิบ',
                            false,
                        ],
                    ],
                ],
                [
                    'question' => 'Which word means the action นับ?',
                    'explanation' => 'count เป็นคำกริยาหมายถึง นับ',
                    'answers' => [
                        [
                            'sit',
                            false,
                        ],
                        [
                            'count',
                            true,
                        ],
                        [
                            'drink',
                            false,
                        ],
                        [
                            'sleep',
                            false,
                        ],
                    ],
                ],
                [
                    'question' => 'What does number mean in "phone number"?',
                    'explanation' => 'phone number หมายถึง หมายเลขโทรศัพท์',
                    'answers' => [
                        [
                            'อาหาร',
                            false,
                        ],
                        [
                            'เพื่อน',
                            false,
                        ],
                        [
                            'หมายเลข',
                            true,
                        ],
                        [
                            'กระเป๋า',
                            false,
                        ],
                    ],
                ],
                [
                    'question' => 'Ben has two pens and gets one more. How many pens does he have?',
                    'explanation' => 'สองด้ามเพิ่มอีกหนึ่งด้าม รวมเป็นสามด้าม หรือ three',
                    'answers' => [
                        [
                            'five',
                            false,
                        ],
                        [
                            'one',
                            false,
                        ],
                        [
                            'four',
                            false,
                        ],
                        [
                            'three',
                            true,
                        ],
                    ],
                ],
                [
                    'question' => 'There are four chairs. Two people sit down. How many chairs are still empty?',
                    'explanation' => 'เก้าอี้สี่ตัวมีคนนั่งสองตัว เหลือว่างสองตัว หรือ two',
                    'answers' => [
                        [
                            'two',
                            true,
                        ],
                        [
                            'one',
                            false,
                        ],
                        [
                            'three',
                            false,
                        ],
                        [
                            'four',
                            false,
                        ],
                    ],
                ],
                [
                    'question' => 'Which number comes after ten?',
                    'explanation' => 'หลัง ten คือ eleven หรือสิบเอ็ด',
                    'answers' => [
                        [
                            'nine',
                            false,
                        ],
                        [
                            'eleven',
                            true,
                        ],
                        [
                            'eight',
                            false,
                        ],
                        [
                            'twelve',
                            false,
                        ],
                    ],
                ],
            ],
            'fill_blank' => [
                [
                    'question' => 'ในกล่องไม่มีแอปเปิล: "There are ___ apples in the box."',
                    'explanation' => 'zero หมายถึง ศูนย์ จึงใช้เมื่อไม่มีแอปเปิลเลย',
                    'answers' => [
                        [
                            'one',
                            false,
                        ],
                        [
                            'two',
                            false,
                        ],
                        [
                            'zero',
                            true,
                        ],
                        [
                            'three',
                            false,
                        ],
                    ],
                ],
                [
                    'question' => 'เติมคำจำนวนสอง: "I have ___ books."',
                    'explanation' => 'two books หมายถึง หนังสือสองเล่ม',
                    'answers' => [
                        [
                            'four',
                            false,
                        ],
                        [
                            'six',
                            false,
                        ],
                        [
                            'eight',
                            false,
                        ],
                        [
                            'two',
                            true,
                        ],
                    ],
                ],
                [
                    'question' => 'เติมคำจำนวนสี่: "We need ___ chairs."',
                    'explanation' => 'four chairs หมายถึง เก้าอี้สี่ตัว',
                    'answers' => [
                        [
                            'four',
                            true,
                        ],
                        [
                            'seven',
                            false,
                        ],
                        [
                            'nine',
                            false,
                        ],
                        [
                            'five',
                            false,
                        ],
                    ],
                ],
                [
                    'question' => 'เติมคำจำนวนหก: "There are ___ cups."',
                    'explanation' => 'six cups หมายถึง ถ้วยหกใบ',
                    'answers' => [
                        [
                            'ten',
                            false,
                        ],
                        [
                            'six',
                            true,
                        ],
                        [
                            'two',
                            false,
                        ],
                        [
                            'eight',
                            false,
                        ],
                    ],
                ],
                [
                    'question' => 'เติมคำจำนวนแปด: "There are ___ students."',
                    'explanation' => 'eight students หมายถึง นักเรียนแปดคน',
                    'answers' => [
                        [
                            'three',
                            false,
                        ],
                        [
                            'five',
                            false,
                        ],
                        [
                            'eight',
                            true,
                        ],
                        [
                            'eleven',
                            false,
                        ],
                    ],
                ],
                [
                    'question' => 'เติมคำจำนวนสิบ: "I have ___ fingers."',
                    'explanation' => 'ten หมายถึง สิบ ประโยคนี้พูดถึงนิ้วมือสิบนิ้ว',
                    'answers' => [
                        [
                            'seven',
                            false,
                        ],
                        [
                            'nine',
                            false,
                        ],
                        [
                            'twelve',
                            false,
                        ],
                        [
                            'ten',
                            true,
                        ],
                    ],
                ],
                [
                    'question' => 'เติมคำจำนวนสิบเอ็ด: "I have ___ cards."',
                    'explanation' => 'eleven cards หมายถึง บัตรสิบเอ็ดใบ',
                    'answers' => [
                        [
                            'eleven',
                            true,
                        ],
                        [
                            'nine',
                            false,
                        ],
                        [
                            'ten',
                            false,
                        ],
                        [
                            'eight',
                            false,
                        ],
                    ],
                ],
                [
                    'question' => 'เติมจำนวนเดือนในหนึ่งปี: "There are ___ months in a year."',
                    'explanation' => 'หนึ่งปีมีสิบสองเดือน ใช้คำว่า twelve',
                    'answers' => [
                        [
                            'nine',
                            false,
                        ],
                        [
                            'twelve',
                            true,
                        ],
                        [
                            'ten',
                            false,
                        ],
                        [
                            'eleven',
                            false,
                        ],
                    ],
                ],
            ],
            'listening' => [
                [
                    'audio_path' => 'audio/english/numbers/seven-apples.mp3',
                    'question' => 'จากเสียง ผู้พูดมีแอปเปิลกี่ผล?',
                    'explanation' => 'ผู้พูดบอกว่า seven apples หรือแอปเปิลเจ็ดผล',
                    'answers' => [
                        [
                            'five',
                            false,
                        ],
                        [
                            'six',
                            false,
                        ],
                        [
                            'seven',
                            true,
                        ],
                        [
                            'nine',
                            false,
                        ],
                    ],
                ],
                [
                    'audio_path' => 'audio/english/numbers/ten-students-in-room.mp3',
                    'question' => 'จากเสียง ในห้องมีนักเรียนกี่คน?',
                    'explanation' => 'เสียงบอกว่า ten students หรือนักเรียนสิบคน',
                    'answers' => [
                        [
                            'eight',
                            false,
                        ],
                        [
                            'twelve',
                            false,
                        ],
                        [
                            'two',
                            false,
                        ],
                        [
                            'ten',
                            true,
                        ],
                    ],
                ],
            ],
            'image_choice' => [
                [
                    'image_path' => 'images/english/numbers/three-apples-on-table.jpg',
                    'question' => 'ในภาพมีแอปเปิลกี่ผล?',
                    'explanation' => 'ภาพมีแอปเปิลสามผล ใช้คำว่า three',
                    'answers' => [
                        [
                            'three',
                            true,
                        ],
                        [
                            'one',
                            false,
                        ],
                        [
                            'two',
                            false,
                        ],
                        [
                            'four',
                            false,
                        ],
                    ],
                ],
                [
                    'image_path' => 'images/english/numbers/five-books-in-row.jpg',
                    'question' => 'ในภาพมีหนังสือกี่เล่ม?',
                    'explanation' => 'ภาพมีหนังสือห้าเล่ม ใช้คำว่า five',
                    'answers' => [
                        [
                            'four',
                            false,
                        ],
                        [
                            'five',
                            true,
                        ],
                        [
                            'six',
                            false,
                        ],
                        [
                            'three',
                            false,
                        ],
                    ],
                ],
            ],
        ],
    ],
];
