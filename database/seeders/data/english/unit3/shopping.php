<?php

return [
    'Shopping' => [
        'vocabulary' => [
            ['shop', 'ʃɑp', 'ร้านค้า', 'This shop is open.', 'ดิส ช็อป อิซ โอเพิน', 'ร้านนี้เปิดอยู่'],
            ['buy', 'baɪ', 'ซื้อ', 'I want to buy a shirt.', 'ไอ วอนท์ ทู บาย อะ เชิร์ต', 'ฉันอยากซื้อเสื้อเชิ้ต'],
            ['sell', 'sɛl', 'ขาย', 'They sell fresh fruit.', 'เดย์ เซล เฟรช ฟรูต', 'พวกเขาขายผลไม้สด'],
            ['price', 'praɪs', 'ราคา', 'What is the price?', 'วอท อิซ เดอะ ไพรซ์', 'ราคาเท่าไร'],
            ['money', 'ˈmʌni', 'เงิน', 'I have some money.', 'ไอ แฮฟ ซัม มันนี', 'ฉันมีเงินอยู่บ้าง'],
            ['cash', 'kæʃ', 'เงินสด', 'I pay with cash.', 'ไอ เพย์ วิธ แคช', 'ฉันจ่ายด้วยเงินสด'],
            ['card', 'kɑrd', 'บัตร', 'Can I pay by card?', 'แคน ไอ เพย์ บาย คาร์ด', 'ฉันจ่ายด้วยบัตรได้ไหม'],
            ['cheap', 'tʃip', 'ราคาถูก', 'This bag is cheap.', 'ดิส แบ็ก อิซ ชีพ', 'กระเป๋าใบนี้ราคาถูก'],
            ['expensive', 'ɪkˈspɛnsɪv', 'ราคาแพง', 'These shoes are expensive.', 'ดีซ ชูซ อาร์ อิคสเพนซิฟ', 'รองเท้าคู่นี้ราคาแพง'],
            ['size', 'saɪz', 'ขนาด', 'What size do you need?', 'วอท ไซซ์ ดู ยู นีด', 'คุณต้องการขนาดไหน'],
            ['small', 'smɔl', 'เล็ก', 'I need a small bag.', 'ไอ นีด อะ สมอล แบ็ก', 'ฉันต้องการกระเป๋าใบเล็ก'],
            ['large', 'lɑrdʒ', 'ใหญ่', 'This shirt is too large.', 'ดิส เชิร์ต อิซ ทู ลาร์จ', 'เสื้อเชิ้ตตัวนี้ใหญ่เกินไป'],
            ['shirt', 'ʃɝt', 'เสื้อเชิ้ต', 'I like this blue shirt.', 'ไอ ไลค์ ดิส บลู เชิร์ต', 'ฉันชอบเสื้อเชิ้ตสีน้ำเงินตัวนี้'],
            ['shoes', 'ʃuz', 'รองเท้า', 'My shoes are black.', 'มาย ชูซ อาร์ แบล็ก', 'รองเท้าของฉันเป็นสีดำ'],
            ['bag', 'bæɡ', 'กระเป๋า', 'Please put it in a bag.', 'พลีซ พุท อิท อิน อะ แบ็ก', 'ช่วยใส่มันในกระเป๋าให้หน่อย'],
            ['receipt', 'rɪˈsit', 'ใบเสร็จ', 'Can I have a receipt?', 'แคน ไอ แฮฟ อะ ริซีท', 'ฉันขอใบเสร็จได้ไหม'],
        ],
        'exercises' => [
            'multiple_choice' => [
                [
                    'question' => 'คำใดหมายถึง “ซื้อ”',
                    'explanation' => 'buy หมายถึง ซื้อ เช่น ซื้อเสื้อหรือซื้ออาหาร',
                    'answers' => [
                        ['buy', true],
                        ['sell', false],
                        ['walk', false],
                        ['read', false],
                    ],
                ],
                [
                    'question' => 'ร้านค้าทำอะไรในประโยค “They sell fresh fruit.”',
                    'explanation' => 'sell หมายถึง ขาย ประโยคนี้บอกว่าพวกเขาขายผลไม้สด',
                    'answers' => [
                        ['ซื้อผลไม้สด', false],
                        ['กินผลไม้สด', false],
                        ['ปลูกผลไม้สด', false],
                        ['ขายผลไม้สด', true],
                    ],
                ],
                [
                    'question' => 'คุณต้องการถามราคาของสินค้า ควรพูดว่าอะไร',
                    'explanation' => 'What is the price? ใช้ถามว่าสินค้ามีราคาเท่าไร',
                    'answers' => [
                        ['Where do you live?', false],
                        ['How old are you?', false],
                        ['What is the price?', true],
                        ['What is your name?', false],
                    ],
                ],
                [
                    'question' => 'สินค้าราคาไม่แพง ใช้คำใดอธิบาย',
                    'explanation' => 'cheap ใช้อธิบายสินค้าที่มีราคาถูก',
                    'answers' => [
                        ['late', false],
                        ['cheap', true],
                        ['expensive', false],
                        ['large', false],
                    ],
                ],
                [
                    'question' => 'คำว่า expensive บอกอะไรเกี่ยวกับสินค้า',
                    'explanation' => 'expensive หมายถึง ราคาแพง เป็นคำที่ใช้อธิบายราคา',
                    'answers' => [
                        ['ราคาแพง', true],
                        ['มีขนาดเล็ก', false],
                        ['เปิดอยู่', false],
                        ['มีสีแดง', false],
                    ],
                ],
                [
                    'question' => 'คุณจะจ่ายด้วยธนบัตรและเหรียญ ควรพูดว่าอะไร',
                    'explanation' => 'cash คือเงินสด จึงใช้ I pay with cash. เมื่อจ่ายด้วยธนบัตรหรือเหรียญ',
                    'answers' => [
                        ['I pay by card.', false],
                        ['I need a larger size.', false],
                        ['I want a receipt.', false],
                        ['I pay with cash.', true],
                    ],
                ],
                [
                    'question' => 'ข้อใดใช้ถามว่าจ่ายด้วยบัตรได้หรือไม่',
                    'explanation' => 'Can I pay by card? เป็นคำถามสุภาพเมื่อคุณต้องการจ่ายด้วยบัตร',
                    'answers' => [
                        ['Can I try this shirt?', false],
                        ['Can I have a receipt?', false],
                        ['Can I pay by card?', true],
                        ['Can I buy a bag?', false],
                    ],
                ],
                [
                    'question' => 'หลังจ่ายเงิน คุณต้องการหลักฐานการซื้อ ควรขออะไร',
                    'explanation' => 'receipt หมายถึง ใบเสร็จ ซึ่งเป็นหลักฐานการซื้อสินค้า',
                    'answers' => [
                        ['a chair', false],
                        ['a receipt', true],
                        ['a ticket', false],
                        ['a shirt', false],
                    ],
                ],
            ],
            'fill_blank' => [
                [
                    'question' => 'This ____ is open. (ร้านนี้เปิดอยู่)',
                    'explanation' => 'shop หมายถึง ร้านค้า จึงเติมให้ได้ว่า This shop is open.',
                    'answers' => [
                        ['shop', true],
                        ['receipt', false],
                        ['size', false],
                        ['price', false],
                    ],
                ],
                [
                    'question' => 'I have some ____. (ฉันมีเงินอยู่บ้าง)',
                    'explanation' => 'money หมายถึง เงิน ใช้กับ some ได้ในประโยค I have some money.',
                    'answers' => [
                        ['shirt', false],
                        ['shop', false],
                        ['size', false],
                        ['money', true],
                    ],
                ],
                [
                    'question' => 'What ____ do you need? (คุณต้องการขนาดไหน)',
                    'explanation' => 'size หมายถึง ขนาด คำถามนี้ใช้เมื่อสอบถามขนาดสินค้าที่ต้องการ',
                    'answers' => [
                        ['shoes', false],
                        ['receipt', false],
                        ['size', true],
                        ['cash', false],
                    ],
                ],
                [
                    'question' => 'I need a ____ bag. (ฉันต้องการกระเป๋าใบเล็ก)',
                    'explanation' => 'small หมายถึง เล็ก และวางหน้าคำนาม bag เพื่อบอกขนาด',
                    'answers' => [
                        ['open', false],
                        ['small', true],
                        ['large', false],
                        ['late', false],
                    ],
                ],
                [
                    'question' => 'This shirt is too ____. (เสื้อเชิ้ตตัวนี้ใหญ่เกินไป)',
                    'explanation' => 'large หมายถึง ใหญ่ ส่วน too large หมายถึง ใหญ่เกินไป',
                    'answers' => [
                        ['large', true],
                        ['small', false],
                        ['cheap', false],
                        ['blue', false],
                    ],
                ],
                [
                    'question' => 'I like this blue ____. (ฉันชอบเสื้อเชิ้ตสีน้ำเงินตัวนี้)',
                    'explanation' => 'shirt หมายถึง เสื้อเชิ้ต จึงตรงกับความหมายภาษาไทยที่กำหนด',
                    'answers' => [
                        ['bag', false],
                        ['card', false],
                        ['shop', false],
                        ['shirt', true],
                    ],
                ],
                [
                    'question' => 'My ____ are black. (รองเท้าของฉันเป็นสีดำ)',
                    'explanation' => 'shoes เป็นคำนามพหูพจน์ที่หมายถึงรองเท้า จึงใช้กับ are',
                    'answers' => [
                        ['bag', false],
                        ['receipt', false],
                        ['shoes', true],
                        ['shirt', false],
                    ],
                ],
                [
                    'question' => 'Please put it in a ____. (ช่วยใส่มันในกระเป๋าให้หน่อย)',
                    'explanation' => 'bag หมายถึง กระเป๋า และตรงกับสิ่งที่ใช้ใส่สินค้าในประโยคนี้',
                    'answers' => [
                        ['size', false],
                        ['bag', true],
                        ['card', false],
                        ['price', false],
                    ],
                ],
            ],
            'listening' => [
                [
                    'audio_path' => 'audio/english/shopping/shirt-is-ten-dollars.mp3',
                    'question' => 'ฟังเสียงแล้วตอบว่าเสื้อเชิ้ตราคาเท่าไร',
                    'vocabulary_mode' => 'after_vocabulary',
                    'required_vocabulary_words' => ['shirt'],
                    'explanation' => 'เสียงพูดว่า The shirt is ten dollars. จึงตอบว่าเสื้อเชิ้ตราคา 10 ดอลลาร์',
                    'answers' => [
                        ['Ten dollars.', true],
                        ['Five dollars.', false],
                        ['Twenty dollars.', false],
                        ['Thirty dollars.', false],
                    ],
                ],
                [
                    'audio_path' => 'audio/english/shopping/can-i-pay-by-card.mp3',
                    'question' => 'ฟังเสียงแล้วเลือกประโยคที่ผู้พูดใช้ถาม',
                    'explanation' => 'ผู้พูดถามว่า Can I pay by card? ซึ่งหมายถึง ฉันจ่ายด้วยบัตรได้ไหม',
                    'answers' => [
                        ['Can I pay with cash?', false],
                        ['Can I have a bag?', false],
                        ['Can I buy these shoes?', false],
                        ['Can I pay by card?', true],
                    ],
                ],
            ],
            'image_choice' => [
                [
                    'image_path' => 'images/english/shopping/customer-holding-red-shirt.jpg',
                    'question' => 'สิ่งที่ลูกค้าถืออยู่ในภาพคืออะไร',
                    'explanation' => 'ลูกค้ากำลังถือเสื้อเชิ้ตสีแดง ซึ่งเรียกว่า a shirt',
                    'answers' => [
                        ['a bag', false],
                        ['a hat', false],
                        ['a shirt', true],
                        ['a pair of shoes', false],
                    ],
                ],
                [
                    'image_path' => 'images/english/shopping/customer-paying-with-cash.jpg',
                    'question' => 'ลูกค้าในภาพกำลังจ่ายเงินด้วยอะไร',
                    'explanation' => 'ลูกค้าส่งธนบัตรให้พนักงาน จึงเป็นการจ่ายด้วยเงินสดหรือ cash',
                    'answers' => [
                        ['a ticket', false],
                        ['cash', true],
                        ['a card', false],
                        ['a receipt', false],
                    ],
                ],
            ],
        ],
    ],
];
