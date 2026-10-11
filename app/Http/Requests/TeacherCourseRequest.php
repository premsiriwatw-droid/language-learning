<?php

namespace App\Http\Requests;

use App\Models\Lesson;
use App\Models\TeacherCourse;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class TeacherCourseRequest extends FormRequest
{
    public function authorize(): bool
    {
        $course = $this->route('teacherCourse');

        return $course instanceof TeacherCourse
            ? $this->user()->can('update', $course)
            : $this->user()->can('create', TeacherCourse::class);
    }

    protected function prepareForValidation(): void
    {
        if ($this->input('price_type') === TeacherCourse::FREE) {
            $this->merge(['price' => null]);
        }

        $this->merge(['lessons' => array_values(array_filter((array) $this->input('lessons', [])))]);
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:120'],
            'description' => ['required', 'string', 'max:3000'],
            'language_id' => ['required', 'integer', Rule::exists('languages', 'id')],
            'level' => ['required', Rule::in(TeacherCourse::LEVELS)],
            'price_type' => ['required', Rule::in(TeacherCourse::PRICE_TYPES)],
            'price' => ['nullable', 'required_if:price_type,'.TeacherCourse::PAID, 'numeric', 'min:1', 'max:100000'],
            'enrollment_mode' => ['required', Rule::in(TeacherCourse::ENROLLMENT_MODES)],
            'lessons' => ['array', 'max:200'],
            'lessons.*' => ['integer', 'distinct'],
        ];
    }

    /** Curriculum may only use system lessons of the course's own language. */
    public function after(): array
    {
        return [
            function (Validator $validator) {
                // A course learners can see or are studying must keep at least one lesson.
                $course = $this->route('teacherCourse');
                if (
                    $course instanceof TeacherCourse
                    && $course->status !== TeacherCourse::DRAFT
                    && $this->input('lessons', []) === []
                ) {
                    $validator->errors()->add('lessons', 'คอร์สที่เปิดรับสมัครหรือมีผู้เรียนแล้วต้องมีบทเรียนอย่างน้อย 1 บท');
                }
            },
            function (Validator $validator) {
                $ids = collect($this->input('lessons', []))->map(fn ($id) => (int) $id)->unique();

                if ($ids->isEmpty() || $validator->errors()->has('language_id')) {
                    return;
                }

                $valid = Lesson::query()
                    ->whereIn('lessons.id', $ids)
                    ->whereHas('unit.course', fn ($q) => $q->where('language_id', (int) $this->input('language_id')))
                    ->count();

                if ($valid !== $ids->count()) {
                    $validator->errors()->add('lessons', 'เลือกได้เฉพาะบทเรียนของภาษาที่คอร์สนี้สอน');
                }
            },
        ];
    }

    public function messages(): array
    {
        return [
            'required' => 'กรุณากรอก :attribute',
            'required_if' => 'คอร์สแบบเสียเงินต้องกำหนด :attribute',
            'max' => ':attribute ยาวหรือมากเกินไป (ไม่เกิน :max)',
            'min' => ':attribute ต้องไม่น้อยกว่า :min',
            'numeric' => ':attribute ต้องเป็นตัวเลข',
            'in' => ':attribute ไม่ถูกต้อง',
            'exists' => ':attribute ไม่มีในระบบ',
            'distinct' => 'เลือกบทเรียนซ้ำกัน',
        ];
    }

    public function attributes(): array
    {
        return [
            'title' => 'ชื่อคอร์ส',
            'description' => 'รายละเอียดคอร์ส',
            'language_id' => 'ภาษา',
            'level' => 'ระดับ',
            'price_type' => 'ประเภทราคา',
            'price' => 'ราคา',
            'enrollment_mode' => 'วิธีรับผู้เรียน',
            'lessons' => 'บทเรียน',
        ];
    }
}
