<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\ManagesLearningStructure;
use App\Http\Controllers\Controller;
use App\Models\Lesson;
use App\Models\Unit;
use App\Services\LearningStructure\DeletionImpact;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class LessonController extends Controller
{
    use ManagesLearningStructure;

    /**
     * แสดง Lesson ทั้งหมดของ Unit นี้ เรียงตามลำดับการเรียน
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
     * เพิ่ม Lesson ใหม่ต่อท้ายลำดับของ Unit นี้ (ชื่อห้ามซ้ำภายใน Unit เดียวกัน)
     * unit_id มาจาก URL เท่านั้น ไม่รับจากฟอร์ม
     */
    public function store(Request $request, Unit $unit): RedirectResponse
    {
        $validated = $request->validate(
            $this->lessonRules($unit->id),
            $this->structureMessages(),
            $this->structureAttributes(),
        );

        DB::transaction(function () use ($unit, $validated) {
            // ล็อก parent ไว้ให้การคำนวณ position ถัดไปไม่ชนกันเมื่อเพิ่มพร้อมกัน
            Unit::whereKey($unit->id)->lockForUpdate()->first();

            $unit->lessons()->create($validated);
        });

        return back()->with('success', 'เพิ่ม Lesson เรียบร้อยแล้ว');
    }

    /**
     * แก้ไข Lesson (ย้าย Unit หรือแก้ position ผ่านฟอร์มนี้ไม่ได้
     * และไม่แตะ Vocabulary/Exercise ที่ผูกอยู่ ซึ่งเป็นงานของ Content)
     */
    public function update(Request $request, Lesson $lesson): RedirectResponse
    {
        $validated = $request->validate(
            $this->lessonRules($lesson->unit_id, $lesson),
            $this->structureMessages(),
            $this->structureAttributes(),
        );

        $lesson->update($validated);

        return back()->with('success', 'แก้ไข Lesson เรียบร้อยแล้ว');
    }

    /**
     * หน้ายืนยันการลบ แสดงข้อมูลลูกทั้งหมดที่จะถูกลบตามไปด้วย
     */
    public function confirmDestroy(Lesson $lesson): View
    {
        return view('admin.confirm-delete', [
            'kind' => 'Lesson',
            'name' => $lesson->title,
            'impact' => DeletionImpact::for($lesson),
            'action' => route('admin.lessons.destroy', $lesson),
            'cancelUrl' => route('admin.units.lessons.index', $lesson->unit_id),
        ]);
    }

    /**
     * ลบ Lesson (Vocabulary/Exercise/Question/Answer/ความคืบหน้า ถูกลบตาม
     * cascade ของฐานข้อมูล) แล้วเรียงลำดับ Lesson ที่เหลือใหม่ให้ต่อเนื่อง 1..N
     */
    public function destroy(Request $request, Lesson $lesson): RedirectResponse
    {
        $this->ensureDeletionConfirmed($request, $lesson->title);

        $unitId = $lesson->unit_id;
        $title = $lesson->title;

        DB::transaction(function () use ($lesson, $unitId) {
            Unit::whereKey($unitId)->lockForUpdate()->first();

            $lesson->delete();

            Lesson::resequence($unitId);
        });

        return redirect()
            ->route('admin.units.lessons.index', $unitId)
            ->with('success', "ลบ Lesson \"{$title}\" และเนื้อหาทั้งหมดภายใต้ Lesson นี้เรียบร้อยแล้ว");
    }

    /**
     * จัดลำดับ Lesson ใหม่ทั้งหมดใน Unit นี้ในครั้งเดียว
     * order ต้องเป็น id ของ Lesson ใน Unit นี้ครบทุกตัวพอดี
     */
    public function reorder(Request $request, Unit $unit): RedirectResponse
    {
        DB::transaction(function () use ($request, $unit) {
            Unit::whereKey($unit->id)->lockForUpdate()->first();

            $order = $this->validatedOrder($request, $unit->lessons()->pluck('id')->all(), 'Lesson');

            foreach ($order as $index => $lessonId) {
                Lesson::whereKey($lessonId)
                    ->where('unit_id', $unit->id)
                    ->update(['position' => $index + 1]);
            }
        });

        return back()->with('success', 'จัดลำดับ Lesson เรียบร้อยแล้ว');
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    private function lessonRules(int $unitId, ?Lesson $ignore = null): array
    {
        $unique = Rule::unique('lessons', 'title')->where('unit_id', $unitId);

        if ($ignore) {
            $unique->ignore($ignore);
        }

        return [
            'title' => ['required', 'string', 'max:255', $unique],
            'content' => ['nullable', 'string', 'max:65000'],
        ];
    }
}
