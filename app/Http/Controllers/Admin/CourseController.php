<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Language;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CourseController extends Controller
{
    /**
     * แสดง Course ทั้งหมดของภาษานี้ พร้อมฟอร์มเพิ่ม/แก้ไข/ลบ
     */
    public function index(Language $language): View
    {
        $courses = $language->courses()
            ->withCount('units')
            ->orderBy('title')
            ->get();

        return view('admin.courses.index', compact('language', 'courses'));
    }

    /**
     * เพิ่ม Course ใหม่ในภาษานี้
     */
    public function store(Request $request, Language $language): RedirectResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
        ]);

        $language->courses()->create($validated);

        return back()->with('success', 'เพิ่มคอร์สเรียบร้อยแล้ว');
    }

    /**
     * แก้ไขชื่อ Course
     */
    public function update(Request $request, Course $course): RedirectResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
        ]);

        $course->update($validated);

        return back()->with('success', 'แก้ไขคอร์สเรียบร้อยแล้ว');
    }

    /**
     * ลบ Course (Unit/Lesson/Content ภายใต้ Course นี้จะถูกลบตามไปด้วย
     * เพราะกำหนด cascade ไว้ที่ระดับฐานข้อมูลแล้ว)
     */
    public function destroy(Course $course): RedirectResponse
    {
        $language = $course->language;
        $unitCount = $course->units()->count();
        $title = $course->title;

        $course->delete();

        return redirect()
            ->route('admin.languages.courses.index', $language)
            ->with('success', "ลบคอร์ส \"{$title}\" และ {$unitCount} Unit ที่อยู่ภายใต้คอร์สนี้เรียบร้อยแล้ว");
    }
}
