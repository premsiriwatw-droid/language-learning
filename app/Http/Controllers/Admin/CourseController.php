<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\ManagesLearningStructure;
use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Language;
use App\Services\LearningStructure\DeletionImpact;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class CourseController extends Controller
{
    use ManagesLearningStructure;

    /**
     * แสดง Course ทั้งหมดของภาษานี้ พร้อมฟอร์มเพิ่ม/แก้ไข
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
     * เพิ่ม Course ใหม่ในภาษานี้ (ชื่อห้ามซ้ำภายในภาษาเดียวกัน)
     * language_id มาจาก URL เท่านั้น ไม่รับจากฟอร์ม
     */
    public function store(Request $request, Language $language): RedirectResponse
    {
        $validated = $request->validate(
            ['title' => [
                'required', 'string', 'max:255',
                Rule::unique('courses', 'title')->where('language_id', $language->id),
            ]],
            $this->structureMessages(),
            $this->structureAttributes(),
        );

        $language->courses()->create($validated);

        return back()->with('success', 'เพิ่มคอร์สเรียบร้อยแล้ว');
    }

    /**
     * แก้ไขชื่อ Course (ย้ายไปภาษาอื่นไม่ได้ เพราะ language_id ไม่ fillable)
     */
    public function update(Request $request, Course $course): RedirectResponse
    {
        $validated = $request->validate(
            ['title' => [
                'required', 'string', 'max:255',
                Rule::unique('courses', 'title')->where('language_id', $course->language_id)->ignore($course),
            ]],
            $this->structureMessages(),
            $this->structureAttributes(),
        );

        $course->update($validated);

        return back()->with('success', 'แก้ไขคอร์สเรียบร้อยแล้ว');
    }

    /**
     * หน้ายืนยันการลบ แสดงข้อมูลลูกทั้งหมดที่จะถูกลบตามไปด้วย
     */
    public function confirmDestroy(Course $course): View
    {
        return view('admin.confirm-delete', [
            'kind' => 'คอร์ส',
            'name' => $course->title,
            'impact' => DeletionImpact::for($course),
            'action' => route('admin.courses.destroy', $course),
            'cancelUrl' => route('admin.languages.courses.index', $course->language_id),
        ]);
    }

    /**
     * ลบ Course (Unit/Lesson/Content ภายใต้ Course นี้ถูกลบตาม cascade ของฐานข้อมูล)
     * ต้องพิมพ์ชื่อคอร์สยืนยันก่อน
     */
    public function destroy(Request $request, Course $course): RedirectResponse
    {
        $this->ensureDeletionConfirmed($request, $course->title);

        $languageId = $course->language_id;
        $title = $course->title;
        $course->delete();

        return redirect()
            ->route('admin.languages.courses.index', $languageId)
            ->with('success', "ลบคอร์ส \"{$title}\" และข้อมูลทั้งหมดภายใต้คอร์สนี้เรียบร้อยแล้ว");
    }
}
