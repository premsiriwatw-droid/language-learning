<?php

return [
    'Health & Body' => [
        'vocabulary' => [
            ['body', 'ˈbɑdi', 'ร่างกาย', 'My body needs rest.', 'มาย บอดี นีดส์ เรสต์', 'ร่างกายของฉันต้องการการพักผ่อน'],
            ['head', 'hɛd', 'ศีรษะ', 'My head hurts.', 'มาย เฮด เฮิร์ตส์', 'ฉันปวดศีรษะ'],
            ['arm', 'ɑrm', 'แขน', 'My left arm hurts.', 'มาย เลฟต์ อาร์ม เฮิร์ตส์', 'แขนซ้ายของฉันเจ็บ'],
            ['hand', 'hænd', 'มือ', 'Please wash your hands.', 'พลีซ วอช ยัวร์ แฮนด์ส', 'กรุณาล้างมือ'],
            ['leg', 'lɛɡ', 'ขา', 'My right leg hurts.', 'มาย ไรต์ เลก เฮิร์ตส์', 'ขาขวาของฉันเจ็บ'],
            ['foot', 'fʊt', 'เท้า', 'My left foot hurts.', 'มาย เลฟต์ ฟุต เฮิร์ตส์', 'เท้าซ้ายของฉันเจ็บ'],
            ['hurt', 'hɝt', 'เจ็บ; ปวด', 'Does your arm hurt?', 'ดัซ ยัวร์ อาร์ม เฮิร์ต', 'แขนของคุณเจ็บไหม'],
            ['sick', 'sɪk', 'ป่วย', 'I feel sick today.', 'ไอ ฟีล ซิก ทูเดย์', 'วันนี้ฉันรู้สึกไม่สบาย'],
            ['fever', 'ˈfivɚ', 'ไข้', 'I have a fever.', 'ไอ แฮฟ อะ ฟีเวอร์', 'ฉันมีไข้'],
            ['cough', 'kɔf', 'ไอ', 'I have a cough.', 'ไอ แฮฟ อะ คอฟ', 'ฉันมีอาการไอ'],
            ['doctor', 'ˈdɑktɚ', 'แพทย์; หมอ', 'I need to see a doctor.', 'ไอ นีด ทู ซี อะ ดอกเทอร์', 'ฉันต้องไปพบแพทย์'],
            ['medicine', 'ˈmɛdəsən', 'ยา', 'This medicine is for my cough.', 'ดิส เมดิซิน อิซ ฟอร์ มาย คอฟ', 'ยานี้สำหรับอาการไอของฉัน'],
            ['rest', 'rɛst', 'พักผ่อน', 'I want to rest at home.', 'ไอ วอนต์ ทู เรสต์ แอท โฮม', 'ฉันอยากพักผ่อนที่บ้าน'],
            ['better', 'ˈbɛtɚ', 'ดีขึ้น', 'I feel better today.', 'ไอ ฟีล เบเทอร์ ทูเดย์', 'วันนี้ฉันรู้สึกดีขึ้น'],
            ['hospital', 'ˈhɑspɪtəl', 'โรงพยาบาล', 'The hospital is near my house.', 'เดอะ ฮอสพิเทิล อิซ เนียร์ มาย เฮาส์', 'โรงพยาบาลอยู่ใกล้บ้านของฉัน'],
            ['healthy', 'ˈhɛlθi', 'สุขภาพดี', 'She is healthy.', 'ชี อิซ เฮลธี', 'เธอมีสุขภาพดี'],
        ],
        'exercises' => [
            'multiple_choice' => [
                [
                    'question' => 'คำว่า head หมายถึงส่วนใดของร่างกาย?',
                    'explanation' => 'head หมายถึงศีรษะ',
                    'answers' => [
                        ['แขน', false],
                        ['ศีรษะ', true],
                        ['ขา', false],
                        ['มือ', false],
                    ],
                ],
                [
                    'question' => 'คำว่า foot หมายถึงอะไร?',
                    'explanation' => 'foot หมายถึงเท้าหนึ่งข้าง ส่วนรูปพหูพจน์คือ feet',
                    'answers' => [
                        ['มือ', false],
                        ['ศีรษะ', false],
                        ['แขน', false],
                        ['เท้า', true],
                    ],
                ],
                [
                    'question' => 'เพื่อนถามว่า Does your arm hurt? เขากำลังถามอะไร?',
                    'explanation' => 'hurt หมายถึงเจ็บหรือปวด ประโยคนี้ถามว่าแขนเจ็บไหม',
                    'answers' => [
                        ['แขนของคุณเจ็บไหม', true],
                        ['คุณล้างมือหรือยัง', false],
                        ['คุณอยากนอนหรือไม่', false],
                        ['คุณอยู่ที่โรงพยาบาลหรือไม่', false],
                    ],
                ],
                [
                    'question' => 'คำว่า fever ในประโยค I have a fever. หมายถึงอะไร?',
                    'explanation' => 'fever หมายถึงไข้ ประโยคนี้บอกว่าผู้พูดมีไข้',
                    'answers' => [
                        ['อาการไอ', false],
                        ['ยา', false],
                        ['ไข้', true],
                        ['เท้า', false],
                    ],
                ],
                [
                    'question' => 'I have a cough. บอกอาการใด?',
                    'explanation' => 'cough หมายถึงอาการไอ',
                    'answers' => [
                        ['ปวดขา', false],
                        ['มีอาการไอ', true],
                        ['หิว', false],
                        ['กระหายน้ำ', false],
                    ],
                ],
                [
                    'question' => 'หากต้องการพบแพทย์ ควรพูดประโยคใด?',
                    'explanation' => 'doctor หมายถึงแพทย์ I need to see a doctor. ใช้บอกว่าต้องการพบแพทย์',
                    'answers' => [
                        ['I need a train ticket.', false],
                        ['I want to buy a shirt.', false],
                        ['I want to read a map.', false],
                        ['I need to see a doctor.', true],
                    ],
                ],
                [
                    'question' => 'คำว่า medicine หมายถึงสิ่งใด?',
                    'explanation' => 'medicine หมายถึงยา',
                    'answers' => [
                        ['ยา', true],
                        ['กระเป๋าเดินทาง', false],
                        ['อาหาร', false],
                        ['เสื้อผ้า', false],
                    ],
                ],
                [
                    'question' => 'คำว่า hospital คือสถานที่ใด?',
                    'explanation' => 'hospital หมายถึงโรงพยาบาล',
                    'answers' => [
                        ['สนามบิน', false],
                        ['โรงแรม', false],
                        ['โรงพยาบาล', true],
                        ['ห้องสมุด', false],
                    ],
                ],
            ],
            'fill_blank' => [
                [
                    'question' => 'เติมคำให้ตรงกับความหมาย “ร่างกายของฉันต้องการการพักผ่อน”: My ___ needs rest.',
                    'explanation' => 'body หมายถึงร่างกาย จึงตรงกับความหมายที่กำหนด',
                    'answers' => [
                        ['ticket', false],
                        ['wallet', false],
                        ['body', true],
                        ['map', false],
                    ],
                ],
                [
                    'question' => 'เติมคำให้ตรงกับความหมาย “แขนซ้ายของฉันเจ็บ”: My left ___ hurts.',
                    'explanation' => 'arm หมายถึงแขน',
                    'answers' => [
                        ['arm', true],
                        ['room', false],
                        ['key', false],
                        ['cough', false],
                    ],
                ],
                [
                    'question' => 'เติมคำให้ตรงกับความหมาย “มือขวาของฉันเจ็บ”: My right ___ hurts.',
                    'explanation' => 'hand หมายถึงมือ',
                    'answers' => [
                        ['hotel', false],
                        ['fever', false],
                        ['doctor', false],
                        ['hand', true],
                    ],
                ],
                [
                    'question' => 'เติมคำให้ตรงกับความหมาย “ขาขวาของฉันเจ็บ”: My right ___ hurts.',
                    'explanation' => 'leg หมายถึงขา',
                    'answers' => [
                        ['medicine', false],
                        ['leg', true],
                        ['passport', false],
                        ['night', false],
                    ],
                ],
                [
                    'question' => 'เติมคำให้ตรงกับความหมาย “วันนี้ฉันรู้สึกไม่สบาย”: I feel ___ today.',
                    'explanation' => 'sick หมายถึงป่วยหรือไม่สบาย',
                    'answers' => [
                        ['sick', true],
                        ['early', false],
                        ['red', false],
                        ['open', false],
                    ],
                ],
                [
                    'question' => 'เติมคำให้ตรงกับความหมาย “ฉันอยากพักผ่อนที่บ้าน”: I want to ___ at home.',
                    'explanation' => 'rest หมายถึงพักผ่อน',
                    'answers' => [
                        ['spell', false],
                        ['pay', false],
                        ['rest', true],
                        ['book', false],
                    ],
                ],
                [
                    'question' => 'เติมคำให้ตรงกับความหมาย “วันนี้ฉันรู้สึกดีขึ้น”: I feel ___ today.',
                    'explanation' => 'better หมายถึงดีขึ้น ใช้เปรียบเทียบกับอาการก่อนหน้านี้',
                    'answers' => [
                        ['cold', false],
                        ['better', true],
                        ['late', false],
                        ['busy', false],
                    ],
                ],
                [
                    'question' => 'เติมคำให้ตรงกับความหมาย “เธอมีสุขภาพดี”: She is ___.',
                    'explanation' => 'healthy หมายถึงมีสุขภาพดี',
                    'answers' => [
                        ['lost', false],
                        ['hungry', false],
                        ['thirsty', false],
                        ['healthy', true],
                    ],
                ],
            ],
            'listening' => [
                [
                    'audio_path' => 'audio/english/health-body/my-head-hurts-need-rest.mp3',
                    'question' => 'จากเสียง ผู้พูดปวดส่วนใด?',
                    'vocabulary_mode' => 'after_vocabulary',
                    'required_vocabulary_words' => ['head'],
                    'explanation' => 'ผู้พูดบอกว่า My head hurts. จึงปวดศีรษะ',
                    'answers' => [
                        ['แขน', false],
                        ['ขา', false],
                        ['ศีรษะ', true],
                        ['เท้า', false],
                    ],
                ],
                [
                    'audio_path' => 'audio/english/health-body/fever-going-to-hospital.mp3',
                    'question' => 'จากเสียง ผู้พูดกำลังจะไปที่ไหน?',
                    'vocabulary_mode' => 'after_vocabulary',
                    'required_vocabulary_words' => ['hospital'],
                    'explanation' => 'เสียงบอกว่า I am going to the hospital. จึงกำลังจะไปโรงพยาบาล',
                    'answers' => [
                        ['โรงพยาบาล', true],
                        ['โรงแรม', false],
                        ['โรงเรียน', false],
                        ['สนามบิน', false],
                    ],
                ],
            ],
            'image_choice' => [
                [
                    'image_path' => 'images/english/health-body/doctor-examining-patient.jpg',
                    'question' => 'บุคคลที่ใช้หูฟังตรวจคนไข้ในภาพมีอาชีพอะไร?',
                    'explanation' => 'doctor หมายถึงแพทย์ ภาพแสดงแพทย์กำลังตรวจคนไข้',
                    'answers' => [
                        ['A teacher.', false],
                        ['A doctor.', true],
                        ['A driver.', false],
                        ['A cook.', false],
                    ],
                ],
                [
                    'image_path' => 'images/english/health-body/person-coughing-into-elbow.jpg',
                    'question' => 'ประโยคใดตรงกับการกระทำของบุคคลในภาพ?',
                    'vocabulary_mode' => 'after_vocabulary',
                    'required_vocabulary_words' => ['cough'],
                    'explanation' => 'ภาพแสดงคนกำลังไอ ประโยค She is coughing. หมายถึงเธอกำลังไอ',
                    'answers' => [
                        ['She is cooking.', false],
                        ['She is swimming.', false],
                        ['She is reading.', false],
                        ['She is coughing.', true],
                    ],
                ],
            ],
        ],
    ],
];
