<?php

return [
    'Asking for Help' => [
        'vocabulary' => [
            ['help', 'hɛlp', 'ช่วย; ความช่วยเหลือ', 'Can you help me?', 'แคน ยู เฮลป์ มี', 'คุณช่วยฉันได้ไหม'],
            ['please', 'pliz', 'กรุณา; โปรด', 'Please wait here.', 'พลีซ เวต เฮียร์', 'กรุณารอที่นี่'],
            ['sorry', 'ˈsɑri', 'ขอโทษ; เสียใจ', 'Sorry, I am late.', 'ซอรี ไอ แอม เลท', 'ขอโทษ ฉันมาสาย'],
            ['excuse me', 'ɪkˈskjuz mi', 'ขอโทษนะ; ขอรบกวน', 'Excuse me, where is the station?', 'อิกสคิวซ มี แวร์ อิซ เดอะ สเตชัน', 'ขอโทษนะ สถานีอยู่ที่ไหน'],
            ['lost', 'lɔst', 'หลงทาง; สูญหาย', 'I am lost.', 'ไอ แอม ลอสต์', 'ฉันหลงทาง'],
            ['map', 'mæp', 'แผนที่', 'Can you show me on the map?', 'แคน ยู โช มี ออน เดอะ แมพ', 'คุณชี้ให้ฉันดูบนแผนที่ได้ไหม'],
            ['address', 'ˈæˌdrɛs', 'ที่อยู่', 'Please write the address.', 'พลีซ ไรต์ ดิ แอดเดรส', 'กรุณาเขียนที่อยู่'],
            ['repeat', 'rɪˈpit', 'พูดซ้ำ; ทำซ้ำ', 'Please repeat that.', 'พลีซ รีพีต แดท', 'กรุณาพูดซ้ำอีกครั้ง'],
            ['slowly', 'ˈsloʊli', 'อย่างช้า ๆ', 'Please speak slowly.', 'พลีซ สปีค สโลลี', 'กรุณาพูดช้า ๆ'],
            ['understand', 'ˌʌndɚˈstænd', 'เข้าใจ', 'I do not understand.', 'ไอ ดู นอต อันเดอร์สแตนด์', 'ฉันไม่เข้าใจ'],
            ['find', 'faɪnd', 'หาเจอ; พบ', 'I cannot find my bag.', 'ไอ แคนนอต ไฟนด์ มาย แบก', 'ฉันหากระเป๋าไม่เจอ'],
            ['phone', 'foʊn', 'โทรศัพท์', 'My phone is in my bag.', 'มาย โฟน อิซ อิน มาย แบก', 'โทรศัพท์ของฉันอยู่ในกระเป๋า'],
            ['wallet', 'ˈwɑlɪt', 'กระเป๋าสตางค์', 'I lost my wallet.', 'ไอ ลอสต์ มาย วอลิต', 'ฉันทำกระเป๋าสตางค์หาย'],
            ['police', 'pəˈlis', 'ตำรวจ', 'Please call the police.', 'พลีซ คอล เดอะ พะลีซ', 'กรุณาโทรแจ้งตำรวจ'],
            ['problem', 'ˈprɑbləm', 'ปัญหา', 'I have a problem.', 'ไอ แฮฟ อะ พรอบเลิม', 'ฉันมีปัญหา'],
            ['thank you', 'ˈθæŋk ju', 'ขอบคุณ', 'Thank you for your help.', 'แธงก์ ยู ฟอร์ ยัวร์ เฮลป์', 'ขอบคุณสำหรับความช่วยเหลือของคุณ'],
        ],
        'exercises' => [
            'multiple_choice' => [
                [
                    'question' => 'คุณต้องการให้คนอื่นช่วย ควรพูดประโยคใด?',
                    'explanation' => 'Can you help me? เป็นการขอความช่วยเหลือ',
                    'answers' => [
                        ['I like this shirt.', false],
                        ['It is sunny today.', false],
                        ['Can you help me?', true],
                        ['I am eighteen.', false],
                    ],
                ],
                [
                    'question' => 'ก่อนถามทางกับคนที่ไม่รู้จัก ควรใช้คำใดเพื่อเรียกความสนใจอย่างสุภาพ?',
                    'explanation' => 'Excuse me. ใช้เรียกความสนใจอย่างสุภาพก่อนถามหรือขอรบกวน',
                    'answers' => [
                        ['Excuse me.', true],
                        ['Good night.', false],
                        ['See you tomorrow.', false],
                        ['You are welcome.', false],
                    ],
                ],
                [
                    'question' => 'I am lost. หมายถึงอะไร?',
                    'explanation' => 'lost ในประโยคนี้หมายถึงหลงทาง',
                    'answers' => [
                        ['ฉันหิว', false],
                        ['ฉันมาถึงแล้ว', false],
                        ['ฉันกำลังนอน', false],
                        ['ฉันหลงทาง', true],
                    ],
                ],
                [
                    'question' => 'คำว่า map หมายถึงอะไร?',
                    'explanation' => 'map หมายถึงแผนที่ ใช้ดูตำแหน่งและเส้นทาง',
                    'answers' => [
                        ['กระเป๋าสตางค์', false],
                        ['แผนที่', true],
                        ['ตั๋ว', false],
                        ['โทรศัพท์', false],
                    ],
                ],
                [
                    'question' => 'Please write the address. ขอให้ผู้ฟังทำอะไร?',
                    'explanation' => 'address หมายถึงที่อยู่ ประโยคนี้ขอให้เขียนที่อยู่',
                    'answers' => [
                        ['เขียนที่อยู่', true],
                        ['อ่านชื่อผู้พูด', false],
                        ['โทรหาเพื่อน', false],
                        ['เปิดประตู', false],
                    ],
                ],
                [
                    'question' => 'คำว่า wallet หมายถึงสิ่งใด?',
                    'explanation' => 'wallet หมายถึงกระเป๋าสตางค์',
                    'answers' => [
                        ['กระเป๋าเดินทาง', false],
                        ['หนังสือเดินทาง', false],
                        ['กระเป๋าสตางค์', true],
                        ['แผนที่', false],
                    ],
                ],
                [
                    'question' => 'คำว่า police หมายถึงใคร?',
                    'explanation' => 'police หมายถึงตำรวจ',
                    'answers' => [
                        ['แพทย์', false],
                        ['ตำรวจ', true],
                        ['ครู', false],
                        ['พนักงานต้อนรับ', false],
                    ],
                ],
                [
                    'question' => 'คนอื่นช่วยบอกทางให้คุณแล้ว ควรพูดอะไร?',
                    'explanation' => 'Thank you. ใช้ขอบคุณผู้ที่ช่วยเรา',
                    'answers' => [
                        ['I am lost.', false],
                        ['Where is my wallet?', false],
                        ['I cannot find it.', false],
                        ['Thank you.', true],
                    ],
                ],
            ],
            'fill_blank' => [
                [
                    'question' => 'เติมคำให้ตรงกับความหมาย “กรุณารอที่นี่”: ___ wait here.',
                    'explanation' => 'Please ใช้ขอให้ผู้อื่นทำสิ่งใดอย่างสุภาพ',
                    'answers' => [
                        ['Today', false],
                        ['Please', true],
                        ['Very', false],
                        ['Because', false],
                    ],
                ],
                [
                    'question' => 'เติมคำให้ตรงกับความหมาย “ขอโทษ ฉันมาสาย”: ___, I am late.',
                    'explanation' => 'Sorry ใช้ขอโทษ ในที่นี้ขอโทษที่มาสาย',
                    'answers' => [
                        ['Hello', false],
                        ['Welcome', false],
                        ['Thanks', false],
                        ['Sorry', true],
                    ],
                ],
                [
                    'question' => 'เติมคำให้ตรงกับความหมาย “กรุณาพูดซ้ำอีกครั้ง”: Please ___ that.',
                    'explanation' => 'repeat หมายถึงพูดซ้ำหรือทำซ้ำ',
                    'answers' => [
                        ['carry', false],
                        ['close', false],
                        ['repeat', true],
                        ['buy', false],
                    ],
                ],
                [
                    'question' => 'เติมคำให้ตรงกับความหมาย “กรุณาพูดช้า ๆ”: Please speak ___.',
                    'explanation' => 'slowly หมายถึงอย่างช้า ๆ',
                    'answers' => [
                        ['slowly', true],
                        ['loudly', false],
                        ['quickly', false],
                        ['quietly', false],
                    ],
                ],
                [
                    'question' => 'เติมคำให้ตรงกับความหมาย “ฉันไม่เข้าใจ”: I do not ___.',
                    'explanation' => 'understand หมายถึงเข้าใจ',
                    'answers' => [
                        ['swim', false],
                        ['cook', false],
                        ['sleep', false],
                        ['understand', true],
                    ],
                ],
                [
                    'question' => 'เติมคำให้ตรงกับความหมาย “ฉันหากระเป๋าไม่เจอ”: I cannot ___ my bag.',
                    'explanation' => 'find หมายถึงหาเจอ I cannot find my bag. จึงแปลว่าหากระเป๋าไม่เจอ',
                    'answers' => [
                        ['eat', false],
                        ['find', true],
                        ['drink', false],
                        ['wear', false],
                    ],
                ],
                [
                    'question' => 'เติมคำให้ตรงกับความหมาย “โทรศัพท์ของฉันอยู่ในกระเป๋า”: My ___ is in my bag.',
                    'explanation' => 'phone หมายถึงโทรศัพท์',
                    'answers' => [
                        ['phone', true],
                        ['school', false],
                        ['station', false],
                        ['teacher', false],
                    ],
                ],
                [
                    'question' => 'เติมคำให้ตรงกับความหมาย “ฉันมีปัญหา”: I have a ___.',
                    'explanation' => 'problem หมายถึงปัญหา',
                    'answers' => [
                        ['map', false],
                        ['ticket', false],
                        ['problem', true],
                        ['key', false],
                    ],
                ],
            ],
            'listening' => [
                [
                    'audio_path' => 'audio/english/asking-for-help/cannot-find-wallet-phone-in-bag.mp3',
                    'question' => 'จากเสียง ผู้พูดหาอะไรไม่เจอ?',
                    'vocabulary_mode' => 'after_vocabulary',
                    'required_vocabulary_words' => ['wallet'],
                    'explanation' => 'ผู้พูดบอกว่า I cannot find my wallet. ส่วนโทรศัพท์ยังอยู่ในกระเป๋า จึงหากระเป๋าสตางค์ไม่เจอ',
                    'answers' => [
                        ['โทรศัพท์', false],
                        ['หนังสือเดินทาง', false],
                        ['กุญแจ', false],
                        ['กระเป๋าสตางค์', true],
                    ],
                ],
                [
                    'audio_path' => 'audio/english/asking-for-help/speak-slowly-write-address.mp3',
                    'question' => 'จากเสียง ผู้พูดขอให้ผู้ฟังเขียนอะไร?',
                    'vocabulary_mode' => 'after_vocabulary',
                    'required_vocabulary_words' => ['address'],
                    'explanation' => 'Can you write the address? เป็นการขอให้เขียนที่อยู่',
                    'answers' => [
                        ['ชื่อเพื่อน', false],
                        ['ที่อยู่', true],
                        ['ราคา', false],
                        ['หมายเลขห้อง', false],
                    ],
                ],
            ],
            'image_choice' => [
                [
                    'image_path' => 'images/english/asking-for-help/traveler-asking-directions-with-map.jpg',
                    'question' => 'นักเดินทางที่ถือแผนที่ในภาพน่าจะพูดประโยคใด?',
                    'explanation' => 'ภาพแสดงนักเดินทางขอความช่วยเหลือเรื่องเส้นทาง จึงเหมาะกับ Can you help me find the station?',
                    'answers' => [
                        ['Can you help me find the station?', true],
                        ['Can I have a glass of water?', false],
                        ['I would like a red shirt.', false],
                        ['I have a fever.', false],
                    ],
                ],
                [
                    'image_path' => 'images/english/asking-for-help/traveler-speaking-to-police-officer.jpg',
                    'question' => 'นักเดินทางในภาพกำลังขอความช่วยเหลือจากใคร?',
                    'explanation' => 'บุคคลในเครื่องแบบตำรวจคือ A police officer.',
                    'answers' => [
                        ['A doctor.', false],
                        ['A teacher.', false],
                        ['A police officer.', true],
                        ['A cook.', false],
                    ],
                ],
            ],
        ],
    ],
];
