<?php

namespace App\Http\Requests;

use App\Models\TeacherCourse;
use App\Models\TeacherLesson;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class TeacherLessonRequest extends FormRequest
{
    public function authorize(): bool
    {
        $course = $this->route('teacherCourse');

        return $course instanceof TeacherCourse && $this->user()->can('update', $course);
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'video_url' => trim((string) $this->input('video_url')) ?: null,
            'remove_document' => $this->boolean('remove_document'),
            'remove_video' => $this->boolean('remove_video'),
        ]);
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:150'],
            'body' => ['nullable', 'string', 'max:20000'],
            'video_url' => ['nullable', 'string', 'max:500', 'url:http,https'],
            'video_file' => [
                'nullable', 'file', 'prohibits:video_url',
                'mimes:'.implode(',', array_keys(TeacherLesson::VIDEO_TYPES)),
                'extensions:'.implode(',', array_keys(TeacherLesson::VIDEO_TYPES)),
                'max:'.TeacherLesson::VIDEO_MAX_KB,
            ],
            'document' => [
                'nullable', 'file',
                'mimes:'.implode(',', TeacherLesson::DOCUMENT_EXTENSIONS),
                'extensions:'.implode(',', TeacherLesson::DOCUMENT_EXTENSIONS),
                'max:'.TeacherLesson::DOCUMENT_MAX_KB,
            ],
            'remove_document' => ['boolean'],
            'remove_video' => ['boolean'],
        ];
    }

    public function after(): array
    {
        return [
            function (Validator $validator) {
                $url = $this->input('video_url');
                if ($url !== null && ! $validator->errors()->has('video_url') && TeacherLesson::embedUrlFor($url) === null) {
                    $validator->errors()->add('video_url', 'รองรับเฉพาะลิงก์ YouTube หรือ Vimeo (หรืออัปโหลดไฟล์วิดีโอแทน)');
                }

                if ($validator->errors()->isNotEmpty()) {
                    return;
                }

                $lesson = $this->route('teacherLesson');
                $keepsDocument = $lesson instanceof TeacherLesson && $lesson->hasDocument() && ! $this->boolean('remove_document');
                $keepsVideoFile = $lesson instanceof TeacherLesson && $lesson->hasVideoFile() && ! $this->boolean('remove_video') && $url === null;

                $hasContent = trim((string) $this->input('body')) !== ''
                    || $url !== null
                    || $this->hasFile('video_file') || $this->hasFile('document')
                    || $keepsDocument || $keepsVideoFile;

                if (! $hasContent) {
                    $validator->errors()->add('body', 'บทเรียนต้องมีเนื้อหาอย่างน้อย 1 อย่าง: ข้อความ เอกสาร หรือวิดีโอ');
                }
            },
        ];
    }

    public function messages(): array
    {
        return [
            'required' => 'กรุณากรอก :attribute',
            'max' => ':attribute ใหญ่หรือยาวเกินไป (ไม่เกิน :max)',
            'video_file.max' => 'ไฟล์วิดีโอต้องไม่เกิน 100 MB',
            'document.max' => 'ไฟล์เอกสารต้องไม่เกิน 20 MB',
            'url' => ':attribute ต้องเป็นลิงก์ที่ขึ้นต้นด้วย http หรือ https',
            'mimes' => ':attribute ต้องเป็นไฟล์ :values',
            'extensions' => ':attribute ต้องเป็นไฟล์ :values',
            'uploaded' => 'อัปโหลด :attribute ไม่สำเร็จ (ไฟล์อาจใหญ่เกินกว่าที่เซิร์ฟเวอร์รับได้)',
            'file' => 'อัปโหลด :attribute ไม่สำเร็จ',
            'prohibits' => 'เลือกอย่างใดอย่างหนึ่ง: ลิงก์วิดีโอ หรือ อัปโหลดไฟล์วิดีโอ',
        ];
    }

    public function attributes(): array
    {
        return [
            'title' => 'ชื่อบทเรียน',
            'body' => 'เนื้อหา',
            'video_url' => 'ลิงก์วิดีโอ',
            'video_file' => 'ไฟล์วิดีโอ',
            'document' => 'ไฟล์เอกสาร',
        ];
    }
}
