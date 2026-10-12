<?php

return [
    'Family' => [
        'vocabulary' => [
            [
                'mother',
                'มาเธอร์',
                'แม่',
                'My mother is kind.',
                'มาย มาเธอร์ อิซ ไคน์ด',
                'แม่ของฉันใจดี',
            ],
            [
                'father',
                'ฟาเธอร์',
                'พ่อ',
                'My father is at home.',
                'มาย ฟาเธอร์ อิซ แอท โฮม',
                'พ่อของฉันอยู่ที่บ้าน',
            ],
            [
                'parents',
                'แพเรนต์ส',
                'พ่อแม่',
                'My parents live in Bangkok.',
                'มาย แพเรนต์ส ลิฟ อิน แบงค็อก',
                'พ่อแม่ของฉันอาศัยอยู่ในกรุงเทพฯ',
            ],
            [
                'sister',
                'ซิสเทอร์',
                'พี่สาว; น้องสาว',
                'I have one sister.',
                'ไอ แฮฟ วัน ซิสเทอร์',
                'ฉันมีพี่สาวหรือน้องสาวหนึ่งคน',
            ],
            [
                'brother',
                'บราเธอร์',
                'พี่ชาย; น้องชาย',
                'My brother is ten.',
                'มาย บราเธอร์ อิซ เทน',
                'พี่ชายหรือน้องชายของฉันอายุสิบปี',
            ],
            [
                'grandmother',
                'แกรนด์มาเธอร์',
                'ย่า; ยาย',
                'My grandmother likes tea.',
                'มาย แกรนด์มาเธอร์ ไลก์ส ที',
                'ย่าหรือยายของฉันชอบชา',
            ],
            [
                'grandfather',
                'แกรนด์ฟาเธอร์',
                'ปู่; ตา',
                'My grandfather reads a book.',
                'มาย แกรนด์ฟาเธอร์ รีดส์ อะ บุค',
                'ปู่หรือตาของฉันอ่านหนังสือ',
            ],
            [
                'family',
                'แฟมิลี',
                'ครอบครัว',
                'I love my family.',
                'ไอ ลัฟ มาย แฟมิลี',
                'ฉันรักครอบครัวของฉัน',
            ],
            [
                'child',
                'ไชลด์',
                'เด็กหนึ่งคน; ลูกหนึ่งคน',
                'The child is happy.',
                'เดอะ ไชลด์ อิซ แฮปปี',
                'เด็กคนนั้นมีความสุข',
            ],
            [
                'children',
                'ชิลเดรน',
                'เด็กหลายคน; ลูกหลายคน',
                'The children are at school.',
                'เดอะ ชิลเดรน อาร์ แอท สคูล',
                'เด็ก ๆ อยู่ที่โรงเรียน',
            ],
            [
                'son',
                'ซัน',
                'ลูกชาย',
                'Their son is a student.',
                'แดร์ ซัน อิซ อะ สตูเดนต์',
                'ลูกชายของพวกเขาเป็นนักเรียน',
            ],
            [
                'daughter',
                'ดอเทอร์',
                'ลูกสาว',
                'Their daughter is five.',
                'แดร์ ดอเทอร์ อิซ ไฟฟ์',
                'ลูกสาวของพวกเขาอายุห้าปี',
            ],
            [
                'aunt',
                'แอนต์',
                'ป้า; น้า; อาผู้หญิง',
                'My aunt lives near us.',
                'มาย แอนต์ ลิฟส์ เนียร์ อัส',
                'ป้า น้า หรืออาผู้หญิงของฉันอาศัยอยู่ใกล้เรา',
            ],
            [
                'uncle',
                'อังเคิล',
                'ลุง; น้า; อาผู้ชาย',
                'My uncle is a teacher.',
                'มาย อังเคิล อิซ อะ ทีเชอร์',
                'ลุง น้า หรืออาผู้ชายของฉันเป็นครู',
            ],
            [
                'cousin',
                'คัซเซิน',
                'ลูกพี่ลูกน้อง',
                'My cousin plays with me.',
                'มาย คัซเซิน เพลย์ส วิธ มี',
                'ลูกพี่ลูกน้องของฉันเล่นกับฉัน',
            ],
        ],
        'exercises' => [
            'multiple_choice' => [
                [
                    'question' => 'Your female parent is your ___.',
                    'explanation' => 'mother หมายถึง แม่ ซึ่งเป็นผู้ปกครองผู้หญิง',
                    'answers' => [
                        [
                            'mother',
                            true,
                        ],
                        [
                            'father',
                            false,
                        ],
                        [
                            'brother',
                            false,
                        ],
                        [
                            'uncle',
                            false,
                        ],
                    ],
                ],
                [
                    'question' => 'Your male parent is your ___.',
                    'explanation' => 'father หมายถึง พ่อ ซึ่งเป็นผู้ปกครองผู้ชาย',
                    'answers' => [
                        [
                            'mother',
                            false,
                        ],
                        [
                            'father',
                            true,
                        ],
                        [
                            'sister',
                            false,
                        ],
                        [
                            'aunt',
                            false,
                        ],
                    ],
                ],
                [
                    'question' => 'Which word means พ่อแม่?',
                    'explanation' => 'parents หมายถึง พ่อแม่',
                    'answers' => [
                        [
                            'cousins',
                            false,
                        ],
                        [
                            'friends',
                            false,
                        ],
                        [
                            'parents',
                            true,
                        ],
                        [
                            'children',
                            false,
                        ],
                    ],
                ],
                [
                    'question' => 'Anna and Ben have the same parents. Anna is a girl. Anna is Ben\'s ___.',
                    'explanation' => 'เด็กผู้หญิงที่มีพ่อแม่เดียวกันเป็น sister หรือพี่สาวหรือน้องสาว',
                    'answers' => [
                        [
                            'daughter',
                            false,
                        ],
                        [
                            'mother',
                            false,
                        ],
                        [
                            'aunt',
                            false,
                        ],
                        [
                            'sister',
                            true,
                        ],
                    ],
                ],
                [
                    'question' => 'Anna and Ben have the same parents. Ben is a boy. Ben is Anna\'s ___.',
                    'explanation' => 'เด็กผู้ชายที่มีพ่อแม่เดียวกันเป็น brother หรือพี่ชายหรือน้องชาย',
                    'answers' => [
                        [
                            'brother',
                            true,
                        ],
                        [
                            'father',
                            false,
                        ],
                        [
                            'uncle',
                            false,
                        ],
                        [
                            'son',
                            false,
                        ],
                    ],
                ],
                [
                    'question' => 'Your mother\'s mother is your ___.',
                    'explanation' => 'แม่ของแม่คือ grandmother หรือยาย',
                    'answers' => [
                        [
                            'grandfather',
                            false,
                        ],
                        [
                            'grandmother',
                            true,
                        ],
                        [
                            'sister',
                            false,
                        ],
                        [
                            'daughter',
                            false,
                        ],
                    ],
                ],
                [
                    'question' => 'Your father\'s father is your ___.',
                    'explanation' => 'พ่อของพ่อคือ grandfather หรือปู่',
                    'answers' => [
                        [
                            'brother',
                            false,
                        ],
                        [
                            'son',
                            false,
                        ],
                        [
                            'grandfather',
                            true,
                        ],
                        [
                            'grandmother',
                            false,
                        ],
                    ],
                ],
                [
                    'question' => '"I love my family." คำว่า family หมายถึงอะไร?',
                    'explanation' => 'family หมายถึง ครอบครัว',
                    'answers' => [
                        [
                            'ร้านค้า',
                            false,
                        ],
                        [
                            'โรงเรียน',
                            false,
                        ],
                        [
                            'ประเทศ',
                            false,
                        ],
                        [
                            'ครอบครัว',
                            true,
                        ],
                    ],
                ],
                [
                    'question' => 'Which word refers to one young person?',
                    'explanation' => 'child เป็นเอกพจน์ หมายถึง เด็กหนึ่งคน',
                    'answers' => [
                        [
                            'child',
                            true,
                        ],
                        [
                            'children',
                            false,
                        ],
                        [
                            'parents',
                            false,
                        ],
                        [
                            'adults',
                            false,
                        ],
                    ],
                ],
                [
                    'question' => 'What is the plural form of child?',
                    'explanation' => 'รูปพหูพจน์ของ child คือ children',
                    'answers' => [
                        [
                            'childs',
                            false,
                        ],
                        [
                            'children',
                            true,
                        ],
                        [
                            'childes',
                            false,
                        ],
                        [
                            'child',
                            false,
                        ],
                    ],
                ],
                [
                    'question' => 'A mother\'s male child is her ___.',
                    'explanation' => 'son หมายถึง ลูกชาย',
                    'answers' => [
                        [
                            'sister',
                            false,
                        ],
                        [
                            'aunt',
                            false,
                        ],
                        [
                            'son',
                            true,
                        ],
                        [
                            'daughter',
                            false,
                        ],
                    ],
                ],
                [
                    'question' => 'A father\'s female child is his ___.',
                    'explanation' => 'daughter หมายถึง ลูกสาว',
                    'answers' => [
                        [
                            'uncle',
                            false,
                        ],
                        [
                            'son',
                            false,
                        ],
                        [
                            'brother',
                            false,
                        ],
                        [
                            'daughter',
                            true,
                        ],
                    ],
                ],
                [
                    'question' => 'Your mother\'s sister is your ___.',
                    'explanation' => 'พี่สาวหรือน้องสาวของแม่เป็น aunt หรือป้าหรือน้าผู้หญิง',
                    'answers' => [
                        [
                            'aunt',
                            true,
                        ],
                        [
                            'uncle',
                            false,
                        ],
                        [
                            'grandfather',
                            false,
                        ],
                        [
                            'son',
                            false,
                        ],
                    ],
                ],
                [
                    'question' => 'Your father\'s brother is your ___.',
                    'explanation' => 'พี่ชายหรือน้องชายของพ่อเป็น uncle หรือลุงหรืออาผู้ชาย',
                    'answers' => [
                        [
                            'aunt',
                            false,
                        ],
                        [
                            'uncle',
                            true,
                        ],
                        [
                            'grandmother',
                            false,
                        ],
                        [
                            'daughter',
                            false,
                        ],
                    ],
                ],
                [
                    'question' => 'Your aunt\'s child is your ___.',
                    'explanation' => 'ลูกของป้า น้า หรืออาเป็น cousin หรือลูกพี่ลูกน้อง',
                    'answers' => [
                        [
                            'grandfather',
                            false,
                        ],
                        [
                            'mother',
                            false,
                        ],
                        [
                            'cousin',
                            true,
                        ],
                        [
                            'parent',
                            false,
                        ],
                    ],
                ],
            ],
            'fill_blank' => [
                [
                    'question' => 'เติมคำว่าแม่: "My ___ is kind."',
                    'explanation' => 'mother หมายถึง แม่ ประโยคนี้หมายถึง แม่ของฉันใจดี',
                    'answers' => [
                        [
                            'father',
                            false,
                        ],
                        [
                            'uncle',
                            false,
                        ],
                        [
                            'brother',
                            false,
                        ],
                        [
                            'mother',
                            true,
                        ],
                    ],
                ],
                [
                    'question' => 'เติมคำว่าพ่อ: "My ___ is at home."',
                    'explanation' => 'father หมายถึง พ่อ',
                    'answers' => [
                        [
                            'father',
                            true,
                        ],
                        [
                            'mother',
                            false,
                        ],
                        [
                            'aunt',
                            false,
                        ],
                        [
                            'sister',
                            false,
                        ],
                    ],
                ],
                [
                    'question' => 'เติมคำว่าพ่อแม่: "My ___ live in Bangkok."',
                    'explanation' => 'parents เป็นพหูพจน์ หมายถึง พ่อแม่ และใช้กับ live',
                    'answers' => [
                        [
                            'daughter',
                            false,
                        ],
                        [
                            'parents',
                            true,
                        ],
                        [
                            'child',
                            false,
                        ],
                        [
                            'son',
                            false,
                        ],
                    ],
                ],
                [
                    'question' => 'I have a female sibling. She is my ___.',
                    'explanation' => 'sister หมายถึง พี่สาวหรือน้องสาว',
                    'answers' => [
                        [
                            'uncle',
                            false,
                        ],
                        [
                            'brother',
                            false,
                        ],
                        [
                            'sister',
                            true,
                        ],
                        [
                            'father',
                            false,
                        ],
                    ],
                ],
                [
                    'question' => 'I have a male sibling. He is my ___.',
                    'explanation' => 'brother หมายถึง พี่ชายหรือน้องชาย',
                    'answers' => [
                        [
                            'sister',
                            false,
                        ],
                        [
                            'mother',
                            false,
                        ],
                        [
                            'aunt',
                            false,
                        ],
                        [
                            'brother',
                            true,
                        ],
                    ],
                ],
                [
                    'question' => 'My mother\'s mother is my ___.',
                    'explanation' => 'แม่ของแม่คือ grandmother หรือยาย',
                    'answers' => [
                        [
                            'grandmother',
                            true,
                        ],
                        [
                            'grandfather',
                            false,
                        ],
                        [
                            'son',
                            false,
                        ],
                        [
                            'cousin',
                            false,
                        ],
                    ],
                ],
                [
                    'question' => 'My mother\'s father is my ___.',
                    'explanation' => 'พ่อของแม่คือ grandfather หรือตา',
                    'answers' => [
                        [
                            'daughter',
                            false,
                        ],
                        [
                            'grandfather',
                            true,
                        ],
                        [
                            'sister',
                            false,
                        ],
                        [
                            'grandmother',
                            false,
                        ],
                    ],
                ],
                [
                    'question' => 'เติมคำว่าครอบครัว: "I love my ___."',
                    'explanation' => 'family หมายถึง ครอบครัว',
                    'answers' => [
                        [
                            'number',
                            false,
                        ],
                        [
                            'clock',
                            false,
                        ],
                        [
                            'family',
                            true,
                        ],
                        [
                            'milk',
                            false,
                        ],
                    ],
                ],
                [
                    'question' => 'There is one ___ in the room.',
                    'explanation' => 'one ใช้กับคำนามเอกพจน์ child หมายถึง เด็กหนึ่งคน',
                    'answers' => [
                        [
                            'children',
                            false,
                        ],
                        [
                            'parents',
                            false,
                        ],
                        [
                            'friends',
                            false,
                        ],
                        [
                            'child',
                            true,
                        ],
                    ],
                ],
                [
                    'question' => 'There are two ___ in the room.',
                    'explanation' => 'two ใช้กับคำนามพหูพจน์ children หมายถึง เด็กสองคน',
                    'answers' => [
                        [
                            'children',
                            true,
                        ],
                        [
                            'child',
                            false,
                        ],
                        [
                            'mother',
                            false,
                        ],
                        [
                            'father',
                            false,
                        ],
                    ],
                ],
                [
                    'question' => 'My father\'s sister is my ___.',
                    'explanation' => 'พี่สาวหรือน้องสาวของพ่อคือ aunt หรือป้าหรืออาผู้หญิง',
                    'answers' => [
                        [
                            'grandfather',
                            false,
                        ],
                        [
                            'aunt',
                            true,
                        ],
                        [
                            'son',
                            false,
                        ],
                        [
                            'uncle',
                            false,
                        ],
                    ],
                ],
                [
                    'question' => 'My mother\'s brother is my ___.',
                    'explanation' => 'พี่ชายหรือน้องชายของแม่คือ uncle หรือลุงหรือน้าผู้ชาย',
                    'answers' => [
                        [
                            'daughter',
                            false,
                        ],
                        [
                            'aunt',
                            false,
                        ],
                        [
                            'uncle',
                            true,
                        ],
                        [
                            'grandmother',
                            false,
                        ],
                    ],
                ],
            ],
            'listening' => [
                [
                    'audio_path' => 'audio/english/family/one-brother-and-one-sister.mp3',
                    'question' => 'จากเสียง ผู้พูดมีพี่น้องอย่างไร?',
                    'explanation' => 'เสียงบอกว่ามี brother หนึ่งคนและ sister หนึ่งคน',
                    'answers' => [
                        [
                            'two brothers',
                            false,
                        ],
                        [
                            'two sisters',
                            false,
                        ],
                        [
                            'one cousin',
                            false,
                        ],
                        [
                            'one brother and one sister',
                            true,
                        ],
                    ],
                ],
                [
                    'audio_path' => 'audio/english/family/cousin-is-a-student.mp3',
                    'question' => 'จากเสียง แอนนาเป็นอะไรกับผู้พูด?',
                    'explanation' => 'ผู้พูดแนะนำแอนนาว่า my cousin หรือลูกพี่ลูกน้อง',
                    'answers' => [
                        [
                            'a cousin',
                            true,
                        ],
                        [
                            'a grandmother',
                            false,
                        ],
                        [
                            'an aunt',
                            false,
                        ],
                        [
                            'a mother',
                            false,
                        ],
                    ],
                ],
            ],
            'image_choice' => [
                [
                    'image_path' => 'images/english/family/family-together-at-home.jpg',
                    'question' => 'คำใดเหมาะกับกลุ่มคนในภาพมากที่สุด?',
                    'explanation' => 'family หมายถึง ครอบครัว ตรงกับภาพคนในครอบครัวอยู่ร่วมกัน',
                    'answers' => [
                        [
                            'food',
                            false,
                        ],
                        [
                            'family',
                            true,
                        ],
                        [
                            'clock',
                            false,
                        ],
                        [
                            'country',
                            false,
                        ],
                    ],
                ],
                [
                    'image_path' => 'images/english/family/children-playing-together.jpg',
                    'question' => 'คำใดใช้เรียกเด็กสองคนในภาพ?',
                    'explanation' => 'children เป็นรูปพหูพจน์ของ child ใช้เรียกเด็กหลายคน',
                    'answers' => [
                        [
                            'parents',
                            false,
                        ],
                        [
                            'grandparents',
                            false,
                        ],
                        [
                            'children',
                            true,
                        ],
                        [
                            'child',
                            false,
                        ],
                    ],
                ],
            ],
        ],
    ],
];
