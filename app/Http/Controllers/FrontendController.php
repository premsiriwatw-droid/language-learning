<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class FrontendController extends Controller
{
    public function languages()
    {
        $languages = [
            ['code' => 'EN', 'name_en' => 'English', 'name_th' => 'อังกฤษ'],
            ['code' => '中', 'name_en' => 'Chinese', 'name_th' => 'ภาษาจีน'],
        ];

        return view('frontend.languages', compact('languages'));
    }

    public function lessons()
    {
        $progressPercent = 80;
        $userXp = 120;
        $lessons = [ ... ];
        
        $lessons = [
            ['id' => 1, 'title' => 'Lesson 1', 'is_completed' => true, 'is_locked' => false],
            ['id' => 2, 'title' => 'Lesson 2', 'is_completed' => true, 'is_locked' => false],
            ['id' => 3, 'title' => 'Lesson 3', 'is_completed' => false, 'is_locked' => false],
            ['id' => 4, 'title' => 'Lesson 4', 'is_completed' => false, 'is_locked' => true],
        ];

        return view('frontend.lessons', compact('lessons', 'progressPercent', 'userXp'));
    }

    // หน้าเรียนคำศัพท์ (Flashcards)
    public function lessonShow($id)
    {
        // Mock Data คำศัพท์ (รอเชื่อมกับ Content Data ของคนที่ 3)
        $vocabularies = [
            ['word' => 'Hello', 'meaning' => 'สวัสดี', 'pronunciation' => 'เฮล-โล', 'example' => 'Hello, nice to meet you.'],
            ['word' => 'Thank you', 'meaning' => 'ขอบคุณ', 'pronunciation' => 'แธงค์-ยู', 'example' => 'Thank you very much.'],
            ['word' => 'Goodbye', 'meaning' => 'ลาก่อน', 'pronunciation' => 'กู้ด-บาย', 'example' => 'Goodbye, see you tomorrow.'],
        ];

        return view('frontend.lesson-show', compact('vocabularies', 'id'));
    }

    // หน้าทำแบบทดสอบ (Quiz Flow)
    public function quizShow($id)
    {
        // Mock Data โจทย์ Quiz (รอเชื่อมกับ Quiz System ของคนที่ 4)
        $questions = [
            [
                'question' => 'คำว่า "สวัสดี" ในภาษาอังกฤษคือคำว่าอะไร?',
                'options' => ['Goodbye', 'Hello', 'Thank you', 'Sorry'],
                'correct' => 1
            ],
            [
                'question' => 'คำว่า "Thank you" แปลว่าอะไร?',
                'options' => ['ลาก่อน', 'ขอโทษ', 'ขอบคุณ', 'ยินดีด้วย'],
                'correct' => 2
            ],
        ];

        return view('frontend.quiz-show', compact('questions', 'id'));
    }
}