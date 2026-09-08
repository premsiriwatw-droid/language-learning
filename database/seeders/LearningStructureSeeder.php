<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Language;
use App\Models\Course;
use App\Models\Unit;
use App\Models\Lesson;

class LearningStructureSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Language (มี $fillable = ['name'])
        $language = Language::firstOrCreate([
            'name' => 'Chinese',
        ]);

        // 2. Course (สร้างผ่าน relationship courses())
        $course = $language->courses()->firstOrCreate([
            'title' => 'Chinese Beginner',
        ]);

        // 3. Unit (สร้างผ่าน relationship units())
        $unit = $course->units()->firstOrCreate([
            'title' => 'Unit 1: Basics',
        ]);

        // 4. Lessons (สร้างผ่าน relationship lessons() พร้อมชื่อตรงกับที่ Content Seeder ใช้ Lookup)
        $lessons = [
            'Greetings',
            'Self Introduction',
            'Numbers',
        ];

        foreach ($lessons as $title) {
            $unit->lessons()->firstOrCreate([
                'title' => $title,
            ]);
        }
    }
}