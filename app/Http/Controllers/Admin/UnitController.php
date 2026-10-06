<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\ManagesLearningStructure;
use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Unit;
use App\Services\LearningStructure\DeletionImpact;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class UnitController extends Controller
{
    use ManagesLearningStructure;

    /**
     * แสดง Unit ทั้งหมดของ Course นี้ เรียงตามลำดับการเรียน
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
     * เพิ่ม Unit ใหม่ต่อท้ายลำดับของ Course นี้ (ชื่อห้ามซ้ำภายใน Course เดียวกัน)
     * course_id มาจาก URL เท่านั้น ไม่รับจากฟอร์ม
     */
    public function store(Request $request, Course $course): RedirectResponse
    {
        $validated = $request->validate(
            ['title' => [
                'required', 'string', 'max:255',
                Rule::unique('units', 'title')->where('course_id', $course->id),
            ]],
            $this->structureMessages(),
            $this->structureAttributes(),
        );

        DB::transaction(function () use ($course, $validated) {
            // ล็อก parent ไว้ให้การคำนวณ position ถัดไปไม่ชนกันเมื่อเพิ่มพร้อมกัน
            Course::whereKey($course->id)->lockForUpdate()->first();

            $course->units()->create($validated);
        });

        return back()->with('success', 'เพิ่ม Unit เรียบร้อยแล้ว');
    }

    /**
     * แก้ไขชื่อ Unit (ย้าย Course หรือแก้ position ผ่านฟอร์มนี้ไม่ได้)
     */
    public function update(Request $request, Unit $unit): RedirectResponse
    {
        $validated = $request->validate(
            ['title' => [
                'required', 'string', 'max:255',
                Rule::unique('units', 'title')->where('course_id', $unit->course_id)->ignore($unit),
            ]],
            $this->structureMessages(),
            $this->structureAttributes(),
        );

        $unit->update($validated);

        return back()->with('success', 'แก้ไข Unit เรียบร้อยแล้ว');
    }

    /**
     * หน้ายืนยันการลบ แสดงข้อมูลลูกทั้งหมดที่จะถูกลบตามไปด้วย
     */
    public function confirmDestroy(Unit $unit): View
    {
        return view('admin.confirm-delete', [
            'kind' => 'Unit',
            'name' => $unit->title,
            'impact' => DeletionImpact::for($unit),
            'action' => route('admin.units.destroy', $unit),
            'cancelUrl' => route('admin.courses.units.index', $unit->course_id),
        ]);
    }

    /**
     * ลบ Unit (Lesson/Content ภายใต้ Unit นี้ถูกลบตาม cascade ของฐานข้อมูล)
     * แล้วเรียงลำดับ Unit ที่เหลือใหม่ให้ต่อเนื่อง 1..N
     */
    public function destroy(Request $request, Unit $unit): RedirectResponse
    {
        $this->ensureDeletionConfirmed($request, $unit->title);

        $courseId = $unit->course_id;
        $title = $unit->title;

        DB::transaction(function () use ($unit, $courseId) {
            Course::whereKey($courseId)->lockForUpdate()->first();

            $unit->delete();

            Unit::resequence($courseId);
        });

        return redirect()
            ->route('admin.courses.units.index', $courseId)
            ->with('success', "ลบ Unit \"{$title}\" และข้อมูลทั้งหมดภายใต้ Unit นี้เรียบร้อยแล้ว");
    }

    /**
     * จัดลำดับ Unit ใหม่ทั้งหมดใน Course นี้ในครั้งเดียว
     * order ต้องเป็น id ของ Unit ใน Course นี้ครบทุกตัวพอดี
     */
    public function reorder(Request $request, Course $course): RedirectResponse
    {
        DB::transaction(function () use ($request, $course) {
            Course::whereKey($course->id)->lockForUpdate()->first();

            $order = $this->validatedOrder($request, $course->units()->pluck('id')->all(), 'Unit');

            foreach ($order as $index => $unitId) {
                Unit::whereKey($unitId)
                    ->where('course_id', $course->id)
                    ->update(['position' => $index + 1]);
            }
        });

        return back()->with('success', 'จัดลำดับ Unit เรียบร้อยแล้ว');
    }
}
