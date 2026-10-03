<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Exercise;
use App\Models\Unit;
use App\Models\Vocabulary;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class UnitController extends Controller
{
    /**
     * แสดง Unit ทั้งหมดของ Course นี้ เรียงตามลำดับการเรียน
     * พร้อมฟอร์มเพิ่ม/แก้ไข/ลบ และปุ่มจัดลำดับ
     */
    public function index(Course $course): View
    {
        $course->load('language');

        $units = $course->units()
            ->withCount('lessons')
            ->get();

        return view('admin.units.index', compact('course', 'units'));
    }

    /**
     * เพิ่ม Unit ใหม่ใน Course นี้ (จะต่อท้ายลำดับปัจจุบันให้อัตโนมัติ)
     */
    public function store(Request $request, Course $course): RedirectResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
        ]);

        $course->units()->create($validated);

        return back()->with('success', 'เพิ่ม Unit เรียบร้อยแล้ว');
    }

    /**
     * แก้ไขชื่อ Unit (ไม่แก้ไข Course ที่สังกัด เพื่อไม่ให้โครงสร้างเพี้ยน)
     */
    public function update(Request $request, Unit $unit): RedirectResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
        ]);

        $unit->update($validated);

        return back()->with('success', 'แก้ไข Unit เรียบร้อยแล้ว');
    }

    /**
     * ลบ Unit (Lesson และ Content ทั้งหมดภายใต้ Unit นี้จะถูกลบตามไปด้วย
     * เพราะกำหนด cascade ไว้ที่ระดับฐานข้อมูลแล้ว) แล้วจัดลำดับ Unit
     * ที่เหลือใน Course ใหม่ให้ต่อเนื่องกัน (ไม่มีช่องว่าง)
     */
    public function destroy(Unit $unit): RedirectResponse
    {
        $course = $unit->course;
        $title = $unit->title;

        $lessonIds = $unit->lessons()->pluck('id');
        $lessonCount = $lessonIds->count();
        $vocabularyCount = Vocabulary::whereIn('lesson_id', $lessonIds)->count();
        $exerciseCount = Exercise::whereIn('lesson_id', $lessonIds)->count();

        $unit->delete();

        Unit::resequence($course->id);

        return redirect()
            ->route('admin.courses.units.index', $course)
            ->with('success', "ลบ Unit \"{$title}\" เรียบร้อยแล้ว (รวม {$lessonCount} Lesson, "
                ."{$vocabularyCount} คำศัพท์ และ {$exerciseCount} แบบฝึกหัดที่อยู่ภายใต้ Unit นี้)");
    }

    /**
     * จัดลำดับ Unit ใหม่ทั้งหมดใน Course นี้ในครั้งเดียว
     *
     * รับ order เป็น array ของ Unit id เรียงตามลำดับที่ต้องการ
     * ต้องครบทุก Unit ของ Course นี้พอดี (ไม่ขาด ไม่เกิน ไม่ซ้ำ)
     * เพื่อป้องกันไม่ให้ลำดับเพี้ยนจากการส่งข้อมูลไม่ครบ
     */
    public function reorder(Request $request, Course $course): RedirectResponse
    {
        $validated = $request->validate([
            'order' => ['required', 'array'],
            'order.*' => ['integer'],
        ]);

        $existingIds = $course->units()->pluck('id')->map(fn ($id) => (int) $id)->sort()->values()->all();
        $submittedIds = collect($validated['order'])->map(fn ($id) => (int) $id)->sort()->values()->all();

        if ($existingIds !== $submittedIds) {
            throw ValidationException::withMessages([
                'order' => 'รายการ Unit ที่ส่งมาไม่ตรงกับ Unit ทั้งหมดใน Course นี้',
            ]);
        }

        DB::transaction(function () use ($validated) {
            foreach (array_values($validated['order']) as $index => $unitId) {
                Unit::whereKey((int) $unitId)->update(['position' => $index + 1]);
            }
        });

        return back()->with('success', 'จัดลำดับ Unit เรียบร้อยแล้ว');
    }
}
