<?php

namespace App\Http\Controllers\Admin\Concerns;

use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

/**
 * กฎกลางของหน้า Admin Learning Structure (Language / Course / Unit / Lesson)
 * ใช้ร่วมกันเพื่อให้ validation, การยืนยันการลบ และการจัดลำดับ
 * ทำงานเหมือนกันทุกระดับ
 */
trait ManagesLearningStructure
{
    /**
     * ข้อความ validation ภาษาไทย
     *
     * @return array<string, string>
     */
    protected function structureMessages(): array
    {
        return [
            'required' => 'กรุณากรอก :attribute',
            'string' => ':attribute ต้องเป็นข้อความ',
            'max' => ':attribute ต้องยาวไม่เกิน :max ตัวอักษร',
            'unique' => ':attribute นี้มีอยู่แล้วในระดับเดียวกัน',
            'array' => 'ข้อมูล :attribute ไม่ถูกต้อง',
            'integer' => 'ข้อมูล :attribute ไม่ถูกต้อง',
            'distinct' => 'รายการ :attribute มีค่าซ้ำกัน',
        ];
    }

    /**
     * @return array<string, string>
     */
    protected function structureAttributes(): array
    {
        return [
            'name' => 'ชื่อภาษา',
            'title' => 'ชื่อ',
            'content' => 'คำอธิบาย',
            'order' => 'ลำดับ',
            'order.*' => 'ลำดับ',
            'confirm_name' => 'ชื่อที่ใช้ยืนยัน',
        ];
    }

    /**
     * ฝั่ง server ต้องได้รับชื่อที่พิมพ์ยืนยันตรงกับชื่อจริงก่อนลบเสมอ
     * (ไม่พึ่ง confirm() ของ browser อย่างเดียว เพราะข้ามได้)
     */
    protected function ensureDeletionConfirmed(Request $request, string $expectedName): void
    {
        $request->validate(
            ['confirm_name' => ['required', 'string']],
            $this->structureMessages(),
            $this->structureAttributes(),
        );

        if (trim((string) $request->input('confirm_name')) !== trim($expectedName)) {
            throw ValidationException::withMessages([
                'confirm_name' => 'ชื่อที่พิมพ์ไม่ตรงกับ "'.$expectedName.'" จึงยังไม่ได้ลบ',
            ]);
        }
    }

    /**
     * ตรวจรายการลำดับใหม่: ต้องเป็น id ของลูกใน parent นี้ครบทุกตัวพอดี
     * ไม่ขาด ไม่เกิน ไม่ซ้ำ และไม่มี id ของ parent อื่นปนมา
     *
     * @param  array<int, int|string>  $existingIds
     * @return array<int, int>  id ตามลำดับใหม่
     */
    protected function validatedOrder(Request $request, array $existingIds, string $label): array
    {
        $validated = $request->validate(
            [
                'order' => ['required', 'array'],
                'order.*' => ['required', 'integer', 'distinct'],
            ],
            $this->structureMessages(),
            $this->structureAttributes(),
        );

        $submitted = array_map('intval', array_values($validated['order']));

        $expected = array_map('intval', $existingIds);
        $sortedSubmitted = $submitted;
        sort($expected);
        sort($sortedSubmitted);

        if ($expected !== $sortedSubmitted) {
            throw ValidationException::withMessages([
                'order' => "รายการ {$label} ที่ส่งมาไม่ตรงกับข้อมูลปัจจุบัน (อาจมีคนเพิ่ม/ลบระหว่างนี้) กรุณารีเฟรชหน้าแล้วลองใหม่",
            ]);
        }

        return $submitted;
    }
}
