<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Lesson;
use App\Models\Unit;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class LessonController extends Controller
{
    /**
     * แสดง Lesson ทั้งหมดของ Unit นี้ เรียงตามลำดับการเรียน
     * พร้อมฟอร์มเพิ่ม/แก้ไข/ลบ และปุ่มจัดลำดับ
     */
    public function index(Unit $unit): View
    {
        $unit->load('course.language');

        $lessons = $unit->lessons()
            ->withCount(['vocabularies', 'exercises'])
            ->get();

        return view('admin.lessons.index', compact('unit', 'lessons'));
    }

    /**
     * เพิ่ม Lesson ใหม่ใน Unit นี้ (จะต่อท้ายลำดับปัจจุบันให้อัตโนมัติ)
     */
    public function store(Request $request, Unit $unit): RedirectResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'content' => ['nullable', 'string'],
        ]);

        $unit->lessons()->create($validated);

        return back()->with('success', 'เพิ่ม Lesson เรียบร้อยแล้ว');
    }

    /**
     * แก้ไข Lesson (ไม่แก้ไข Unit ที่สังกัด เพื่อไม่ให้โครงสร้างเพี้ยน
     * และไม่แตะต้อง Vocabulary/Exercise ที่ผูกอยู่ ซึ่งเป็นงานของ Content)
     */
    public function update(Request $request, Lesson $lesson): RedirectResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'content' => ['nullable', 'string'],
        ]);

        $lesson->update($validated);

        return back()->with('success', 'แก้ไข Lesson เรียบร้อยแล้ว');
    }

    /**
     * ลบ Lesson (Vocabulary/Exercise/Question/Answer ภายใต้ Lesson นี้
     * จะถูกลบตามไปด้วย เพราะกำหนด cascade ไว้ที่ระดับฐานข้อมูลแล้ว)
     * แล้วจัดลำดับ Lesson ที่เหลือใน Unit ใหม่ให้ต่อเนื่องกัน (ไม่มีช่องว่าง)
     */
    public function destroy(Lesson $lesson): RedirectResponse
    {
        $unit = $lesson->unit;
        $title = $lesson->title;
        $vocabularyCount = $lesson->vocabularies()->count();
        $exerciseCount = $lesson->exercises()->count();

        $lesson->delete();

        Lesson::resequence($unit->id);

        return redirect()
            ->route('admin.units.lessons.index', $unit)
            ->with('success', "ลบ Lesson \"{$title}\" เรียบร้อยแล้ว (รวม {$vocabularyCount} คำศัพท์ "
                ."และ {$exerciseCount} แบบฝึกหัดที่อยู่ภายใต้ Lesson นี้)");
    }

    /**
     * จัดลำดับ Lesson ใหม่ทั้งหมดใน Unit นี้ในครั้งเดียว
     *
     * รับ order เป็น array ของ Lesson id เรียงตามลำดับที่ต้องการ
     * ต้องครบทุก Lesson ของ Unit นี้พอดี (ไม่ขาด ไม่เกิน ไม่ซ้ำ)
     * เพื่อป้องกันไม่ให้ลำดับเพี้ยนจากการส่งข้อมูลไม่ครบ
     */
    public function reorder(Request $request, Unit $unit): RedirectResponse
    {
        $validated = $request->validate([
            'order' => ['required', 'array'],
            'order.*' => ['integer'],
        ]);

        $existingIds = $unit->lessons()->pluck('id')->map(fn ($id) => (int) $id)->sort()->values()->all();
        $submittedIds = collect($validated['order'])->map(fn ($id) => (int) $id)->sort()->values()->all();

        if ($existingIds !== $submittedIds) {
            throw ValidationException::withMessages([
                'order' => 'รายการ Lesson ที่ส่งมาไม่ตรงกับ Lesson ทั้งหมดใน Unit นี้',
            ]);
        }

        DB::transaction(function () use ($validated) {
            foreach (array_values($validated['order']) as $index => $lessonId) {
                Lesson::whereKey((int) $lessonId)->update(['position' => $index + 1]);
            }
        });

        return back()->with('success', 'จัดลำดับ Lesson เรียบร้อยแล้ว');
    }
}
