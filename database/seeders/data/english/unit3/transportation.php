<?php

return [
    'Transportation' => [
        'vocabulary' => [
            ['bus', 'bʌs', 'รถบัส', 'I go to school by bus.', 'ไอ โก ทู สคูล บาย บัส', 'ฉันไปโรงเรียนโดยรถบัส'],
            ['train', 'treɪn', 'รถไฟ', 'The train is here.', 'เดอะ เทรน อิซ เฮียร์', 'รถไฟมาแล้ว'],
            ['taxi', 'ˈtæksi', 'แท็กซี่', 'We need a taxi.', 'วี นีด อะ แท็กซี', 'เราต้องการแท็กซี่'],
            ['bicycle', 'ˈbaɪsɪkəl', 'จักรยาน', 'This is my bicycle.', 'ดิส อิซ มาย ไบซิเคิล', 'นี่คือจักรยานของฉัน'],
            ['car', 'kɑr', 'รถยนต์', 'Her car is red.', 'เฮอร์ คาร์ อิซ เรด', 'รถยนต์ของเธอเป็นสีแดง'],
            ['boat', 'boʊt', 'เรือ', 'The boat is on the river.', 'เดอะ โบท อิซ ออน เดอะ ริเวอร์', 'เรืออยู่ในแม่น้ำ'],
            ['plane', 'pleɪn', 'เครื่องบิน', 'We travel by plane.', 'วี แทรเวิล บาย เพลน', 'เราเดินทางโดยเครื่องบิน'],
            ['walk', 'wɔk', 'เดิน', 'I walk to the park.', 'ไอ วอล์ก ทู เดอะ พาร์ก', 'ฉันเดินไปสวนสาธารณะ'],
            ['drive', 'draɪv', 'ขับรถ', 'I can drive a car.', 'ไอ แคน ไดรฟ์ อะ คาร์', 'ฉันขับรถยนต์ได้'],
            ['ride', 'raɪd', 'ขี่', 'I ride my bicycle.', 'ไอ ไรด์ มาย ไบซิเคิล', 'ฉันขี่จักรยานของฉัน'],
            ['station', 'ˈsteɪʃən', 'สถานี', 'The train station is near my house.', 'เดอะ เทรน สเตเชิน อิซ เนียร์ มาย เฮาส์', 'สถานีรถไฟอยู่ใกล้บ้านของฉัน'],
            ['ticket', 'ˈtɪkət', 'ตั๋ว', 'I need one ticket.', 'ไอ นีด วัน ทิคเคิท', 'ฉันต้องการตั๋วหนึ่งใบ'],
            ['bus stop', 'ˈbʌs stɑp', 'ป้ายรถบัส', 'I am at the bus stop.', 'ไอ แอม แอท เดอะ บัส สต็อป', 'ฉันอยู่ที่ป้ายรถบัส'],
            ['get on', 'ɡɛt ɑn', 'ขึ้นรถหรือยานพาหนะ', 'Please get on the bus.', 'พลีซ เก็ท ออน เดอะ บัส', 'กรุณาขึ้นรถบัส'],
            ['get off', 'ɡɛt ɔf', 'ลงจากรถหรือยานพาหนะ', 'We get off at the next stop.', 'วี เก็ท ออฟ แอท เดอะ เน็กซ์ท สต็อป', 'เราลงรถที่ป้ายถัดไป'],
            ['late', 'leɪt', 'สาย; ช้ากว่ากำหนด', 'The bus is late.', 'เดอะ บัส อิซ เลท', 'รถบัสมาช้ากว่ากำหนด'],
        ],
        'exercises' => [
            'multiple_choice' => [
                [
                    'question' => 'คำว่า bus หมายถึงยานพาหนะชนิดใด',
                    'explanation' => 'bus หมายถึง รถบัส ซึ่งเป็นรถสำหรับผู้โดยสารหลายคน',
                    'answers' => [
                        ['รถบัส', true],
                        ['เรือ', false],
                        ['จักรยาน', false],
                        ['เครื่องบิน', false],
                    ],
                ],
                [
                    'question' => 'คุณจะเดินทางด้วยรถไฟ ควรพูดว่าอะไร',
                    'explanation' => 'by train หมายถึง โดยรถไฟ จึงใช้ I go by train.',
                    'answers' => [
                        ['I go by boat.', false],
                        ['I go by plane.', false],
                        ['I go by bicycle.', false],
                        ['I go by train.', true],
                    ],
                ],
                [
                    'question' => 'คุณต้องการเรียกรถแท็กซี่ ข้อใดเหมาะสม',
                    'explanation' => 'We need a taxi. หมายถึง เราต้องการแท็กซี่',
                    'answers' => [
                        ['We need a boat.', false],
                        ['We need a bicycle.', false],
                        ['We need a taxi.', true],
                        ['We need a ticket.', false],
                    ],
                ],
                [
                    'question' => 'ยานพาหนะที่บินบนท้องฟ้าเรียกว่าอะไร',
                    'explanation' => 'plane หมายถึง เครื่องบิน ซึ่งเป็นยานพาหนะที่บินบนท้องฟ้า',
                    'answers' => [
                        ['a car', false],
                        ['a plane', true],
                        ['a train', false],
                        ['a bus', false],
                    ],
                ],
                [
                    'question' => 'คำใดหมายถึงยานพาหนะที่เดินทางบนผิวน้ำ',
                    'explanation' => 'boat หมายถึง เรือ ใช้เดินทางบนผิวน้ำ',
                    'answers' => [
                        ['boat', true],
                        ['train', false],
                        ['taxi', false],
                        ['bus', false],
                    ],
                ],
                [
                    'question' => 'พนักงานพูดว่า “Please get on the bus.” คุณควรทำอะไร',
                    'explanation' => 'get on the bus หมายถึง ขึ้นรถบัส',
                    'answers' => [
                        ['ลงจากรถบัส', false],
                        ['ซื้อตั๋วรถไฟ', false],
                        ['ขี่จักรยาน', false],
                        ['ขึ้นรถบัส', true],
                    ],
                ],
                [
                    'question' => '“We get off at the next stop.” หมายถึงอะไร',
                    'explanation' => 'get off หมายถึง ลงจากรถ ประโยคนี้บอกว่าเราจะลงที่ป้ายถัดไป',
                    'answers' => [
                        ['เรารอรถที่บ้าน', false],
                        ['เราขับรถไปสถานี', false],
                        ['เราลงรถที่ป้ายถัดไป', true],
                        ['เราขึ้นรถที่ป้ายถัดไป', false],
                    ],
                ],
                [
                    'question' => 'คุณต้องการตั๋วหนึ่งใบ ควรพูดว่าอะไร',
                    'explanation' => 'ticket หมายถึง ตั๋ว ดังนั้น I need one ticket. จึงหมายถึงฉันต้องการตั๋วหนึ่งใบ',
                    'answers' => [
                        ['I need one shirt.', false],
                        ['I need one ticket.', true],
                        ['I need one car.', false],
                        ['I need one bag.', false],
                    ],
                ],
            ],
            'fill_blank' => [
                [
                    'question' => 'This is my ____. (นี่คือจักรยานของฉัน)',
                    'explanation' => 'bicycle หมายถึง จักรยาน จึงตรงกับความหมายที่กำหนด',
                    'answers' => [
                        ['bicycle', true],
                        ['car', false],
                        ['boat', false],
                        ['ticket', false],
                    ],
                ],
                [
                    'question' => 'Her ____ is red. (รถยนต์ของเธอเป็นสีแดง)',
                    'explanation' => 'car หมายถึง รถยนต์ และใช้เป็นประธานของประโยคนี้',
                    'answers' => [
                        ['train', false],
                        ['bicycle', false],
                        ['boat', false],
                        ['car', true],
                    ],
                ],
                [
                    'question' => 'I ____ to the park. (ฉันเดินไปสวนสาธารณะ)',
                    'explanation' => 'walk หมายถึง เดิน ใช้ในประโยค I walk to the park.',
                    'answers' => [
                        ['ride', false],
                        ['fly', false],
                        ['walk', true],
                        ['drive', false],
                    ],
                ],
                [
                    'question' => 'I can ____ a car. (ฉันขับรถยนต์ได้)',
                    'explanation' => 'drive หมายถึง ขับรถ และวางหลัง can ในรูปคำกริยาเดิม',
                    'answers' => [
                        ['read', false],
                        ['drive', true],
                        ['walk', false],
                        ['swim', false],
                    ],
                ],
                [
                    'question' => 'I ____ my bicycle. (ฉันขี่จักรยานของฉัน)',
                    'explanation' => 'ride ใช้กับการขี่จักรยาน จึงเติมเป็น I ride my bicycle.',
                    'answers' => [
                        ['ride', true],
                        ['drive', false],
                        ['walk', false],
                        ['fly', false],
                    ],
                ],
                [
                    'question' => 'The train ____ is near my house. (สถานีรถไฟอยู่ใกล้บ้านของฉัน)',
                    'explanation' => 'train station หมายถึง สถานีรถไฟ จึงเติม station',
                    'answers' => [
                        ['ticket', false],
                        ['car', false],
                        ['boat', false],
                        ['station', true],
                    ],
                ],
                [
                    'question' => 'I wait at the ____. (ฉันรอที่ป้ายรถบัส)',
                    'explanation' => 'bus stop หมายถึง ป้ายรถบัส ซึ่งเป็นสถานที่รอรถบัส',
                    'answers' => [
                        ['airport', false],
                        ['park', false],
                        ['bus stop', true],
                        ['train station', false],
                    ],
                ],
                [
                    'question' => 'The bus is ____. (รถบัสมาช้ากว่ากำหนด)',
                    'explanation' => 'late หมายถึง ช้ากว่ากำหนด ในประโยคนี้บอกว่ารถบัสมาช้า',
                    'answers' => [
                        ['cheap', false],
                        ['late', true],
                        ['red', false],
                        ['large', false],
                    ],
                ],
            ],
            'listening' => [
                [
                    'audio_path' => 'audio/english/transportation/go-to-school-by-bus.mp3',
                    'question' => 'ฟังเสียงแล้วตอบว่าผู้พูดไปโรงเรียนอย่างไร',
                    'explanation' => 'ผู้พูดบอกว่า I go to school by bus. จึงไปโรงเรียนโดยรถบัส',
                    'answers' => [
                        ['By bus.', true],
                        ['By train.', false],
                        ['By taxi.', false],
                        ['On foot.', false],
                    ],
                ],
                [
                    'audio_path' => 'audio/english/transportation/train-leaves-at-nine.mp3',
                    'question' => 'ฟังเสียงแล้วตอบว่ารถไฟออกกี่โมง',
                    'vocabulary_mode' => 'after_vocabulary',
                    'required_vocabulary_words' => ['train'],
                    'explanation' => 'เสียงพูดว่า The train leaves at nine o’clock. จึงตอบว่าเก้าโมง',
                    'answers' => [
                        ['At eight o’clock.', false],
                        ['At ten o’clock.', false],
                        ['At eleven o’clock.', false],
                        ['At nine o’clock.', true],
                    ],
                ],
            ],
            'image_choice' => [
                [
                    'image_path' => 'images/english/transportation/blue-bicycle.jpg',
                    'question' => 'ยานพาหนะในภาพเรียกว่าอะไร',
                    'explanation' => 'ภาพแสดงจักรยาน ซึ่งภาษาอังกฤษเรียกว่า a bicycle',
                    'answers' => [
                        ['a plane', false],
                        ['a train', false],
                        ['a bicycle', true],
                        ['a boat', false],
                    ],
                ],
                [
                    'image_path' => 'images/english/transportation/person-walking-on-sidewalk.jpg',
                    'question' => 'คนในภาพกำลังทำอะไร',
                    'vocabulary_mode' => 'after_vocabulary',
                    'required_vocabulary_words' => ['walk'],
                    'explanation' => 'คนในภาพกำลังเดินบนทางเท้า จึงเลือก walking',
                    'answers' => [
                        ['flying a plane', false],
                        ['walking', true],
                        ['driving a car', false],
                        ['riding a bicycle', false],
                    ],
                ],
            ],
        ],
    ],
];
