<?php

return [
    'Hobbies' => [
        'vocabulary' => [
            ['read', 'rid', 'อ่าน', 'I read a book every day.', 'ไอ รีด อะ บุค เอฟรี เดย์', 'ฉันอ่านหนังสือทุกวัน'],
            ['book', 'bʊk', 'หนังสือ', 'This book is interesting.', 'ดิส บุค อิซ อินเทอเรสทิง', 'หนังสือเล่มนี้น่าสนใจ'],
            ['music', 'ˈmjuzɪk', 'ดนตรี', 'I listen to music.', 'ไอ ลิสเซิน ทู มิวซิค', 'ฉันฟังดนตรี'],
            ['sing', 'sɪŋ', 'ร้องเพลง', 'We sing together.', 'วี ซิง ทูเก็ธเธอร์', 'เราร้องเพลงด้วยกัน'],
            ['dance', 'dæns', 'เต้นรำ', 'I like to dance.', 'ไอ ไลค์ ทู แดนซ์', 'ฉันชอบเต้นรำ'],
            ['draw', 'drɔ', 'วาดด้วยดินสอหรือปากกา', 'I draw with a pencil.', 'ไอ ดรอ วิธ อะ เพนเซิล', 'ฉันวาดด้วยดินสอ'],
            ['paint', 'peɪnt', 'วาดหรือระบายสีด้วยสี', 'I paint with a brush.', 'ไอ เพนท์ วิธ อะ บรัช', 'ฉันวาดด้วยพู่กัน'],
            ['swim', 'swɪm', 'ว่ายน้ำ', 'I can swim.', 'ไอ แคน สวิม', 'ฉันว่ายน้ำได้'],
            ['run', 'rʌn', 'วิ่ง', 'I run in the park.', 'ไอ รัน อิน เดอะ พาร์ก', 'ฉันวิ่งในสวนสาธารณะ'],
            ['soccer', 'ˈsɑkɚ', 'ฟุตบอล', 'We play soccer after school.', 'วี เพลย์ ซอคเคอร์ แอฟเทอร์ สคูล', 'เราเล่นฟุตบอลหลังเลิกเรียน'],
            ['basketball', 'ˈbæskətˌbɔl', 'บาสเกตบอล', 'I play basketball with my friends.', 'ไอ เพลย์ บาสคิทบอล วิธ มาย เฟรนด์ซ', 'ฉันเล่นบาสเกตบอลกับเพื่อน ๆ'],
            ['game', 'ɡeɪm', 'เกม', 'This game is fun.', 'ดิส เกม อิซ ฟัน', 'เกมนี้สนุก'],
            ['photo', 'ˈfoʊtoʊ', 'รูปถ่าย', 'I take a photo of my dog.', 'ไอ เทค อะ โฟโท ออฟ มาย ด็อก', 'ฉันถ่ายรูปสุนัขของฉัน'],
            ['cook', 'kʊk', 'ทำอาหาร', 'I like to cook.', 'ไอ ไลค์ ทู คุค', 'ฉันชอบทำอาหาร'],
            ['garden', 'ˈɡɑrdən', 'สวน', 'I grow flowers in my garden.', 'ไอ โกร ฟลาวเออร์ซ อิน มาย การ์เดิน', 'ฉันปลูกดอกไม้ในสวนของฉัน'],
            ['free time', 'fri taɪm', 'เวลาว่าง', 'I read in my free time.', 'ไอ รีด อิน มาย ฟรี ไทม์', 'ฉันอ่านหนังสือในเวลาว่าง'],
        ],
        'exercises' => [
            'multiple_choice' => [
                [
                    'question' => 'คำใดหมายถึง “อ่าน”',
                    'explanation' => 'read ในรูปปัจจุบันออกเสียงว่า รีด และหมายถึง อ่าน',
                    'answers' => [
                        ['read', true],
                        ['sing', false],
                        ['swim', false],
                        ['dance', false],
                    ],
                ],
                [
                    'question' => 'คุณชอบฟังดนตรี ควรพูดว่าอะไร',
                    'explanation' => 'listen to music หมายถึง ฟังดนตรี และต้องใช้ to หลัง listen ในประโยคนี้',
                    'answers' => [
                        ['I like to play soccer.', false],
                        ['I like to take photos.', false],
                        ['I like to cook.', false],
                        ['I like to listen to music.', true],
                    ],
                ],
                [
                    'question' => 'คำว่า sing หมายถึงกิจกรรมใด',
                    'explanation' => 'sing หมายถึง ร้องเพลง',
                    'answers' => [
                        ['วาดรูป', false],
                        ['ทำอาหาร', false],
                        ['ร้องเพลง', true],
                        ['ว่ายน้ำ', false],
                    ],
                ],
                [
                    'question' => 'คุณชอบเต้นรำ ควรตอบว่าอะไรเมื่อเพื่อนถามถึงงานอดิเรก',
                    'explanation' => 'I like to dance. หมายถึง ฉันชอบเต้นรำ',
                    'answers' => [
                        ['I like to cook.', false],
                        ['I like to dance.', true],
                        ['I like to read.', false],
                        ['I like to swim.', false],
                    ],
                ],
                [
                    'question' => 'คุณใช้ดินสอวาดรูป กิจกรรมนี้ใช้คำกริยาใด',
                    'explanation' => 'draw ใช้กับการวาดเส้นด้วยดินสอหรือปากกา ส่วน paint ใช้กับสีและพู่กัน',
                    'answers' => [
                        ['draw', true],
                        ['paint', false],
                        ['sing', false],
                        ['run', false],
                    ],
                ],
                [
                    'question' => '“I can swim.” หมายถึงอะไร',
                    'explanation' => 'swim หมายถึง ว่ายน้ำ ส่วน can ใช้บอกความสามารถ',
                    'answers' => [
                        ['ฉันวิ่งได้', false],
                        ['ฉันร้องเพลงได้', false],
                        ['ฉันอ่านได้', false],
                        ['ฉันว่ายน้ำได้', true],
                    ],
                ],
                [
                    'question' => 'กีฬา soccer ใช้อุปกรณ์ใดเป็นหลัก',
                    'explanation' => 'soccer คือฟุตบอล ผู้เล่นใช้เท้าเตะลูกฟุตบอลเข้าสู่ประตู',
                    'answers' => [
                        ['พู่กัน', false],
                        ['หนังสือ', false],
                        ['ลูกฟุตบอล', true],
                        ['ไม้เทนนิส', false],
                    ],
                ],
                [
                    'question' => 'คุณต้องการถามว่าเพื่อนทำอะไรในเวลาว่าง ควรพูดว่าอะไร',
                    'explanation' => 'free time หมายถึง เวลาว่าง คำถามนี้ใช้ถามกิจกรรมที่ทำในเวลาว่าง',
                    'answers' => [
                        ['How much is this shirt?', false],
                        ['What do you do in your free time?', true],
                        ['What time is it?', false],
                        ['What is your name?', false],
                    ],
                ],
            ],
            'fill_blank' => [
                [
                    'question' => 'This ____ is interesting. (หนังสือเล่มนี้น่าสนใจ)',
                    'explanation' => 'book หมายถึง หนังสือ จึงตรงกับความหมายที่กำหนด',
                    'answers' => [
                        ['book', true],
                        ['photo', false],
                        ['game', false],
                        ['garden', false],
                    ],
                ],
                [
                    'question' => 'I ____ with a brush. (ฉันวาดด้วยพู่กัน)',
                    'explanation' => 'paint ใช้กับการวาดหรือระบายสีด้วยพู่กัน จึงเหมาะกับประโยคนี้',
                    'answers' => [
                        ['read', false],
                        ['sing', false],
                        ['swim', false],
                        ['paint', true],
                    ],
                ],
                [
                    'question' => 'I ____ in the park. (ฉันวิ่งในสวนสาธารณะ)',
                    'explanation' => 'run หมายถึง วิ่ง จึงตรงกับกิจกรรมในภาษาไทย',
                    'answers' => [
                        ['dance', false],
                        ['read', false],
                        ['run', true],
                        ['sing', false],
                    ],
                ],
                [
                    'question' => 'I play ____ with my friends. (ฉันเล่นบาสเกตบอลกับเพื่อน ๆ)',
                    'explanation' => 'basketball หมายถึง บาสเกตบอล และใช้กับ play เมื่อพูดถึงการเล่นกีฬา',
                    'answers' => [
                        ['volleyball', false],
                        ['basketball', true],
                        ['soccer', false],
                        ['tennis', false],
                    ],
                ],
                [
                    'question' => 'This ____ is fun. (เกมนี้สนุก)',
                    'explanation' => 'game หมายถึง เกม จึงเติมให้ตรงกับความหมายภาษาไทย',
                    'answers' => [
                        ['game', true],
                        ['book', false],
                        ['photo', false],
                        ['garden', false],
                    ],
                ],
                [
                    'question' => 'I take a ____ of my dog. (ฉันถ่ายรูปสุนัขของฉัน)',
                    'explanation' => 'take a photo หมายถึง ถ่ายรูป จึงเติม photo',
                    'answers' => [
                        ['book', false],
                        ['game', false],
                        ['song', false],
                        ['photo', true],
                    ],
                ],
                [
                    'question' => 'I like to ____. (ฉันชอบทำอาหาร)',
                    'explanation' => 'cook หมายถึง ทำอาหาร และวางหลัง to ในรูปคำกริยาเดิม',
                    'answers' => [
                        ['swim', false],
                        ['dance', false],
                        ['cook', true],
                        ['sing', false],
                    ],
                ],
                [
                    'question' => 'I grow flowers in my ____. (ฉันปลูกดอกไม้ในสวนของฉัน)',
                    'explanation' => 'garden หมายถึง สวน เป็นสถานที่ปลูกดอกไม้ได้',
                    'answers' => [
                        ['game', false],
                        ['garden', true],
                        ['book', false],
                        ['photo', false],
                    ],
                ],
            ],
            'listening' => [
                [
                    'audio_path' => 'audio/english/hobbies/reading-books-in-free-time.mp3',
                    'question' => 'ฟังเสียงแล้วตอบว่าผู้พูดชอบทำอะไรในเวลาว่าง',
                    'vocabulary_mode' => 'after_vocabulary',
                    'required_vocabulary_words' => ['read'],
                    'explanation' => 'ผู้พูดบอกว่า I like reading books in my free time. จึงชอบอ่านหนังสือ',
                    'answers' => [
                        ['Reading books.', true],
                        ['Listening to music.', false],
                        ['Singing songs.', false],
                        ['Cooking food.', false],
                    ],
                ],
                [
                    'audio_path' => 'audio/english/hobbies/basketball-with-friends-on-sunday.mp3',
                    'question' => 'ฟังเสียงแล้วตอบว่าผู้พูดเล่นกีฬาอะไรกับเพื่อน',
                    'explanation' => 'เสียงพูดว่า I play basketball with my friends on Sunday. จึงเล่นบาสเกตบอลกับเพื่อน',
                    'answers' => [
                        ['Soccer.', false],
                        ['Tennis.', false],
                        ['Volleyball.', false],
                        ['Basketball.', true],
                    ],
                ],
            ],
            'image_choice' => [
                [
                    'image_path' => 'images/english/hobbies/person-drawing-with-pencil.jpg',
                    'question' => 'คนในภาพกำลังทำกิจกรรมใด',
                    'vocabulary_mode' => 'after_vocabulary',
                    'required_vocabulary_words' => ['draw'],
                    'explanation' => 'คนในภาพใช้ดินสอวาดรูปบนกระดาษ จึงเป็น drawing with a pencil',
                    'answers' => [
                        ['reading a book', false],
                        ['cooking food', false],
                        ['drawing with a pencil', true],
                        ['painting with a brush', false],
                    ],
                ],
                [
                    'image_path' => 'images/english/hobbies/person-swimming-in-pool.jpg',
                    'question' => 'กิจกรรมในภาพเรียกว่าอะไร',
                    'vocabulary_mode' => 'after_vocabulary',
                    'required_vocabulary_words' => ['swim'],
                    'explanation' => 'คนในภาพกำลังว่ายน้ำในสระ จึงเลือก swimming',
                    'answers' => [
                        ['singing', false],
                        ['swimming', true],
                        ['running', false],
                        ['dancing', false],
                    ],
                ],
            ],
        ],
    ],
];
