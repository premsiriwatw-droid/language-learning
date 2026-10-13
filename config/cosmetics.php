<?php

return [
    'types' => [
        'frame' => 'กรอบรูป', 'background' => 'พื้นหลัง', 'name_color' => 'สีชื่อ',
        'streak_style' => 'รูปแบบ Streak', 'sticker' => 'สติกเกอร์',
    ],
    // Only these bundled classes may be rendered. Database values are never CSS/HTML.
    'styles' => [
        'frame' => ['gold' => 'cosmetic-frame-gold', 'ocean' => 'cosmetic-frame-ocean'],
        'background' => ['sunrise' => 'cosmetic-background-sunrise', 'forest' => 'cosmetic-background-forest'],
        'name_color' => ['ruby' => 'cosmetic-name-ruby', 'indigo' => 'cosmetic-name-indigo'],
        'streak_style' => ['flame' => 'cosmetic-streak-flame', 'sparkle' => 'cosmetic-streak-sparkle'],
        'sticker' => ['star' => '⭐', 'panda' => '🐼'],
    ],
];
