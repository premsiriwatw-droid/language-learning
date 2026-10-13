<?php

return [
    'Places & Directions' => [
        'vocabulary' => [
            ['hospital', 'ฮอสพิทัล', 'โรงพยาบาล', 'The hospital is near here.', 'เดอะ ฮอสพิเทิล อิซ เนียร์ เฮียร์', 'โรงพยาบาลอยู่ใกล้ที่นี่'],
            ['bank', 'แบงก์', 'ธนาคาร', 'I am at the bank.', 'ไอ แอม แอท เดอะ แบงก์', 'ฉันอยู่ที่ธนาคาร'],
            ['school', 'สคูล', 'โรงเรียน', 'My school is small.', 'มาย สคูล อิซ สมอล', 'โรงเรียนของฉันเล็ก'],
            ['park', 'พาร์ก', 'สวนสาธารณะ', 'We walk in the park.', 'วี วอล์ก อิน เดอะ พาร์ก', 'เราเดินในสวนสาธารณะ'],
            ['market', 'มาร์คิท', 'ตลาด', 'I buy fruit at the market.', 'ไอ บาย ฟรูต แอท เดอะ มาร์คิท', 'ฉันซื้อผลไม้ที่ตลาด'],
            ['restaurant', 'เรสเทอรอนท์', 'ร้านอาหาร', 'This restaurant is open.', 'ดิส เรสเทอรอนท์ อิซ โอเพิน', 'ร้านอาหารนี้เปิดอยู่'],
            ['library', 'ไลบรารี', 'ห้องสมุด', 'We read in the library.', 'วี รีด อิน เดอะ ไลแบรรี', 'เราอ่านหนังสือในห้องสมุด'],
            ['post office', 'โพสต์ ออฟฟิซ', 'ที่ทำการไปรษณีย์', 'Where is the post office?', 'แวร์ อิซ เดอะ โพสต์ ออฟฟิซ', 'ที่ทำการไปรษณีย์อยู่ที่ไหน'],
            ['near', 'เนียร์', 'ใกล้', 'The bank is near my house.', 'เดอะ แบงก์ อิซ เนียร์ มาย เฮาส์', 'ธนาคารอยู่ใกล้บ้านของฉัน'],
            ['far', 'ฟาร์', 'ไกล', 'The airport is far from here.', 'ดิ แอร์พอร์ต อิซ ฟาร์ ฟรอม เฮียร์', 'สนามบินอยู่ไกลจากที่นี่'],
            ['left', 'เลฟท์', 'ซ้าย', 'Turn left here.', 'เทิร์น เลฟท์ เฮียร์', 'เลี้ยวซ้ายตรงนี้'],
            ['right', 'ไรท์', 'ขวา', 'The park is on your right.', 'เดอะ พาร์ก อิซ ออน ยอร์ ไรท์', 'สวนสาธารณะอยู่ทางขวามือของคุณ'],
            ['straight', 'สเตรท', 'ตรงไป', 'Go straight.', 'โก สเตรท', 'ตรงไป'],
            ['opposite', 'ออพพะซิท', 'ตรงข้าม', 'The bank is opposite the school.', 'เดอะ แบงก์ อิซ ออเพอะซิท เดอะ สคูล', 'ธนาคารอยู่ตรงข้ามโรงเรียน'],
            ['between', 'บิทวีน', 'ระหว่าง', 'The park is between two shops.', 'เดอะ พาร์ก อิซ บิทวีน ทู ช็อปส์', 'สวนสาธารณะอยู่ระหว่างร้านค้าสองร้าน'],
            ['behind', 'บิไฮนด์', 'ข้างหลัง', 'The car is behind the house.', 'เดอะ คาร์ อิซ บิไฮนด์ เดอะ เฮาส์', 'รถยนต์อยู่ข้างหลังบ้าน'],
        ],
        'exercises' => [
            'multiple_choice' => [
                [
                    'question' => 'คุณไม่สบายและต้องการพบแพทย์ ควรไปสถานที่ใด',
                    'explanation' => 'hospital หมายถึง โรงพยาบาล เป็นสถานที่สำหรับพบแพทย์และรับการรักษา',
                    'answers' => [
                        ['a hospital', true],
                        ['a library', false],
                        ['a market', false],
                        ['a post office', false],
                    ],
                ],
                [
                    'question' => 'คำว่า bank หมายถึงสถานที่ใดในประโยค “I am at the bank.”',
                    'explanation' => 'ในประโยคนี้ bank หมายถึง ธนาคาร',
                    'answers' => [
                        ['โรงเรียน', false],
                        ['ร้านอาหาร', false],
                        ['สวนสาธารณะ', false],
                        ['ธนาคาร', true],
                    ],
                ],
                [
                    'question' => 'เด็กไปเรียนหนังสือที่สถานที่ใด',
                    'explanation' => 'school หมายถึง โรงเรียน ซึ่งเป็นสถานที่ที่เด็กไปเรียนหนังสือ',
                    'answers' => [
                        ['a market', false],
                        ['a post office', false],
                        ['a school', true],
                        ['a bank', false],
                    ],
                ],
                [
                    'question' => 'คุณอยากเดินเล่นในสวนสาธารณะ ควรพูดว่าอะไร',
                    'explanation' => 'park หมายถึง สวนสาธารณะ ประโยค I want to go to the park. จึงตรงกับสถานการณ์',
                    'answers' => [
                        ['I want to go to the library.', false],
                        ['I want to go to the park.', true],
                        ['I want to go to the bank.', false],
                        ['I want to go to the hospital.', false],
                    ],
                ],
                [
                    'question' => 'คำใดหมายถึง “ตลาด”',
                    'explanation' => 'market หมายถึง ตลาด เป็นสถานที่ซื้อขายสินค้าและอาหาร',
                    'answers' => [
                        ['market', true],
                        ['hospital', false],
                        ['school', false],
                        ['library', false],
                    ],
                ],
                [
                    'question' => 'คุณอยากนั่งกินอาหารในร้าน ควรไปที่ใด',
                    'explanation' => 'restaurant หมายถึง ร้านอาหาร เป็นสถานที่ที่ลูกค้านั่งสั่งและกินอาหารได้',
                    'answers' => [
                        ['a library', false],
                        ['a bank', false],
                        ['a post office', false],
                        ['a restaurant', true],
                    ],
                ],
                [
                    'question' => 'สถานที่สำหรับยืมและอ่านหนังสือเรียกว่าอะไร',
                    'explanation' => 'library หมายถึง ห้องสมุด ซึ่งมีหนังสือให้ยืมและอ่าน',
                    'answers' => [
                        ['a restaurant', false],
                        ['a hospital', false],
                        ['a library', true],
                        ['a market', false],
                    ],
                ],
                [
                    'question' => 'คุณต้องการส่งจดหมาย ควรถามหาสถานที่ใด',
                    'explanation' => 'post office หมายถึง ที่ทำการไปรษณีย์ เป็นสถานที่สำหรับส่งจดหมาย',
                    'answers' => [
                        ['the school', false],
                        ['the post office', true],
                        ['the park', false],
                        ['the library', false],
                    ],
                ],
            ],
            'fill_blank' => [
                [
                    'question' => 'The bank is ____ my house. (ธนาคารอยู่ใกล้บ้านของฉัน)',
                    'explanation' => 'near หมายถึง ใกล้ และวางหน้าสถานที่ได้โดยตรง',
                    'answers' => [
                        ['near', true],
                        ['behind', false],
                        ['opposite', false],
                        ['between', false],
                    ],
                ],
                [
                    'question' => 'The airport is ____ from here. (สนามบินอยู่ไกลจากที่นี่)',
                    'explanation' => 'far from หมายถึง ไกลจาก จึงเติม far ในประโยคนี้',
                    'answers' => [
                        ['near', false],
                        ['left', false],
                        ['straight', false],
                        ['far', true],
                    ],
                ],
                [
                    'question' => 'Turn ____ here. (เลี้ยวซ้ายตรงนี้)',
                    'explanation' => 'left หมายถึง ซ้าย จึงเติมเป็น Turn left here.',
                    'answers' => [
                        ['behind', false],
                        ['opposite', false],
                        ['left', true],
                        ['right', false],
                    ],
                ],
                [
                    'question' => 'The park is on your ____. (สวนสาธารณะอยู่ทางขวามือของคุณ)',
                    'explanation' => 'right หมายถึง ขวา และ on your right ใช้บอกว่าสถานที่อยู่ทางขวามือ',
                    'answers' => [
                        ['between', false],
                        ['right', true],
                        ['left', false],
                        ['straight', false],
                    ],
                ],
                [
                    'question' => 'Go ____. (ตรงไป)',
                    'explanation' => 'Go straight. เป็นคำสั่งบอกทางว่า ตรงไป',
                    'answers' => [
                        ['straight', true],
                        ['behind', false],
                        ['between', false],
                        ['opposite', false],
                    ],
                ],
                [
                    'question' => 'The bank is ____ the school. (ธนาคารอยู่ตรงข้ามโรงเรียน)',
                    'explanation' => 'opposite หมายถึง ตรงข้าม จึงตรงกับความหมายภาษาไทยที่กำหนด',
                    'answers' => [
                        ['near', false],
                        ['behind', false],
                        ['between', false],
                        ['opposite', true],
                    ],
                ],
                [
                    'question' => 'The park is ____ two shops. (สวนสาธารณะอยู่ระหว่างร้านค้าสองร้าน)',
                    'explanation' => 'between ใช้บอกว่าอยู่ระหว่างสองสิ่ง ในที่นี้คือร้านค้าสองร้าน',
                    'answers' => [
                        ['opposite', false],
                        ['near', false],
                        ['between', true],
                        ['behind', false],
                    ],
                ],
                [
                    'question' => 'The car is ____ the house. (รถยนต์อยู่ข้างหลังบ้าน)',
                    'explanation' => 'behind หมายถึง ข้างหลัง จึงเติมให้ได้ตำแหน่งตรงตามภาษาไทย',
                    'answers' => [
                        ['between', false],
                        ['behind', true],
                        ['opposite', false],
                        ['near', false],
                    ],
                ],
            ],
            'listening' => [
                [
                    'audio_path' => 'audio/english/places-directions/hospital-on-your-left.mp3',
                    'question' => 'ฟังเสียงแล้วตอบว่าโรงพยาบาลอยู่ด้านใดของผู้ฟัง',
                    'explanation' => 'เสียงบอกว่า The hospital is on your left. จึงอยู่ทางซ้ายมือของผู้ฟัง',
                    'answers' => [
                        ['On your left.', true],
                        ['On your right.', false],
                        ['Behind you.', false],
                        ['Far from here.', false],
                    ],
                ],
                [
                    'audio_path' => 'audio/english/places-directions/library-opposite-the-park.mp3',
                    'question' => 'ฟังเสียงแล้วตอบว่าห้องสมุดอยู่ที่ไหนเมื่อเทียบกับสวนสาธารณะ',
                    'explanation' => 'ผู้พูดบอกว่า The library is opposite the park. จึงอยู่ตรงข้ามสวนสาธารณะ',
                    'answers' => [
                        ['Behind the park.', false],
                        ['Inside the park.', false],
                        ['Between two parks.', false],
                        ['Opposite the park.', true],
                    ],
                ],
            ],
            'image_choice' => [
                [
                    'image_path' => 'images/english/places-directions/park-with-playground.jpg',
                    'question' => 'สถานที่ในภาพคืออะไร',
                    'explanation' => 'ภาพแสดงสนามหญ้า ต้นไม้ และเด็กเล่นเครื่องเล่น จึงเป็นสวนสาธารณะหรือ a park',
                    'answers' => [
                        ['a post office', false],
                        ['a hospital', false],
                        ['a park', true],
                        ['a bank', false],
                    ],
                ],
                [
                    'image_path' => 'images/english/places-directions/library-reading-room.jpg',
                    'question' => 'สถานที่ในภาพเหมาะกับคำใดที่สุด',
                    'explanation' => 'ภาพแสดงชั้นหนังสือและคนอ่านหนังสือในห้องสมุด จึงเลือก a library',
                    'answers' => [
                        ['a hospital', false],
                        ['a library', true],
                        ['a restaurant', false],
                        ['a bank', false],
                    ],
                ],
            ],
        ],
    ],
];
