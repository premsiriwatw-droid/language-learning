<?php

namespace App\Http\Controllers;

use App\Models\Language;
use App\Models\Course;
use App\Models\Unit;
use App\Models\Lesson;
use Illuminate\Http\Request;

class LearningController extends Controller
{
    // 1. หน้าเลือกภาษา / คอร์ส
    public function indexLanguages()
    {
        // ดึงภาษาทั้งหมดพร้อม คอร์สของภาษานั้นๆ
        $languages = Language::with('courses')->get();

        return view('languages.index', compact('languages'));
    }

    // 2. หน้าเลือก Unit ของแต่ละ Course
    public function showUnits(Course $course)
    {
        // ใช้ Route Model Binding ดึง Unit ของ Course นั้นๆ
        $course->load('units');

        return view('courses.units', compact('course'));
    }

    // 3. หน้าเลือก Lesson
    public function showLessons(Unit $unit)
    {
        // ดึง Lesson ทั้งหมดใน Unit นั้นๆ
        $unit->load('lessons');

        return view('units.lessons', compact('unit'));
    }

    // 4. หน้าแสดงเนื้อหาการเรียน (Vocabularies & Exercises)
    public function showLessonContent(Lesson $lesson)
    {
        // ดึงคำศัพท์และแบบฝึกหัดในบทเรียนนั้นมาแสดง
        $lesson->load(['vocabularies', 'exercises']);

        return view('lessons.show', compact('lesson'));
    }
}