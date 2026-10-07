<?php

return [
    'Travel & Hotel' => [
        'vocabulary' => [
            ['travel', 'แทรเวิล', 'เดินทาง; ท่องเที่ยว', 'I like to travel with my family.', 'ไอ ไลก์ ทู แทรเวิล วิธ มาย แฟมิลี', 'ฉันชอบเดินทางกับครอบครัว'],
            ['trip', 'ทริป', 'การเดินทาง; ทริป', 'Our trip starts tomorrow.', 'เอาเออร์ ทริป สตาร์ตส์ ทูมอโร', 'การเดินทางของเราเริ่มพรุ่งนี้'],
            ['hotel', 'โฮเทล', 'โรงแรม', 'This hotel is near the station.', 'ดิส โฮเทล อิซ เนียร์ เดอะ สเตชัน', 'โรงแรมนี้อยู่ใกล้สถานี'],
            ['room', 'รูม', 'ห้อง', 'Our room is on the second floor.', 'เอาเออร์ รูม อิซ ออน เดอะ เซเคินด์ ฟลอร์', 'ห้องของเราอยู่ชั้นสอง'],
            ['single room', 'ซิงเกิล รูม', 'ห้องพักสำหรับหนึ่งคน', 'I would like a single room.', 'ไอ วูด ไลก์ อะ ซิงเกิล รูม', 'ฉันต้องการห้องพักสำหรับหนึ่งคน'],
            ['double room', 'ดับเบิล รูม', 'ห้องพักสำหรับสองคน', 'We would like a double room.', 'วี วูด ไลก์ อะ ดับเบิล รูม', 'เราต้องการห้องพักสำหรับสองคน'],
            ['book', 'บุ๊ก', 'จอง', 'Can I book a room?', 'แคน ไอ บุ๊ก อะ รูม', 'ฉันจองห้องพักได้ไหม'],
            ['night', 'ไนต์', 'คืน', 'We will stay for one night.', 'วี วิล สเตย์ ฟอร์ วัน ไนต์', 'เราจะพักหนึ่งคืน'],
            ['key', 'คี', 'กุญแจ', 'Here is your room key.', 'เฮียร์ อิซ ยัวร์ รูม คี', 'นี่คือกุญแจห้องของคุณ'],
            ['passport', 'แพสพอร์ต', 'หนังสือเดินทาง', 'My passport is in my bag.', 'มาย แพสพอร์ต อิซ อิน มาย แบก', 'หนังสือเดินทางของฉันอยู่ในกระเป๋า'],
            ['luggage', 'ลักกิจ', 'สัมภาระ', 'My luggage is in the car.', 'มาย ลักกิจ อิซ อิน เดอะ คาร์', 'สัมภาระของฉันอยู่ในรถ'],
            ['front desk', 'ฟรันต์ เดสก์', 'เคาน์เตอร์ต้อนรับ', 'Please ask at the front desk.', 'พลีซ แอสก์ แอท เดอะ ฟรันต์ เดสก์', 'กรุณาสอบถามที่เคาน์เตอร์ต้อนรับ'],
            ['check in', 'เช็ก อิน', 'ลงทะเบียนเข้าพัก', 'We check in at three.', 'วี เช็ก อิน แอท ธรี', 'เราลงทะเบียนเข้าพักตอนสามโมง'],
            ['check out', 'เช็ก เอาต์', 'คืนห้องพัก; เช็กเอาต์', 'We check out at ten.', 'วี เช็ก เอาต์ แอท เทน', 'เราคืนห้องพักตอนสิบโมง'],
            ['airport', 'แอร์พอร์ต', 'สนามบิน', 'The bus goes to the airport.', 'เดอะ บัส โกซ ทู ดิ แอร์พอร์ต', 'รถบัสไปสนามบิน'],
            ['ticket', 'ทิคเค็ต', 'ตั๋ว', 'I need a train ticket.', 'ไอ นีด อะ เทรน ทิคิต', 'ฉันต้องการตั๋วรถไฟ'],
        ],
        'exercises' => [
            'multiple_choice' => [
                [
                    'question' => 'คำว่า travel ในประโยค I like to travel. หมายถึงอะไร?',
                    'explanation' => 'travel หมายถึงเดินทางหรือท่องเที่ยว',
                    'answers' => [
                        ['เดินทาง', true],
                        ['พักผ่อน', false],
                        ['ไอ', false],
                        ['จ่ายเงิน', false],
                    ],
                ],
                [
                    'question' => 'คำว่า hotel คือสถานที่ใด?',
                    'explanation' => 'hotel หมายถึงโรงแรม',
                    'answers' => [
                        ['โรงพยาบาล', false],
                        ['สนามบิน', false],
                        ['โรงแรม', true],
                        ['สถานีรถไฟ', false],
                    ],
                ],
                [
                    'question' => 'คุณเดินทางคนเดียวและต้องการห้องพักสำหรับหนึ่งคน ควรขอห้องประเภทใด?',
                    'explanation' => 'single room เป็นห้องพักสำหรับหนึ่งคน',
                    'answers' => [
                        ['A double room.', false],
                        ['A single room.', true],
                        ['A classroom.', false],
                        ['A meeting room.', false],
                    ],
                ],
                [
                    'question' => 'คุณกับเพื่อนต้องการพักในห้องเดียวกันสำหรับสองคน ควรขออะไร?',
                    'explanation' => 'double room เป็นห้องพักสำหรับสองคน',
                    'answers' => [
                        ['A single room.', false],
                        ['A train ticket.', false],
                        ['A passport.', false],
                        ['A double room.', true],
                    ],
                ],
                [
                    'question' => 'Can I book a room? หมายถึงอะไร?',
                    'explanation' => 'book ในบริบทนี้เป็นคำกริยาหมายถึงจอง ประโยคนี้ขอจองห้องพัก',
                    'answers' => [
                        ['ฉันอ่านหนังสือได้ไหม', false],
                        ['ห้องอยู่ที่ไหน', false],
                        ['ฉันจองห้องพักได้ไหม', true],
                        ['ฉันยืมกุญแจได้ไหม', false],
                    ],
                ],
                [
                    'question' => 'คำว่า passport หมายถึงเอกสารใด?',
                    'explanation' => 'passport หมายถึงหนังสือเดินทาง',
                    'answers' => [
                        ['หนังสือเดินทาง', true],
                        ['ตั๋วรถไฟ', false],
                        ['ใบเสร็จ', false],
                        ['บัตรนักเรียน', false],
                    ],
                ],
                [
                    'question' => 'คุณต้องการรับกุญแจห้องพัก พนักงานบอกว่า Please ask at the front desk. คุณควรไปที่ใด?',
                    'explanation' => 'front desk หมายถึงเคาน์เตอร์ต้อนรับของโรงแรม',
                    'answers' => [
                        ['ห้องครัว', false],
                        ['สระว่ายน้ำ', false],
                        ['ป้ายรถเมล์', false],
                        ['เคาน์เตอร์ต้อนรับ', true],
                    ],
                ],
                [
                    'question' => 'คำว่า airport หมายถึงอะไร?',
                    'explanation' => 'airport หมายถึงสนามบิน',
                    'answers' => [
                        ['สวนสาธารณะ', false],
                        ['สนามบิน', true],
                        ['ห้องพัก', false],
                        ['โรงพยาบาล', false],
                    ],
                ],
            ],
            'fill_blank' => [
                [
                    'question' => 'เติมคำให้ตรงกับความหมาย “การเดินทางของเราเริ่มพรุ่งนี้”: Our ___ starts tomorrow.',
                    'explanation' => 'trip หมายถึงการเดินทางหรือทริป',
                    'answers' => [
                        ['key', false],
                        ['medicine', false],
                        ['shirt', false],
                        ['trip', true],
                    ],
                ],
                [
                    'question' => 'เติมคำให้ตรงกับความหมาย “ห้องของเราอยู่ชั้นสอง”: Our ___ is on the second floor.',
                    'explanation' => 'room หมายถึงห้อง',
                    'answers' => [
                        ['cough', false],
                        ['room', true],
                        ['ticket', false],
                        ['night', false],
                    ],
                ],
                [
                    'question' => 'เติมคำให้ตรงกับความหมาย “เราจะพักหนึ่งคืน”: We will stay for one ___.',
                    'explanation' => 'night หมายถึงคืน',
                    'answers' => [
                        ['night', true],
                        ['airport', false],
                        ['passport', false],
                        ['doctor', false],
                    ],
                ],
                [
                    'question' => 'เติมคำให้ตรงกับความหมาย “นี่คือกุญแจห้องของคุณ”: Here is your room ___.',
                    'explanation' => 'key หมายถึงกุญแจ',
                    'answers' => [
                        ['luggage', false],
                        ['trip', false],
                        ['key', true],
                        ['fever', false],
                    ],
                ],
                [
                    'question' => 'เติมคำให้ตรงกับความหมาย “สัมภาระของฉันอยู่ในรถ”: My ___ is in the car.',
                    'explanation' => 'luggage หมายถึงสัมภาระ และใช้ is เพราะเป็นคำนามนับไม่ได้',
                    'answers' => [
                        ['weather', false],
                        ['luggage', true],
                        ['fever', false],
                        ['age', false],
                    ],
                ],
                [
                    'question' => 'เติมคำให้ตรงกับความหมาย “เราลงทะเบียนเข้าพักตอนสามโมง”: We ___ at three.',
                    'explanation' => 'check in หมายถึงลงทะเบียนเข้าพัก',
                    'answers' => [
                        ['check out', false],
                        ['get off', false],
                        ['turn left', false],
                        ['check in', true],
                    ],
                ],
                [
                    'question' => 'เติมคำให้ตรงกับความหมาย “เราคืนห้องพักตอนสิบโมง”: We ___ at ten.',
                    'explanation' => 'check out หมายถึงคืนห้องพักหรือเช็กเอาต์',
                    'answers' => [
                        ['check in', false],
                        ['get on', false],
                        ['check out', true],
                        ['turn right', false],
                    ],
                ],
                [
                    'question' => 'เติมคำให้ตรงกับความหมาย “ฉันต้องการตั๋วรถไฟ”: I need a train ___.',
                    'explanation' => 'ticket หมายถึงตั๋ว',
                    'answers' => [
                        ['ticket', true],
                        ['room', false],
                        ['key', false],
                        ['hand', false],
                    ],
                ],
            ],
            'listening' => [
                [
                    'audio_path' => 'audio/english/travel-hotel/book-single-room-two-nights.mp3',
                    'question' => 'จากเสียง ผู้พูดต้องการพักกี่คืน?',
                    'vocabulary_mode' => 'after_vocabulary',
                    'required_vocabulary_words' => ['night'],
                    'explanation' => 'เสียงบอกว่า for two nights. จึงต้องการพักสองคืน',
                    'answers' => [
                        ['หนึ่งคืน', false],
                        ['สองคืน', true],
                        ['สามคืน', false],
                        ['สี่คืน', false],
                    ],
                ],
                [
                    'audio_path' => 'audio/english/travel-hotel/check-out-ten-then-airport.mp3',
                    'question' => 'จากเสียง หลังคืนห้องพักแล้ว ผู้พูดจะไปที่ไหน?',
                    'vocabulary_mode' => 'after_vocabulary',
                    'required_vocabulary_words' => ['airport'],
                    'explanation' => 'ผู้พูดบอกว่า Then we go to the airport. จึงจะไปสนามบิน',
                    'answers' => [
                        ['โรงเรียน', false],
                        ['ตลาด', false],
                        ['โรงพยาบาล', false],
                        ['สนามบิน', true],
                    ],
                ],
            ],
            'image_choice' => [
                [
                    'image_path' => 'images/english/travel-hotel/guest-receiving-key-at-front-desk.jpg',
                    'question' => 'นักเดินทางในภาพกำลังรับกุญแจห้องที่บริเวณใด?',
                    'explanation' => 'เคาน์เตอร์ที่พนักงานต้อนรับมอบกุญแจให้แขกคือ front desk',
                    'answers' => [
                        ['In the hotel kitchen.', false],
                        ['At the swimming pool.', false],
                        ['At the front desk.', true],
                        ['In the bathroom.', false],
                    ],
                ],
                [
                    'image_path' => 'images/english/travel-hotel/two-suitcases-and-travel-bag.jpg',
                    'question' => 'คำใดตรงกับสิ่งของหลักในภาพ?',
                    'explanation' => 'กระเป๋าเดินทางและกระเป๋าที่นำมาเดินทางเรียกรวมว่า luggage',
                    'answers' => [
                        ['Luggage.', true],
                        ['Medicine.', false],
                        ['Food.', false],
                        ['Clothes on hangers.', false],
                    ],
                ],
            ],
        ],
    ],
];
