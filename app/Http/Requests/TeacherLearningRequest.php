<?php

namespace App\Http\Requests;

use App\Services\TeacherCourses\TeacherLearningService;
use Illuminate\Foundation\Http\FormRequest;

abstract class TeacherLearningRequest extends FormRequest
{
    public function authorize(): bool
    {
        $service = app(TeacherLearningService::class);
        $course = $this->route('teacherCourse');
        $studentId = $this->route('student');

        if ($studentId !== null) {
            $service->teacherStudent($this->user(), $course, $studentId);
        } else {
            $service->learner($this->user(), $course);
        }

        return true;
    }

    public function messages(): array
    {
        return [
            'required' => 'กรุณากรอก :attribute',
            'max' => ':attribute ยาวหรือมากเกินไป (ไม่เกิน :max)',
            'integer' => ':attribute ไม่ถูกต้อง',
            'regex' => 'เลือกบทเรียนหรือ Unit ให้ถูกต้อง',
            'date' => 'กรุณาระบุ :attribute ให้ถูกต้อง',
            'after' => ':attribute ต้องเป็นเวลาในอนาคต',
        ];
    }

    public function attributes(): array
    {
        return [
            'lesson_id' => 'บทเรียน', 'reason' => 'เหตุผล', 'title' => 'ชื่องาน',
            'instructions' => 'คำแนะนำ', 'target' => 'บทเรียนหรือ Unit',
            'due_at' => 'กำหนดส่ง', 'body' => 'ข้อความ',
        ];
    }
}
