<?php

return [
    'Daily Routine' => [
        'vocabulary' => [
            ['wake up', 'weɪk ʌp', 'ตื่นนอน', 'I wake up at six.', 'ไอ เวค อัพ แอท ซิกซ์', 'ฉันตื่นนอนตอนหกโมง'],
            ['get up', 'ɡɛt ʌp', 'ลุกจากที่นอน', 'I get up after I wake up.', 'ไอ เกท อัพ แอฟเทอร์ ไอ เวค อัพ', 'ฉันลุกจากที่นอนหลังจากตื่น'],
            ['brush', 'brʌʃ', 'แปรง', 'I brush my teeth every morning.', 'ไอ บรัช มาย ทีธ เอฟรี มอร์นิง', 'ฉันแปรงฟันทุกเช้า'],
            ['wash', 'wɑʃ', 'ล้าง; ซัก', 'I wash my face.', 'ไอ วอช มาย เฟซ', 'ฉันล้างหน้า'],
            ['breakfast', 'ˈbrɛkfəst', 'อาหารเช้า', 'I eat breakfast at seven.', 'ไอ อีท เบรคเฟิสต์ แอท เซเวน', 'ฉันกินอาหารเช้าตอนเจ็ดโมง'],
            ['lunch', 'lʌntʃ', 'อาหารกลางวัน', 'We have lunch at noon.', 'วี แฮฟ ลันช์ แอท นูน', 'เรากินอาหารกลางวันตอนเที่ยง'],
            ['dinner', 'ˈdɪnər', 'อาหารเย็น', 'We eat dinner together.', 'วี อีท ดินเนอร์ ทูเกเธอร์', 'เรากินอาหารเย็นด้วยกัน'],
            ['go to work', 'ɡoʊ tə wɜrk', 'ไปทำงาน', 'I go to work by bus.', 'ไอ โก ทู เวิร์ก บาย บัส', 'ฉันไปทำงานโดยรถเมล์'],
            ['go home', 'ɡoʊ hoʊm', 'กลับบ้าน', 'I go home at five.', 'ไอ โก โฮม แอท ไฟฟ์', 'ฉันกลับบ้านตอนห้าโมง'],
            ['cook', 'kʊk', 'ทำอาหาร', 'I cook dinner for my family.', 'ไอ คุก ดินเนอร์ ฟอร์ มาย แฟมิลี', 'ฉันทำอาหารเย็นให้ครอบครัว'],
            ['clean', 'klin', 'ทำความสะอาด', 'I clean my room on Sunday.', 'ไอ คลีน มาย รูม ออน ซันเดย์', 'ฉันทำความสะอาดห้องในวันอาทิตย์'],
            ['take a shower', 'teɪk ə ˈʃaʊər', 'อาบน้ำฝักบัว', 'I take a shower before bed.', 'ไอ เทค อะ เชาเออร์ บิฟอร์ เบด', 'ฉันอาบน้ำฝักบัวก่อนเข้านอน'],
            ['sleep', 'slip', 'นอนหลับ', 'I sleep for eight hours.', 'ไอ สลีพ ฟอร์ เอท เอาเออร์ซ', 'ฉันนอนหลับแปดชั่วโมง'],
            ['every day', 'ˈɛvri deɪ', 'ทุกวัน', 'I walk to school every day.', 'ไอ วอล์ก ทู สคูล เอฟรี เดย์', 'ฉันเดินไปโรงเรียนทุกวัน'],
            ['before', 'bɪˈfɔr', 'ก่อน', 'I wash my hands before lunch.', 'ไอ วอช มาย แฮนด์ซ บิฟอร์ ลันช์', 'ฉันล้างมือก่อนอาหารกลางวัน'],
            ['after', 'ˈæftər', 'หลัง; หลังจาก', 'I brush my teeth after breakfast.', 'ไอ บรัช มาย ทีธ แอฟเทอร์ เบรคเฟิสต์', 'ฉันแปรงฟันหลังอาหารเช้า'],
        ],
        'exercises' => [
            'multiple_choice' => [
                [
                    'question' => 'ประโยค I wake up at six. หมายถึงอะไร',
                    'explanation' => 'wake up หมายถึงตื่นนอน ประโยคนี้จึงบอกเวลาที่ตื่น',
                    'answers' => [
                        ['ฉันเข้านอนตอนหกโมง', false],
                        ['ฉันตื่นนอนตอนหกโมง', true],
                        ['ฉันกลับบ้านตอนหกโมง', false],
                        ['ฉันกินอาหารเย็นตอนหกโมง', false],
                    ],
                ],
                [
                    'question' => 'You are awake but still in bed. You leave the bed. What do you do?',
                    'explanation' => 'get up คือการลุกจากที่นอน ส่วน wake up คือตื่นจากการนอน',
                    'answers' => [
                        ['get up', true],
                        ['wake up', false],
                        ['go home', false],
                        ['sleep', false],
                    ],
                ],
                [
                    'question' => 'Which action means ล้างหน้า?',
                    'explanation' => 'wash your face หมายถึงล้างหน้า ส่วน brush your teeth คือแปรงฟัน',
                    'answers' => [
                        ['Brush your teeth.', false],
                        ['Clean your room.', false],
                        ['Cook your dinner.', false],
                        ['Wash your face.', true],
                    ],
                ],
                [
                    'question' => 'Which meal do people usually eat at noon?',
                    'explanation' => 'lunch คืออาหารกลางวัน ซึ่งมักกินในช่วงเที่ยง',
                    'answers' => [
                        ['dinner', false],
                        ['dessert', false],
                        ['lunch', true],
                        ['breakfast', false],
                    ],
                ],
                [
                    'question' => 'คำว่า go to work หมายถึงอะไร',
                    'explanation' => 'go to work เป็นวลีที่ใช้บอกว่าไปทำงาน',
                    'answers' => [
                        ['ไปนอน', false],
                        ['ทำอาหาร', false],
                        ['ไปทำงาน', true],
                        ['กลับบ้าน', false],
                    ],
                ],
                [
                    'question' => 'Your work is finished. You leave the office and return to your house. What do you do?',
                    'explanation' => 'เมื่อกลับจากที่ทำงานไปยังบ้าน ใช้ go home ซึ่งหมายถึงกลับบ้าน',
                    'answers' => [
                        ['take a shower', false],
                        ['go home', true],
                        ['go to work', false],
                        ['get up', false],
                    ],
                ],
                [
                    'question' => 'You use soap and water under a shower. Which phrase describes this?',
                    'explanation' => 'take a shower หมายถึงอาบน้ำฝักบัว',
                    'answers' => [
                        ['take a shower', true],
                        ['cook dinner', false],
                        ['clean a desk', false],
                        ['brush your hair', false],
                    ],
                ],
                [
                    'question' => 'ประโยค I do this every day. หมายถึงทำบ่อยแค่ไหน',
                    'explanation' => 'every day หมายถึงทุกวัน และเขียนแยกเป็นสองคำเมื่อบอกความถี่',
                    'answers' => [
                        ['ทุกสัปดาห์', false],
                        ['เดือนละครั้ง', false],
                        ['ปีละครั้ง', false],
                        ['ทุกวัน', true],
                    ],
                ],
            ],
            'fill_blank' => [
                [
                    'question' => 'Complete: I use a toothbrush to _____ my teeth.',
                    'explanation' => 'ใช้ brush กับ teeth เพื่อบอกว่าแปรงฟัน',
                    'answers' => [
                        ['wash', false],
                        ['cook', false],
                        ['sleep', false],
                        ['brush', true],
                    ],
                ],
                [
                    'question' => 'Complete: My first meal in the morning is _____.',
                    'explanation' => 'breakfast คืออาหารเช้า ซึ่งเป็นมื้อแรกของวัน',
                    'answers' => [
                        ['dinner', false],
                        ['a snack', false],
                        ['breakfast', true],
                        ['lunch', false],
                    ],
                ],
                [
                    'question' => 'Complete: Our evening meal is _____.',
                    'explanation' => 'dinner คืออาหารเย็น จึงตรงกับ evening meal',
                    'answers' => [
                        ['water', false],
                        ['dinner', true],
                        ['breakfast', false],
                        ['lunch', false],
                    ],
                ],
                [
                    'question' => 'Complete: I put rice and vegetables in a pan to _____ dinner.',
                    'explanation' => 'cook หมายถึงทำอาหาร การใส่ข้าวและผักลงในกระทะเป็นขั้นตอนการทำอาหาร',
                    'answers' => [
                        ['cook', true],
                        ['wash', false],
                        ['sleep', false],
                        ['brush', false],
                    ],
                ],
                [
                    'question' => 'Complete: My room is dirty. I need to _____ it.',
                    'explanation' => 'เมื่อห้องสกปรก ต้อง clean it คือทำความสะอาดห้อง',
                    'answers' => [
                        ['clean', true],
                        ['sleep', false],
                        ['eat', false],
                        ['brush', false],
                    ],
                ],
                [
                    'question' => 'Complete: To rest through the night, I _____ for eight hours.',
                    'explanation' => 'sleep for eight hours หมายถึงนอนหลับเป็นเวลาแปดชั่วโมง จึงตรงกับการพักผ่อนตลอดคืน',
                    'answers' => [
                        ['cook', false],
                        ['wash', false],
                        ['clean', false],
                        ['sleep', true],
                    ],
                ],
                [
                    'question' => 'เติมคำที่หมายถึงก่อน: I wash my hands _____ I eat.',
                    'explanation' => 'before หมายถึงก่อน ประโยคนี้บอกว่าล้างมือก่อนกินอาหาร',
                    'answers' => [
                        ['during', false],
                        ['until', false],
                        ['before', true],
                        ['after', false],
                    ],
                ],
                [
                    'question' => 'เติมคำที่หมายถึงหลังจาก: I go home _____ work.',
                    'explanation' => 'after work หมายถึงหลังเลิกงานหรือหลังจากทำงาน',
                    'answers' => [
                        ['without', false],
                        ['after', true],
                        ['before', false],
                        ['during', false],
                    ],
                ],
            ],
            'listening' => [
                [
                    'question' => 'ผู้พูดทำสิ่งใดก่อนออกไปทำงาน',
                    'explanation' => 'ผู้พูดกินอาหารเช้าตอนเจ็ดโมงก่อนออกไปทำงานตอนแปดโมง',
                    'answers' => [
                        ['Go to sleep.', false],
                        ['Eat breakfast.', true],
                        ['Eat dinner.', false],
                        ['Go home.', false],
                    ],
                    'audio_path' => 'audio/english/daily-routine/breakfast-before-work.mp3',
                ],
                [
                    'question' => 'ผู้พูดทำอะไรเป็นอย่างแรกตามเสียง',
                    'explanation' => 'ผู้พูดบอกว่าอาบน้ำฝักบัวก่อนเข้านอน แล้วจึงนอนหลับ',
                    'answers' => [
                        ['Take a shower.', true],
                        ['Go to sleep.', false],
                        ['Go to work.', false],
                        ['Cook lunch.', false],
                    ],
                    'audio_path' => 'audio/english/daily-routine/shower-before-bed.mp3',
                ],
            ],
            'image_choice' => [
                [
                    'question' => 'What is the person doing?',
                    'explanation' => 'คนในภาพกำลังใช้แปรงสีฟันแปรงฟัน จึงตรงกับ Brushing their teeth.',
                    'answers' => [
                        ['Cooking dinner.', false],
                        ['Cleaning a desk.', false],
                        ['Taking a shower.', false],
                        ['Brushing their teeth.', true],
                    ],
                    'image_path' => 'images/english/daily-routine/person-brushing-teeth.jpg',
                ],
                [
                    'question' => 'เลือกวลีที่ตรงกับการกระทำในภาพ',
                    'explanation' => 'คนในภาพกำลังปรุงอาหารในกระทะ คำว่า cooking food จึงตรงกับภาพ',
                    'answers' => [
                        ['Washing a face.', false],
                        ['Going to sleep.', false],
                        ['Cooking food.', true],
                        ['Eating breakfast.', false],
                    ],
                    'image_path' => 'images/english/daily-routine/person-cooking-vegetables.jpg',
                ],
            ],
        ],
    ],
];
