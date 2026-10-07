<?php

return [
    'Weather' => [
        'vocabulary' => [
            ['weather', 'ˈwɛðər', 'สภาพอากาศ', 'The weather is nice today.', 'เดอะ เวเธอร์ อิซ ไนซ์ ทูเดย์', 'วันนี้อากาศดี'],
            ['sunny', 'ˈsʌni', 'มีแดด', 'It is sunny today.', 'อิท อิซ ซันนี ทูเดย์', 'วันนี้มีแดด'],
            ['cloudy', 'ˈklaʊdi', 'มีเมฆมาก', 'The sky is cloudy.', 'เดอะ สกาย อิซ คลาวดี', 'ท้องฟ้ามีเมฆมาก'],
            ['rainy', 'ˈreɪni', 'มีฝนตก', 'It is a rainy day.', 'อิท อิซ อะ เรนนี เดย์', 'วันนี้เป็นวันที่ฝนตก'],
            ['windy', 'ˈwɪndi', 'มีลมแรง', 'It is windy outside.', 'อิท อิซ วินดี เอาต์ไซด์', 'ข้างนอกมีลมแรง'],
            ['snowy', 'ˈsnoʊi', 'มีหิมะตก', 'It is snowy in winter.', 'อิท อิซ สโนอี อิน วินเทอร์', 'ในฤดูหนาวมีหิมะตก'],
            ['hot', 'hɑt', 'ร้อน', 'It is hot this afternoon.', 'อิท อิซ ฮอต ดิส แอฟเทอร์นูน', 'บ่ายนี้อากาศร้อน'],
            ['cold', 'koʊld', 'หนาว; เย็น', 'My hands are cold.', 'มาย แฮนด์ซ อาร์ โคลด์', 'มือของฉันเย็น'],
            ['warm', 'wɔrm', 'อบอุ่น', 'The room is warm.', 'เดอะ รูม อิซ วอร์ม', 'ห้องนี้อบอุ่น'],
            ['cool', 'kul', 'เย็นสบาย', 'The air is cool this morning.', 'ดิ แอร์ อิซ คูล ดิส มอร์นิง', 'เช้านี้อากาศเย็นสบาย'],
            ['rain', 'reɪn', 'ฝน', 'The rain starts at noon.', 'เดอะ เรน สตาร์ตส์ แอท นูน', 'ฝนเริ่มตกตอนเที่ยง'],
            ['snow', 'snoʊ', 'หิมะ', 'There is snow on the ground.', 'แดร์ อิซ สโน ออน เดอะ กราวนด์', 'มีหิมะอยู่บนพื้น'],
            ['umbrella', 'ʌmˈbrɛlə', 'ร่ม', 'I take an umbrella with me.', 'ไอ เทค แอน อัมเบรลละ วิธ มี', 'ฉันนำร่มติดตัวไปด้วย'],
            ['sky', 'skaɪ', 'ท้องฟ้า', 'The sky is blue.', 'เดอะ สกาย อิซ บลู', 'ท้องฟ้าเป็นสีฟ้า'],
            ['today', 'təˈdeɪ', 'วันนี้', 'I am at home today.', 'ไอ แอม แอท โฮม ทูเดย์', 'วันนี้ฉันอยู่บ้าน'],
            ['tomorrow', 'təˈmɑroʊ', 'พรุ่งนี้', 'I will see you tomorrow.', 'ไอ วิล ซี ยู ทูมอโร', 'ฉันจะพบคุณพรุ่งนี้'],
        ],
        'exercises' => [
            'multiple_choice' => [
                [
                    'question' => 'คำว่า weather หมายถึงอะไร',
                    'explanation' => 'weather หมายถึงสภาพอากาศ เช่น อากาศร้อน มีแดด หรือฝนตก',
                    'answers' => [
                        ['สภาพอากาศ', true],
                        ['เวลา', false],
                        ['ราคา', false],
                        ['อายุ', false],
                    ],
                ],
                [
                    'question' => 'Which word is the opposite of hot?',
                    'explanation' => 'hot คือร้อน ส่วน cold คือหนาวหรือเย็น จึงมีความหมายตรงข้ามกัน',
                    'answers' => [
                        ['sunny', false],
                        ['windy', false],
                        ['cloudy', false],
                        ['cold', true],
                    ],
                ],
                [
                    'question' => 'ในประโยค The air is cool. คำว่า cool หมายถึงอะไร',
                    'explanation' => 'เมื่อพูดถึงอากาศ cool หมายถึงเย็นสบาย ไม่ใช่ความหมายว่าเท่',
                    'answers' => [
                        ['มีหิมะตก', false],
                        ['ร้อนมาก', false],
                        ['เย็นสบาย', true],
                        ['มีลมแรง', false],
                    ],
                ],
                [
                    'question' => 'You look up and see blue above you. What are you looking at?',
                    'explanation' => 'sky คือท้องฟ้าที่อยู่ด้านบน ส่วน floor คือพื้น desk คือโต๊ะ และ door คือประตู',
                    'answers' => [
                        ['The door.', false],
                        ['The sky.', true],
                        ['The floor.', false],
                        ['The desk.', false],
                    ],
                ],
                [
                    'question' => 'Water falls from clouds. What is it called?',
                    'explanation' => 'rain คือฝน ซึ่งเป็นหยดน้ำที่ตกลงมาจากเมฆ',
                    'answers' => [
                        ['sunlight', false],
                        ['rain', true],
                        ['snow', false],
                        ['wind', false],
                    ],
                ],
                [
                    'question' => 'คุณพูดถึงวันที่ถัดจากวันนี้ ควรใช้คำใด',
                    'explanation' => 'tomorrow หมายถึงพรุ่งนี้ ซึ่งเป็นวันที่ถัดจากวันนี้',
                    'answers' => [
                        ['tomorrow', true],
                        ['today', false],
                        ['yesterday', false],
                        ['tonight', false],
                    ],
                ],
                [
                    'question' => 'It is raining. What can you use to keep rain off your head?',
                    'explanation' => 'umbrella คือร่ม ใช้บังฝนเพื่อให้ตัวเราเปียกน้อยลง',
                    'answers' => [
                        ['A notebook.', false],
                        ['A pencil.', false],
                        ['A chair.', false],
                        ['An umbrella.', true],
                    ],
                ],
                [
                    'question' => 'Which sentence describes pleasantly warm weather?',
                    'explanation' => 'warm หมายถึงอบอุ่น ประโยค It is warm today. จึงบอกว่าวันนี้อากาศอบอุ่น',
                    'answers' => [
                        ['There is heavy snow today.', false],
                        ['The air is very cold today.', false],
                        ['It is warm today.', true],
                        ['It is freezing today.', false],
                    ],
                ],
            ],
            'fill_blank' => [
                [
                    'question' => 'Complete: The sun is shining. It is _____.',
                    'explanation' => 'เมื่อมีดวงอาทิตย์ส่องแสง ใช้ sunny เพื่อบอกว่ามีแดด',
                    'answers' => [
                        ['snowy', false],
                        ['cloudy', false],
                        ['sunny', true],
                        ['rainy', false],
                    ],
                ],
                [
                    'question' => 'Complete: There are many clouds in the sky. It is _____.',
                    'explanation' => 'cloudy หมายถึงมีเมฆมาก จึงตรงกับท้องฟ้าที่มีเมฆหลายก้อน',
                    'answers' => [
                        ['hot', false],
                        ['cloudy', true],
                        ['sunny', false],
                        ['snowy', false],
                    ],
                ],
                [
                    'question' => 'Complete: Rain is falling all day. It is a _____ day.',
                    'explanation' => 'rainy ใช้ขยาย day เพื่อบอกว่าเป็นวันที่มีฝนตก',
                    'answers' => [
                        ['rainy', true],
                        ['dry', false],
                        ['snowy', false],
                        ['sunny', false],
                    ],
                ],
                [
                    'question' => 'Complete: The wind is strong. It is _____ outside.',
                    'explanation' => 'windy หมายถึงมีลมแรง จึงตรงกับข้อความที่บอกว่าลมพัดแรง',
                    'answers' => [
                        ['rainy', false],
                        ['sunny', false],
                        ['snowy', false],
                        ['windy', true],
                    ],
                ],
                [
                    'question' => 'Complete: Snow is falling. It is _____ today.',
                    'explanation' => 'snowy หมายถึงมีหิมะตก ส่วน rainy หมายถึงมีฝนตก',
                    'answers' => [
                        ['sunny', false],
                        ['rainy', false],
                        ['windy', false],
                        ['snowy', true],
                    ],
                ],
                [
                    'question' => 'Complete: This coat keeps me _____.',
                    'explanation' => 'เสื้อโค้ทช่วยให้ร่างกายอบอุ่น จึงใช้ warm',
                    'answers' => [
                        ['hungry', false],
                        ['late', false],
                        ['warm', true],
                        ['wet', false],
                    ],
                ],
                [
                    'question' => 'Complete: The ground is white because there is _____ on it.',
                    'explanation' => 'snow คือหิมะ ซึ่งทำให้พื้นมีสีขาวได้',
                    'answers' => [
                        ['wind', false],
                        ['snow', true],
                        ['rain', false],
                        ['sunlight', false],
                    ],
                ],
                [
                    'question' => 'เติมคำที่หมายถึงวันนี้: I need the umbrella _____, not tomorrow.',
                    'explanation' => 'today หมายถึงวันนี้ และต่างจาก tomorrow ซึ่งหมายถึงพรุ่งนี้',
                    'answers' => [
                        ['today', true],
                        ['yesterday', false],
                        ['tonight', false],
                        ['tomorrow', false],
                    ],
                ],
            ],
            'listening' => [
                [
                    'question' => 'ฟังเสียงแล้วเลือกสภาพอากาศวันนี้',
                    'explanation' => 'ผู้พูดบอกว่า It is hot and sunny today. จึงหมายถึงวันนี้อากาศร้อนและมีแดด',
                    'answers' => [
                        ['Hot and sunny.', true],
                        ['Cold and snowy.', false],
                        ['Cool and rainy.', false],
                        ['Warm and cloudy.', false],
                    ],
                    'audio_path' => 'audio/english/weather/hot-and-sunny-today.mp3',
                ],
                [
                    'question' => 'ตามเสียง ผู้พูดขอให้เตรียมสิ่งใดสำหรับพรุ่งนี้',
                    'explanation' => 'ผู้พูดคาดว่าพรุ่งนี้จะมีฝน และขอให้ bring an umbrella คือเอาร่มมาด้วย',
                    'answers' => [
                        ['A pencil.', false],
                        ['A chair.', false],
                        ['A notebook.', false],
                        ['An umbrella.', true],
                    ],
                    'audio_path' => 'audio/english/weather/rain-tomorrow-bring-umbrella.mp3',
                ],
            ],
            'image_choice' => [
                [
                    'question' => 'ภาพแสดงสภาพอากาศแบบใด',
                    'explanation' => 'ในภาพมีฝนตกและคนถือร่ม จึงใช้ rainy เพื่อบอกว่ามีฝนตก',
                    'answers' => [
                        ['It is sunny.', false],
                        ['It is dry.', false],
                        ['It is rainy.', true],
                        ['It is snowy.', false],
                    ],
                    'image_path' => 'images/english/weather/rainy-street-with-umbrella.jpg',
                ],
                [
                    'question' => 'Which sentence matches the picture?',
                    'explanation' => 'ภาพมีเกล็ดหิมะกำลังตกและหิมะอยู่บนพื้น จึงตรงกับ It is snowy.',
                    'answers' => [
                        ['The ground is dry.', false],
                        ['It is snowy.', true],
                        ['It is rainy.', false],
                        ['It is sunny.', false],
                    ],
                    'image_path' => 'images/english/weather/falling-snow-in-park.jpg',
                ],
            ],
        ],
    ],
];
