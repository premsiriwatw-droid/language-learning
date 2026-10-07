<?php

return [
    'Review & Daily Conversation' => [
        'vocabulary' => [
            ['morning', 'ˈmɔrnɪŋ', 'ตอนเช้า', 'I study in the morning.', 'ไอ สตัดี อิน เดอะ มอร์นิง', 'ฉันเรียนตอนเช้า'],
            ['evening', 'ˈivnɪŋ', 'ตอนเย็น', 'We eat dinner in the evening.', 'วี อีต ดินเนอร์ อิน ดิ อีฟนิง', 'เรากินอาหารเย็นตอนเย็น'],
            ['today', 'təˈdeɪ', 'วันนี้', 'I am at home today.', 'ไอ แอม แอท โฮม ทูเดย์', 'วันนี้ฉันอยู่บ้าน'],
            ['tomorrow', 'təˈmɑroʊ', 'พรุ่งนี้', 'I will see you tomorrow.', 'ไอ วิล ซี ยู ทูมอโร', 'ฉันจะพบคุณพรุ่งนี้'],
            ['busy', 'ˈbɪzi', 'ยุ่ง; ไม่ว่าง', 'I am busy today.', 'ไอ แอม บิซี ทูเดย์', 'วันนี้ฉันยุ่ง'],
            ['free', 'fri', 'ว่าง', 'Are you free this afternoon?', 'อาร์ ยู ฟรี ดิส แอฟเทอร์นูน', 'บ่ายนี้คุณว่างไหม'],
            ['together', 'təˈɡɛðɚ', 'ด้วยกัน', 'We study together.', 'วี สตัดี ทูเกเธอร์', 'เราเรียนด้วยกัน'],
            ['again', 'əˈɡɛn', 'อีกครั้ง', 'Please say that again.', 'พลีซ เซย์ แดท อะเกน', 'กรุณาพูดอีกครั้ง'],
            ['usually', 'ˈjuʒuəli', 'โดยปกติ; มักจะ', 'I usually walk to school.', 'ไอ ยูชวลลี วอก ทู สกูล', 'โดยปกติฉันเดินไปโรงเรียน'],
            ['sometimes', 'ˈsʌmˌtaɪmz', 'บางครั้ง', 'I sometimes take the bus.', 'ไอ ซัมไทม์ซ เทค เดอะ บัส', 'บางครั้งฉันนั่งรถบัส'],
            ['ready', 'ˈrɛdi', 'พร้อม', 'I am ready to go.', 'ไอ แอม เรดี ทู โก', 'ฉันพร้อมที่จะไปแล้ว'],
            ['wait', 'weɪt', 'รอ', 'Please wait for me.', 'พลีซ เวต ฟอร์ มี', 'กรุณารอฉัน'],
            ['meet', 'mit', 'พบ; พบกัน', 'Let us meet at the park.', 'เลต อัส มีต แอท เดอะ พาร์ก', 'เรามาพบกันที่สวนสาธารณะเถอะ'],
            ['remember', 'rɪˈmɛmbɚ', 'จำได้; อย่าลืม', 'I remember your name.', 'ไอ รีเมมเบอร์ ยัวร์ เนม', 'ฉันจำชื่อของคุณได้'],
            ['forget', 'fɚˈɡɛt', 'ลืม', 'Do not forget your bag.', 'ดู นอต ฟอร์เกต ยัวร์ แบก', 'อย่าลืมกระเป๋าของคุณ'],
            ['need', 'nid', 'จำเป็นต้อง; ต้องการ', 'I need an umbrella.', 'ไอ นีด แอน อัมเบรลละ', 'ฉันต้องการร่ม'],
            ['want', 'wɑnt', 'อยาก; ต้องการ', 'I want a cup of tea.', 'ไอ วอนต์ อะ คัพ ออฟ ที', 'ฉันอยากได้ชาหนึ่งถ้วย'],
            ['because', 'bɪˈkɔz', 'เพราะว่า', 'I stay home because I am sick.', 'ไอ สเตย์ โฮม บิคอซ ไอ แอม ซิก', 'ฉันอยู่บ้านเพราะฉันป่วย'],
        ],
        'exercises' => [
            'multiple_choice' => [
                [
                    'question' => 'คำว่า morning หมายถึงช่วงเวลาใด?',
                    'explanation' => 'morning หมายถึงตอนเช้า',
                    'answers' => [
                        ['ตอนเย็น', false],
                        ['กลางคืน', false],
                        ['เที่ยงคืน', false],
                        ['ตอนเช้า', true],
                    ],
                ],
                [
                    'question' => 'We eat dinner in the evening. หมายถึงอะไร?',
                    'explanation' => 'evening หมายถึงตอนเย็น ประโยคนี้บอกว่าเรากินอาหารเย็นตอนเย็น',
                    'answers' => [
                        ['เรากินอาหารเช้าตอนเช้า', false],
                        ['เรากินอาหารเย็นตอนเย็น', true],
                        ['เรากินอาหารกลางวันตอนเที่ยง', false],
                        ['เราเข้านอนตอนกลางคืน', false],
                    ],
                ],
                [
                    'question' => 'I will see you tomorrow. นัดพบกันเมื่อไร?',
                    'explanation' => 'tomorrow หมายถึงพรุ่งนี้',
                    'answers' => [
                        ['พรุ่งนี้', true],
                        ['วันนี้', false],
                        ['เมื่อวาน', false],
                        ['ตอนนี้', false],
                    ],
                ],
                [
                    'question' => 'เพื่อนชวนไปเที่ยว แต่คุณมีงานต้องทำ ควรตอบอย่างไร?',
                    'explanation' => 'I am busy today. บอกว่าวันนี้ไม่ว่างเพราะมีสิ่งที่ต้องทำ',
                    'answers' => [
                        ['Yes, I am free all day.', false],
                        ['Yes, let us go now.', false],
                        ['Sorry, I am busy today.', true],
                        ['Yes, I have no work today.', false],
                    ],
                ],
                [
                    'question' => 'Are you free this afternoon? กำลังถามอะไร?',
                    'explanation' => 'free ในบริบทนี้หมายถึงว่าง ประโยคนี้ถามว่าบ่ายนี้ว่างไหม',
                    'answers' => [
                        ['บ่ายนี้ฝนจะตกไหม', false],
                        ['บ่ายนี้คุณว่างไหม', true],
                        ['สินค้านี้ราคาเท่าไร', false],
                        ['ตอนนี้กี่โมง', false],
                    ],
                ],
                [
                    'question' => 'คำว่า together ในประโยค We study together. หมายถึงอะไร?',
                    'explanation' => 'together หมายถึงด้วยกัน ประโยคนี้บอกว่าเราเรียนด้วยกัน',
                    'answers' => [
                        ['คนเดียว', false],
                        ['อย่างช้า ๆ', false],
                        ['เมื่อวาน', false],
                        ['ด้วยกัน', true],
                    ],
                ],
                [
                    'question' => 'I am ready to go. บอกว่าผู้พูดอยู่ในสถานะใด?',
                    'explanation' => 'ready หมายถึงพร้อม ประโยคนี้บอกว่าผู้พูดพร้อมที่จะไป',
                    'answers' => [
                        ['หลงทาง', false],
                        ['ป่วย', false],
                        ['พร้อมที่จะไป', true],
                        ['กำลังนอนหลับ', false],
                    ],
                ],
                [
                    'question' => 'Please wait for me. ขอให้ผู้ฟังทำอะไร?',
                    'explanation' => 'wait หมายถึงรอ ประโยคนี้ขอให้ผู้ฟังรอผู้พูด',
                    'answers' => [
                        ['รอผู้พูด', true],
                        ['ลืมชื่อผู้พูด', false],
                        ['เขียนที่อยู่', false],
                        ['ซื้ออาหาร', false],
                    ],
                ],
            ],
            'fill_blank' => [
                [
                    'question' => 'เติมคำให้ตรงกับความหมาย “วันนี้ฉันอยู่บ้าน”: I am at home ___.',
                    'explanation' => 'today หมายถึงวันนี้',
                    'answers' => [
                        ['today', true],
                        ['tomorrow', false],
                        ['yesterday', false],
                        ['tonight', false],
                    ],
                ],
                [
                    'question' => 'เติมคำให้ตรงกับความหมาย “กรุณาพูดอีกครั้ง”: Please say that ___.',
                    'explanation' => 'again หมายถึงอีกครั้ง',
                    'answers' => [
                        ['outside', false],
                        ['together', false],
                        ['again', true],
                        ['tomorrow', false],
                    ],
                ],
                [
                    'question' => 'เติมคำให้ตรงกับความหมาย “โดยปกติฉันเดินไปโรงเรียน”: I ___ walk to school.',
                    'explanation' => 'usually หมายถึงโดยปกติหรือมักจะ',
                    'answers' => [
                        ['never', false],
                        ['sometimes', false],
                        ['rarely', false],
                        ['usually', true],
                    ],
                ],
                [
                    'question' => 'เติมคำให้ตรงกับความหมาย “บางครั้งฉันนั่งรถบัส”: I ___ take the bus.',
                    'explanation' => 'sometimes หมายถึงบางครั้ง',
                    'answers' => [
                        ['always', false],
                        ['sometimes', true],
                        ['never', false],
                        ['usually', false],
                    ],
                ],
                [
                    'question' => 'เติมคำให้ตรงกับความหมาย “เรามาพบกันที่สวนสาธารณะเถอะ”: Let us ___ at the park.',
                    'explanation' => 'meet หมายถึงพบหรือพบกัน',
                    'answers' => [
                        ['cook', false],
                        ['sleep', false],
                        ['meet', true],
                        ['swim', false],
                    ],
                ],
                [
                    'question' => 'เติมคำให้ตรงกับความหมาย “ฉันจำชื่อของคุณได้”: I ___ your name.',
                    'explanation' => 'remember หมายถึงจำได้',
                    'answers' => [
                        ['remember', true],
                        ['forget', false],
                        ['write', false],
                        ['spell', false],
                    ],
                ],
                [
                    'question' => 'เติมคำให้ตรงกับความหมาย “อย่าลืมกระเป๋าของคุณ”: Do not ___ your bag.',
                    'explanation' => 'forget หมายถึงลืม',
                    'answers' => [
                        ['open', false],
                        ['forget', true],
                        ['carry', false],
                        ['buy', false],
                    ],
                ],
                [
                    'question' => 'เติมคำให้ตรงกับความหมาย “ฉันอยู่บ้านเพราะฉันป่วย”: I stay home ___ I am sick.',
                    'explanation' => 'because ใช้บอกเหตุผล หมายถึงเพราะว่า',
                    'answers' => [
                        ['but', false],
                        ['or', false],
                        ['before', false],
                        ['because', true],
                    ],
                ],
            ],
            'listening' => [
                [
                    'audio_path' => 'audio/english/review-daily-conversation/mia-needs-umbrella-to-meet-friends.mp3',
                    'question' => 'What does Mia need?',
                    'explanation' => 'Mia บอกว่า I need an umbrella. เธอจึงต้องการร่ม',
                    'answers' => [
                        ['An umbrella.', true],
                        ['A train ticket.', false],
                        ['A room key.', false],
                        ['A new phone.', false],
                    ],
                ],
                [
                    'audio_path' => 'audio/english/review-daily-conversation/ben-wants-tea-not-coffee.mp3',
                    'question' => 'What does Ben want?',
                    'explanation' => 'Ben บอกว่า I want a cup of tea, not coffee. เขาจึงอยากได้ชา',
                    'answers' => [
                        ['A cup of coffee.', false],
                        ['A glass of water.', false],
                        ['A cup of tea.', true],
                        ['A glass of milk.', false],
                    ],
                ],
            ],
            'image_choice' => [
                [
                    'image_path' => 'images/english/review-daily-conversation/friends-studying-together.jpg',
                    'question' => 'ประโยคใดตรงกับภาพ?',
                    'explanation' => 'ภาพแสดงเพื่อนสองคนกำลังเรียนด้วยกัน จึงตรงกับ They are studying together.',
                    'answers' => [
                        ['They are swimming together.', false],
                        ['They are cooking together.', false],
                        ['They are playing basketball together.', false],
                        ['They are studying together.', true],
                    ],
                ],
                [
                    'image_path' => 'images/english/review-daily-conversation/friends-walking-under-umbrella-in-rain.jpg',
                    'question' => 'ประโยคใดตรงกับสภาพอากาศในภาพ?',
                    'vocabulary_mode' => 'after_vocabulary',
                    'required_vocabulary_words' => ['need'],
                    'explanation' => 'หยดฝนและร่มในภาพแสดงว่าฝนกำลังตก จึงตรงกับ It is raining.',
                    'answers' => [
                        ['It is snowing.', false],
                        ['It is raining.', true],
                        ['It is sunny.', false],
                        ['The sky is clear.', false],
                    ],
                ],
            ],
        ],
    ],
];
