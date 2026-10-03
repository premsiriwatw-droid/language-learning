<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\ManagesLearningStructure;
use App\Http\Controllers\Controller;
use App\Models\Language;
use App\Services\LearningStructure\DeletionImpact;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class LanguageController extends Controller
{
    use ManagesLearningStructure;

    /**
     * แสดงรายการภาษาทั้งหมด พร้อมฟอร์มเพิ่ม/แก้ไข
     */
    public function index(): View
    {
        $languages = Language::withCount('courses')
            ->orderBy('name')
            ->get();

        return view('admin.languages.index', compact('languages'));
    }

    /**
     * เพิ่มภาษาใหม่ (ชื่อห้ามซ้ำ)
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate(
            ['name' => ['required', 'string', 'max:255', Rule::unique('languages', 'name')]],
            $this->structureMessages(),
            $this->structureAttributes(),
        );

        Language::create($validated);

        return back()->with('success', 'เพิ่มภาษาเรียบร้อยแล้ว');
    }

    /**
     * แก้ไขชื่อภาษา (ชื่อห้ามซ้ำกับภาษาอื่น)
     */
    public function update(Request $request, Language $language): RedirectResponse
    {
        $validated = $request->validate(
            ['name' => ['required', 'string', 'max:255', Rule::unique('languages', 'name')->ignore($language)]],
            $this->structureMessages(),
            $this->structureAttributes(),
        );

        $language->update($validated);

        return back()->with('success', 'แก้ไขภาษาเรียบร้อยแล้ว');
    }

    /**
     * หน้ายืนยันการลบ แสดงข้อมูลลูกทั้งหมดที่จะถูกลบตามไปด้วย
     */
    public function confirmDestroy(Language $language): View
    {
        return view('admin.confirm-delete', [
            'kind' => 'ภาษา',
            'name' => $language->name,
            'impact' => DeletionImpact::for($language),
            'action' => route('admin.languages.destroy', $language),
            'cancelUrl' => route('admin.languages.index'),
        ]);
    }

    /**
     * ลบภาษา (Course/Unit/Lesson/Content ภายใต้ภาษานี้ถูกลบตาม cascade ของฐานข้อมูล)
     * ต้องพิมพ์ชื่อภาษายืนยันก่อน
     */
    public function destroy(Request $request, Language $language): RedirectResponse
    {
        $this->ensureDeletionConfirmed($request, $language->name);

        $name = $language->name;
        $language->delete();

        return redirect()
            ->route('admin.languages.index')
            ->with('success', "ลบภาษา \"{$name}\" และข้อมูลทั้งหมดภายใต้ภาษานี้เรียบร้อยแล้ว");
    }
}
