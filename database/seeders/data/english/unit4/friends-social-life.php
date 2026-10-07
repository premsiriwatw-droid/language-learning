<?php

return [
    'Friends & Social Life' => [
        'vocabulary' => [
            ['friend', 'frɛnd', 'เพื่อน', 'She is my friend.', 'ชี อิซ มาย เฟรนด์', 'เธอเป็นเพื่อนของฉัน'],
            ['classmate', 'ˈklæsˌmeɪt', 'เพื่อนร่วมชั้น', 'My classmate sits next to me.', 'มาย คลาสเมท ซิทส์ เน็กซ์ท ทู มี', 'เพื่อนร่วมชั้นของฉันนั่งข้างฉัน'],
            ['neighbor', 'ˈneɪbər', 'เพื่อนบ้าน', 'Our neighbor is kind.', 'เอาเออร์ เนเบอร์ อิซ ไคนด์', 'เพื่อนบ้านของเราใจดี'],
            ['meet', 'mit', 'พบ; เจอ', 'I meet my friends at the park.', 'ไอ มีท มาย เฟรนด์ซ แอท เดอะ พาร์ก', 'ฉันพบเพื่อน ๆ ที่สวนสาธารณะ'],
            ['talk', 'tɔk', 'พูด; คุย', 'I talk to my friend after class.', 'ไอ ทอล์ก ทู มาย เฟรนด์ แอฟเทอร์ คลาส', 'ฉันคุยกับเพื่อนหลังเลิกเรียน'],
            ['chat', 'tʃæt', 'คุยเล่น', 'We chat about our hobbies.', 'วี แชท อะเบาต์ เอาเออร์ ฮอบบีซ', 'เราคุยเล่นเกี่ยวกับงานอดิเรกของเรา'],
            ['visit', 'ˈvɪzɪt', 'ไปเยี่ยม', 'I visit my friend on Sunday.', 'ไอ วิซิท มาย เฟรนด์ ออน ซันเดย์', 'ฉันไปเยี่ยมเพื่อนในวันอาทิตย์'],
            ['call', 'kɔl', 'โทรหา', 'Please call me tonight.', 'พลีซ คอล มี ทูไนต์', 'กรุณาโทรหาฉันคืนนี้'],
            ['message', 'ˈmɛsɪdʒ', 'ข้อความ', 'I send my friend a message.', 'ไอ เซนด์ มาย เฟรนด์ อะ เมสซิจ', 'ฉันส่งข้อความหาเพื่อน'],
            ['invite', 'ɪnˈvaɪt', 'เชิญ', 'I invite my friends to dinner.', 'ไอ อินไวต์ มาย เฟรนด์ซ ทู ดินเนอร์', 'ฉันเชิญเพื่อน ๆ มากินอาหารเย็น'],
            ['party', 'ˈpɑrti', 'งานเลี้ยง', 'We have a party on Friday.', 'วี แฮฟ อะ พาร์ที ออน ฟรายเดย์', 'เรามีงานเลี้ยงในวันศุกร์'],
            ['birthday', 'ˈbɜrθˌdeɪ', 'วันเกิด', 'Today is my birthday.', 'ทูเดย์ อิซ มาย เบิร์ธเดย์', 'วันนี้เป็นวันเกิดของฉัน'],
            ['together', 'təˈɡɛðər', 'ด้วยกัน', 'We walk home together.', 'วี วอล์ก โฮม ทูเกเธอร์', 'เราเดินกลับบ้านด้วยกัน'],
            ['help', 'hɛlp', 'ช่วย; ช่วยเหลือ', 'Can you help me?', 'แคน ยู เฮลป์ มี', 'คุณช่วยฉันได้ไหม'],
            ['kind', 'kaɪnd', 'ใจดี', 'My friend is very kind.', 'มาย เฟรนด์ อิซ เวรี ไคนด์', 'เพื่อนของฉันใจดีมาก'],
            ['free', 'fri', 'ว่าง', 'Are you free this afternoon?', 'อาร์ ยู ฟรี ดิส แอฟเทอร์นูน', 'บ่ายนี้คุณว่างไหม'],
        ],
        'exercises' => [
            'multiple_choice' => [
                [
                    'question' => 'คำว่า friend หมายถึงอะไร',
                    'explanation' => 'friend หมายถึงเพื่อน ซึ่งเป็นคนที่เราสนิทหรือชอบใช้เวลาร่วมกัน',
                    'answers' => [
                        ['ครู', false],
                        ['ลูกค้า', false],
                        ['คนขับรถ', false],
                        ['เพื่อน', true],
                    ],
                ],
                [
                    'question' => 'Mia studies in the same class as you. She is your _____.',
                    'explanation' => 'classmate คือเพื่อนร่วมชั้น ใช้กับคนที่เรียนอยู่ในชั้นเดียวกัน',
                    'answers' => [
                        ['doctor', false],
                        ['driver', false],
                        ['classmate', true],
                        ['teacher', false],
                    ],
                ],
                [
                    'question' => 'What do you call a person who lives next to your home?',
                    'explanation' => 'neighbor คือเพื่อนบ้าน ซึ่งอยู่ใกล้บ้านของเรา',
                    'answers' => [
                        ['Your doctor.', false],
                        ['Your neighbor.', true],
                        ['Your customer.', false],
                        ['Your teacher.', false],
                    ],
                ],
                [
                    'question' => 'คุณต้องการนัดเจอเพื่อนที่สวนสาธารณะ ควรพูดประโยคใด',
                    'explanation' => 'meet หมายถึงพบหรือเจอกัน ประโยคนี้ใช้ชวนไปพบกันที่สวนสาธารณะ',
                    'answers' => [
                        ['Let\'s meet at the park.', true],
                        ['Let\'s go to the library.', false],
                        ['Let\'s clean the desk.', false],
                        ['Let\'s buy a shirt.', false],
                    ],
                ],
                [
                    'question' => 'ในประโยค I talk to my friend. คำว่า talk หมายถึงอะไร',
                    'explanation' => 'talk หมายถึงพูดหรือคุย และใช้ talk to ตามด้วยคนที่เราคุยด้วย',
                    'answers' => [
                        ['คุย', true],
                        ['นอน', false],
                        ['เขียน', false],
                        ['ซื้อ', false],
                    ],
                ],
                [
                    'question' => 'You go to your friend\'s home to spend time with them. What do you do?',
                    'explanation' => 'visit your friend คือไปเยี่ยมเพื่อนที่บ้านหรือสถานที่ที่เพื่อนอยู่',
                    'answers' => [
                        ['Call your friend.', false],
                        ['Write a message.', false],
                        ['Go to sleep.', false],
                        ['Visit your friend.', true],
                    ],
                ],
                [
                    'question' => 'Your friend is kind. Which action fits this description?',
                    'explanation' => 'kind หมายถึงใจดี การช่วยถือกระเป๋าหนักแสดงถึงความใจดี',
                    'answers' => [
                        ['Taking your things without asking.', false],
                        ['Laughing at your mistakes.', false],
                        ['Helping you carry a heavy bag.', true],
                        ['Ignoring you when you need help.', false],
                    ],
                ],
                [
                    'question' => 'เพื่อนถาม Are you free this afternoon? เขาต้องการรู้เรื่องใด',
                    'explanation' => 'free ในคำถามนี้หมายถึงมีเวลาว่าง ไม่ได้หมายถึงไม่มีค่าใช้จ่าย',
                    'answers' => [
                        ['วันนี้คุณอายุเท่าไร', false],
                        ['บ่ายนี้คุณว่างไหม', true],
                        ['บ่ายนี้คุณหิวไหม', false],
                        ['บ้านของคุณอยู่ที่ไหน', false],
                    ],
                ],
            ],
            'fill_blank' => [
                [
                    'question' => 'เติมคำที่หมายถึงคุยเล่น: We _____ about movies after class.',
                    'explanation' => 'chat หมายถึงคุยเล่นหรือพูดคุยอย่างสบาย ๆ เช่น คุยเรื่องภาพยนตร์หลังเลิกเรียน',
                    'answers' => [
                        ['wash', false],
                        ['chat', true],
                        ['sleep', false],
                        ['cook', false],
                    ],
                ],
                [
                    'question' => 'Complete: I want to hear your voice. Can I _____ you?',
                    'explanation' => 'call you หมายถึงโทรหาคุณ จึงใช้เมื่อต้องการได้ยินเสียงอีกฝ่าย',
                    'answers' => [
                        ['call', true],
                        ['write', false],
                        ['clean', false],
                        ['eat', false],
                    ],
                ],
                [
                    'question' => 'Complete: I send a short _____ to my friend on my phone.',
                    'explanation' => 'message คือข้อความที่ส่งให้เพื่อนผ่านโทรศัพท์ได้',
                    'answers' => [
                        ['chair', false],
                        ['breakfast', false],
                        ['window', false],
                        ['message', true],
                    ],
                ],
                [
                    'question' => 'Complete: I want you to come to my party, so I _____ you.',
                    'explanation' => 'invite หมายถึงเชิญ เช่น เชิญเพื่อนมางานเลี้ยง',
                    'answers' => [
                        ['sleep', false],
                        ['cook', false],
                        ['invite', true],
                        ['wash', false],
                    ],
                ],
                [
                    'question' => 'Complete: Friends come to my house for music, food, and a _____.',
                    'explanation' => 'party คืองานเลี้ยง ซึ่งอาจมีดนตรี อาหาร และเพื่อนมาร่วมงาน',
                    'answers' => [
                        ['shower', false],
                        ['question', false],
                        ['party', true],
                        ['classroom', false],
                    ],
                ],
                [
                    'question' => 'Complete: Today is my _____. I am one year older.',
                    'explanation' => 'birthday คือวันเกิด วันที่อายุเพิ่มขึ้นอีกหนึ่งปี',
                    'answers' => [
                        ['message', false],
                        ['birthday', true],
                        ['homework', false],
                        ['breakfast', false],
                    ],
                ],
                [
                    'question' => 'Complete: We go to the park _____, not alone.',
                    'explanation' => 'together หมายถึงด้วยกัน และตรงข้ามกับการไป alone หรือคนเดียว',
                    'answers' => [
                        ['together', true],
                        ['yesterday', false],
                        ['quickly', false],
                        ['late', false],
                    ],
                ],
                [
                    'question' => 'Complete: This bag is heavy. Can you _____ me carry it?',
                    'explanation' => 'help me carry it หมายถึงช่วยฉันถือมัน ใช้ขอความช่วยเหลือได้',
                    'answers' => [
                        ['invite', false],
                        ['call', false],
                        ['wash', false],
                        ['help', true],
                    ],
                ],
            ],
            'listening' => [
                [
                    'question' => 'ผู้พูดต้องการจัดงานเลี้ยงเนื่องในโอกาสใด',
                    'explanation' => 'ผู้พูดบอกว่ามีวันเกิดในวันศุกร์ และต้องการเชิญเพื่อนมางานเลี้ยงเล็ก ๆ',
                    'answers' => [
                        ['A school exam.', false],
                        ['A new job.', false],
                        ['A bus trip.', false],
                        ['A birthday.', true],
                    ],
                    'audio_path' => 'audio/english/friends-social-life/birthday-party-on-friday.mp3',
                ],
                [
                    'question' => 'ผู้พูดจะติดต่อเพื่อนอย่างไรคืนนี้',
                    'vocabulary_mode' => 'after_vocabulary',
                    'required_vocabulary_words' => ['call'],
                    'explanation' => 'ผู้พูดบอกว่า can call you tonight คือสามารถโทรหาคุณคืนนี้ได้',
                    'answers' => [
                        ['By sending a letter.', false],
                        ['By sending a text message.', false],
                        ['By phone.', true],
                        ['By visiting the friend.', false],
                    ],
                    'audio_path' => 'audio/english/friends-social-life/call-friend-tonight.mp3',
                ],
            ],
            'image_choice' => [
                [
                    'question' => 'Which sentence matches the picture?',
                    'explanation' => 'เพื่อนสองคนในภาพกำลังพูดคุยกัน จึงตรงกับ chatting together',
                    'answers' => [
                        ['The friends are cooking.', false],
                        ['The friends are chatting together.', true],
                        ['The friends are sleeping.', false],
                        ['The friends are swimming.', false],
                    ],
                    'image_path' => 'images/english/friends-social-life/friends-chatting-on-park-bench.jpg',
                ],
                [
                    'question' => 'What are the young people doing?',
                    'vocabulary_mode' => 'after_vocabulary',
                    'required_vocabulary_words' => ['help'],
                    'explanation' => 'คนหนุ่มสาวในภาพกำลังช่วยถือถุงให้คนอีกคน จึงตรงกับ Helping someone carry bags.',
                    'answers' => [
                        ['Helping someone carry bags.', true],
                        ['Talking on the phone.', false],
                        ['Sleeping in bed.', false],
                        ['Writing in a notebook.', false],
                    ],
                    'image_path' => 'images/english/friends-social-life/young-adults-helping-carry-bags.jpg',
                ],
            ],
        ],
    ],
];
