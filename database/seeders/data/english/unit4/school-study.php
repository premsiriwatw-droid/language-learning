<?php

return [
    'School & Study' => [
        'vocabulary' => [
            ['school', 'skul', 'โรงเรียน', 'I go to school every day.', 'ไอ โก ทู สคูล เอฟรี เดย์', 'ฉันไปโรงเรียนทุกวัน'],
            ['classroom', 'ˈklæsrum', 'ห้องเรียน', 'Our classroom is small.', 'เอาเออร์ คลาสรูม อิซ สมอล', 'ห้องเรียนของเราเล็ก'],
            ['teacher', 'ˈtitʃər', 'ครู', 'Our teacher is kind.', 'เอาเออร์ ทีเชอร์ อิซ ไคนด์', 'ครูของเราใจดี'],
            ['student', 'ˈstudənt', 'นักเรียน; นักศึกษา', 'I am a student.', 'ไอ แอม อะ สตูเดินท์', 'ฉันเป็นนักเรียน'],
            ['book', 'bʊk', 'หนังสือ', 'Please open your book.', 'พลีซ โอเพิน ยัวร์ บุค', 'กรุณาเปิดหนังสือของคุณ'],
            ['notebook', 'ˈnoʊtˌbʊk', 'สมุดบันทึก', 'I write in my notebook.', 'ไอ ไรต์ อิน มาย โนตบุค', 'ฉันเขียนลงในสมุดบันทึก'],
            ['pen', 'pɛn', 'ปากกา', 'This pen is blue.', 'ดิส เพน อิซ บลู', 'ปากกาด้ามนี้เป็นสีน้ำเงิน'],
            ['pencil', 'ˈpɛnsəl', 'ดินสอ', 'I draw with a pencil.', 'ไอ ดรอ วิธ อะ เพนเซิล', 'ฉันวาดรูปด้วยดินสอ'],
            ['desk', 'dɛsk', 'โต๊ะเรียน; โต๊ะทำงาน', 'My book is on the desk.', 'มาย บุค อิซ ออน เดอะ เดสก์', 'หนังสือของฉันอยู่บนโต๊ะเรียน'],
            ['chair', 'tʃɛr', 'เก้าอี้', 'Please sit on this chair.', 'พลีซ ซิท ออน ดิส แชร์', 'กรุณานั่งบนเก้าอี้ตัวนี้'],
            ['read', 'rid', 'อ่าน', 'I read a book after school.', 'ไอ รีด อะ บุค แอฟเทอร์ สคูล', 'ฉันอ่านหนังสือหลังเลิกเรียน'],
            ['write', 'raɪt', 'เขียน', 'Please write your name.', 'พลีซ ไรต์ ยัวร์ เนม', 'กรุณาเขียนชื่อของคุณ'],
            ['listen', 'ˈlɪsən', 'ฟัง', 'Please listen to the teacher.', 'พลีซ ลิสเซิน ทู เดอะ ทีเชอร์', 'กรุณาฟังครู'],
            ['study', 'ˈstʌdi', 'เรียน; ศึกษา', 'I study English at school.', 'ไอ สตัดดี อิงลิช แอท สคูล', 'ฉันเรียนภาษาอังกฤษที่โรงเรียน'],
            ['homework', 'ˈhoʊmˌwɜrk', 'การบ้าน', 'I do my homework at home.', 'ไอ ดู มาย โฮมเวิร์ก แอท โฮม', 'ฉันทำการบ้านที่บ้าน'],
            ['question', 'ˈkwɛstʃən', 'คำถาม', 'I have a question.', 'ไอ แฮฟ อะ เควสเชิน', 'ฉันมีคำถาม'],
        ],
        'exercises' => [
            'multiple_choice' => [
                [
                    'question' => 'คำว่า school หมายถึงสถานที่ใด',
                    'explanation' => 'school คือโรงเรียน ซึ่งเป็นสถานที่ที่นักเรียนไปเรียน',
                    'answers' => [
                        ['โรงพยาบาล', false],
                        ['สถานีรถไฟ', false],
                        ['โรงเรียน', true],
                        ['โรงแรม', false],
                    ],
                ],
                [
                    'question' => 'What does a teacher usually do at school?',
                    'explanation' => 'teacher คือครู งานหลักที่โรงเรียนคือสอนนักเรียน',
                    'answers' => [
                        ['Drive a taxi.', false],
                        ['Teach students.', true],
                        ['Sell train tickets.', false],
                        ['Cook in a restaurant.', false],
                    ],
                ],
                [
                    'question' => 'Ben is learning English in a class. What is his role in that class?',
                    'explanation' => 'student คือนักเรียนหรือนักศึกษา Ben มีบทบาทเป็นผู้เรียนในชั้นเรียนจึงเป็น student',
                    'answers' => [
                        ['student', true],
                        ['driver', false],
                        ['doctor', false],
                        ['cook', false],
                    ],
                ],
                [
                    'question' => 'คุณต้องการสมุดสำหรับจดสิ่งที่ครูสอน ควรขอสิ่งใด',
                    'explanation' => 'notebook คือสมุดบันทึก ใช้จดเนื้อหาที่เรียน',
                    'answers' => [
                        ['A chair.', false],
                        ['A window.', false],
                        ['A school.', false],
                        ['A notebook.', true],
                    ],
                ],
                [
                    'question' => 'Which object has a flat surface for your book while you study?',
                    'explanation' => 'desk คือโต๊ะเรียนหรือโต๊ะทำงาน มีพื้นราบสำหรับวางหนังสือ',
                    'answers' => [
                        ['A pencil.', false],
                        ['A bag.', false],
                        ['A door.', false],
                        ['A desk.', true],
                    ],
                ],
                [
                    'question' => 'ครูพูดว่า Please read this sentence. คุณควรทำอะไร',
                    'explanation' => 'read หมายถึงอ่าน ครูจึงขอให้อ่านประโยคที่กำหนด',
                    'answers' => [
                        ['ปิดหนังสือ', false],
                        ['เขียนชื่อ', false],
                        ['อ่านประโยคนี้', true],
                        ['ลบประโยคนี้', false],
                    ],
                ],
                [
                    'question' => 'The teacher is speaking. Which instruction asks you to pay attention to the sound?',
                    'explanation' => 'listen to the teacher หมายถึงฟังครู โดยใช้ listen to ตามด้วยคนหรือสิ่งที่ฟัง',
                    'answers' => [
                        ['Clean the desk.', false],
                        ['Listen to the teacher.', true],
                        ['Close the door.', false],
                        ['Draw a picture.', false],
                    ],
                ],
                [
                    'question' => 'คุณไม่เข้าใจบทเรียนและต้องการถามครู ควรพูดอย่างไร',
                    'explanation' => 'I have a question. หมายถึงฉันมีคำถาม ใช้เริ่มถามครูได้อย่างเป็นธรรมชาติ',
                    'answers' => [
                        ['I have a question.', true],
                        ['I have a chair.', false],
                        ['I have a pencil.', false],
                        ['I have a window.', false],
                    ],
                ],
            ],
            'fill_blank' => [
                [
                    'question' => 'Complete: Our teacher and students are in the _____ during the lesson.',
                    'explanation' => 'classroom คือห้องเรียน จึงเป็นห้องที่ครูและนักเรียนใช้ระหว่างเรียน',
                    'answers' => [
                        ['classroom', true],
                        ['kitchen', false],
                        ['bathroom', false],
                        ['bedroom', false],
                    ],
                ],
                [
                    'question' => 'Complete: Please open your _____ and read page ten.',
                    'explanation' => 'book คือหนังสือ ซึ่งเปิดเพื่ออ่านหน้าที่กำหนดได้',
                    'answers' => [
                        ['chair', false],
                        ['desk', false],
                        ['door', false],
                        ['book', true],
                    ],
                ],
                [
                    'question' => 'Complete: I use a _____ to write in ink.',
                    'explanation' => 'pen คือปากกา ใช้เขียนด้วยหมึก ส่วน pencil คือดินสอ',
                    'answers' => [
                        ['ruler', false],
                        ['eraser', false],
                        ['pen', true],
                        ['pencil', false],
                    ],
                ],
                [
                    'question' => 'Complete: I write with a _____, so I can erase my mistakes.',
                    'explanation' => 'pencil คือดินสอ รอยที่เขียนด้วยดินสอสามารถลบด้วยยางลบได้',
                    'answers' => [
                        ['chair', false],
                        ['pencil', true],
                        ['pen', false],
                        ['book', false],
                    ],
                ],
                [
                    'question' => 'Complete: Please sit on this _____.',
                    'explanation' => 'chair คือเก้าอี้ ใช้ sit on a chair เพื่อบอกว่านั่งบนเก้าอี้',
                    'answers' => [
                        ['pen', false],
                        ['chair', true],
                        ['pencil', false],
                        ['book', false],
                    ],
                ],
                [
                    'question' => 'Complete: Please _____ your name on the paper.',
                    'explanation' => 'write your name หมายถึงเขียนชื่อของคุณลงบนกระดาษ',
                    'answers' => [
                        ['write', true],
                        ['listen', false],
                        ['sit', false],
                        ['open', false],
                    ],
                ],
                [
                    'question' => 'Complete: I _____ English to learn new words.',
                    'explanation' => 'study English หมายถึงเรียนหรือศึกษาภาษาอังกฤษ',
                    'answers' => [
                        ['sleep', false],
                        ['cook', false],
                        ['wash', false],
                        ['study', true],
                    ],
                ],
                [
                    'question' => 'Complete: The teacher gives us _____ to do at home.',
                    'explanation' => 'homework คือการบ้าน เป็นงานที่ครูให้นักเรียนทำต่อที่บ้าน',
                    'answers' => [
                        ['a chair', false],
                        ['a shower', false],
                        ['homework', true],
                        ['breakfast', false],
                    ],
                ],
            ],
            'listening' => [
                [
                    'question' => 'ตามเสียง นักเรียนต้องทำอะไรกับหนังสือก่อนอ่าน',
                    'explanation' => 'ผู้พูดขอให้ open your book คือเปิดหนังสือ แล้วอ่านหน้าห้า',
                    'answers' => [
                        ['Put the book away.', false],
                        ['Give the book to a friend.', false],
                        ['Open the book.', true],
                        ['Close the book.', false],
                    ],
                    'audio_path' => 'audio/english/school-study/open-book-read-page-five.mp3',
                ],
                [
                    'question' => 'ผู้พูดทำการบ้านที่ไหน',
                    'explanation' => 'ผู้พูดบอกว่า I do my homework at home. จึงทำการบ้านที่บ้าน',
                    'answers' => [
                        ['In a restaurant.', false],
                        ['At home.', true],
                        ['On a bus.', false],
                        ['At a hotel.', false],
                    ],
                    'audio_path' => 'audio/english/school-study/homework-at-home.mp3',
                ],
            ],
            'image_choice' => [
                [
                    'question' => 'Where are the students in the picture?',
                    'explanation' => 'ภาพมีครู นักเรียน และโต๊ะเรียน จึงเป็น classroom หรือห้องเรียน',
                    'answers' => [
                        ['In a classroom.', true],
                        ['In a kitchen.', false],
                        ['In a bedroom.', false],
                        ['In a hotel room.', false],
                    ],
                    'image_path' => 'images/english/school-study/students-and-teacher-in-classroom.jpg',
                ],
                [
                    'question' => 'What is the student doing?',
                    'explanation' => 'นักเรียนกำลังใช้ปากกาเขียนลงในสมุด จึงตรงกับ Writing in a notebook.',
                    'answers' => [
                        ['Eating lunch.', false],
                        ['Sleeping in bed.', false],
                        ['Taking a shower.', false],
                        ['Writing in a notebook.', true],
                    ],
                    'image_path' => 'images/english/school-study/student-writing-in-notebook.jpg',
                ],
            ],
        ],
    ],
];
