<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Language;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LanguageController extends Controller
{
    /**
     * แสดงรายการภาษาทั้งหมด พร้อมฟอร์มเพิ่ม/แก้ไข/ลบ
     */
    public function index(): View
    {
        $languages = Language::withCount('courses')
            ->orderBy('name')
            ->get();

        return view('admin.languages.index', compact('languages'));
    }

    /**
     * เพิ่มภาษาใหม่
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
        ]);

        Language::create($validated);

        return back()->with('success', 'เพิ่มภาษาเรียบร้อยแล้ว');
    }

    /**
     * แก้ไขชื่อภาษา
     */
    public function update(Request $request, Language $language): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
        ]);

        $language->update($validated);

        return back()->with('success', 'แก้ไขภาษาเรียบร้อยแล้ว');
    }

    /**
     * ลบภาษา (Course/Unit/Lesson/Content ของภาษานี้ทั้งหมดจะถูกลบตามไปด้วย
     * เพราะกำหนด cascade ไว้ที่ระดับฐานข้อมูลแล้ว)
     */
    public function destroy(Language $language): RedirectResponse
    {
        $courseCount = $language->courses()->count();
        $name = $language->name;

        $language->delete();

        return redirect()
            ->route('admin.languages.index')
            ->with('success', "ลบภาษา \"{$name}\" และ {$courseCount} คอร์สที่อยู่ภายใต้ภาษานี้เรียบร้อยแล้ว");
    }
}
