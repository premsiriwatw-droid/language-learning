<?php

namespace Database\Seeders;

use App\Models\Course;
use App\Models\Language;
use Illuminate\Database\Seeder;

class LearningStructureSeeder extends Seeder
{
    public function run(): void
    {
        /*
        |--------------------------------------------------------------------------
        | Language
        |--------------------------------------------------------------------------
        */

        $language = Language::firstOrCreate([
            'name' => 'Chinese',
        ]);

        /*
        |--------------------------------------------------------------------------
        | Course
        |--------------------------------------------------------------------------
        */

        $course = $language->courses()->firstOrCreate([
            'title' => 'Chinese Beginner',
        ]);

        /*
        |--------------------------------------------------------------------------
        | Unit 1: Basics
        |--------------------------------------------------------------------------
        */

        $unit1 = $course->units()->firstOrCreate([
            'title' => 'Unit 1: Basics',
        ]);

        $unit1Lessons = [
            'Greetings',
            'Self Introduction',
            'Numbers',
        ];

        foreach ($unit1Lessons as $title) {
            $unit1->lessons()->firstOrCreate([
                'title' => $title,
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | Unit 2: Daily Life
        |--------------------------------------------------------------------------
        */

        $unit2 = $course->units()->firstOrCreate([
            'title' => 'Unit 2: Daily Life',
        ]);

        $unit2Lessons = [
            'Family',
            'Age',
            'Time',
            'Food & Drinks',
        ];

        foreach ($unit2Lessons as $title) {
            $unit2->lessons()->firstOrCreate([
                'title' => $title,
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | Unit 3: Everyday Conversation
        |--------------------------------------------------------------------------
        */

        $unit3 = $course->units()->firstOrCreate([
            'title' => 'Unit 3: Everyday Conversation',
        ]);

        $unit3Lessons = [
            'Shopping',
            'Transportation',
            'Places & Directions',
            'Hobbies',
        ];

        foreach ($unit3Lessons as $title) {
            $unit3->lessons()->firstOrCreate([
                'title' => $title,
            ]);
        }
    }
}