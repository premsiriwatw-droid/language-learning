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

        /*
        |--------------------------------------------------------------------------
        | Unit 4: Social & Activities
        |--------------------------------------------------------------------------
        */

        $unit4 = $course->units()->firstOrCreate([
            'title' => 'Unit 4: Social & Activities',
        ]);

        $unit4Lessons = [
            'Weather',
            'Daily Routine',
            'School & Study',
            'Friends & Social Life',
        ];

        foreach ($unit4Lessons as $title) {
            $unit4->lessons()->firstOrCreate([
                'title' => $title,
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | Unit 5: Practical Chinese
        |--------------------------------------------------------------------------
        */

        $unit5 = $course->units()->firstOrCreate([
            'title' => 'Unit 5: Practical Chinese',
        ]);

        $unit5Lessons = [
            'Health & Body',
            'Travel & Hotel',
            'Asking for Help',
            'Review & Daily Conversation',
        ];

        foreach ($unit5Lessons as $title) {
            $unit5->lessons()->firstOrCreate([
                'title' => $title,
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | English Learning Structure
        |--------------------------------------------------------------------------
        */

        $englishLanguage = Language::firstOrCreate([
            'name' => 'English',
        ]);

        $englishCourse = $englishLanguage->courses()->firstOrCreate([
            'title' => 'English Beginner',
        ]);

        $englishUnits = [
            'Unit 1: Basics' => [
                'Greetings',
                'Self Introduction',
                'Numbers',
            ],
            'Unit 2: Daily Life' => [
                'Family',
                'Age',
                'Time',
                'Food & Drinks',
            ],
            'Unit 3: Everyday Conversation' => [
                'Shopping',
                'Transportation',
                'Places & Directions',
                'Hobbies',
            ],
            'Unit 4: Social & Activities' => [
                'Weather',
                'Daily Routine',
                'School & Study',
                'Friends & Social Life',
            ],
            'Unit 5: Practical English' => [
                'Health & Body',
                'Travel & Hotel',
                'Asking for Help',
                'Review & Daily Conversation',
            ],
        ];

        foreach ($englishUnits as $unitTitle => $lessonTitles) {
            $englishUnit = $englishCourse->units()->firstOrCreate([
                'title' => $unitTitle,
            ]);

            foreach ($lessonTitles as $lessonTitle) {
                $englishUnit->lessons()->firstOrCreate([
                    'title' => $lessonTitle,
                ]);
            }
        }
    }
}