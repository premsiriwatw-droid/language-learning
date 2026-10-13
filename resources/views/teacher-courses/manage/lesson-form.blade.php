@extends('layouts.app')

@php
    use App\Models\TeacherLesson;
    $editing = $lesson->exists;
    $videoSource = old('video_source', $lesson->hasVideoFile() ? 'file' : 'link');
@endphp

@section('title', $editing ? 'แก้ไขบทเรียน' : 'เพิ่มบทเรียน')

@section('content')
<div class="space-y-6">
    <div>
        <a href="{{ route('teacher.courses.edit', $course) }}#my-lessons" class="text-sm font-bold text-emerald-600">← {{ $course->title }}</a>
        <h1 class="mt-2 text-3xl font-extrabold text-gray-800">{{ $editing ? 'แก้ไขบทเรียน' : 'เพิ่มบทเรียน' }}</h1>
        <p class="mt-1 text-gray-500">ใส่อย่างน้อย 1 อย่าง: เนื้อหา เอกสาร หรือวิดีโอ</p>
        @if ($course->isPublished())
            <p class="mt-2 rounded-xl bg-amber-50 p-3 text-sm text-amber-800">คอร์สนี้เผยแพร่อยู่ ถ้าบันทึก คอร์สจะกลับไปรอแอดมินตรวจอีกครั้ง (ผู้เรียนเดิมยังเรียนต่อได้)</p>
        @endif
    </div>

    @include('teacher-courses.partials.flash')

    <form method="POST" enctype="multipart/form-data"
        action="{{ $editing ? route('teacher.courses.teacher-lessons.update', [$course, $lesson]) : route('teacher.courses.teacher-lessons.store', $course) }}"
        class="space-y-6 rounded-2xl border-2 border-gray-200 bg-white p-6"
        x-data="{ videoSource: '{{ $videoSource === 'file' ? 'file' : 'link' }}', sending: false }" @submit="sending = true">
        @csrf
        @if ($editing) @method('PUT') @endif

        <label class="block font-bold text-gray-700">ชื่อบทเรียน *
            <input type="text" name="title" value="{{ old('title', $lesson->title) }}" required maxlength="150"
                class="mt-1 w-full rounded-xl border-2 border-gray-200 px-3 py-2 font-normal" placeholder="เช่น บทที่ 1 คำทักทายในชีวิตประจำวัน">
        </label>

        <label class="block font-bold text-gray-700">เนื้อหา
            <textarea name="body" rows="8" maxlength="20000" class="mt-1 w-full rounded-xl border-2 border-gray-200 px-3 py-2 font-normal"
                placeholder="อธิบายเนื้อหา คำศัพท์ ตัวอย่างประโยค หรือคำแนะนำก่อนดูวิดีโอ">{{ old('body', $lesson->body) }}</textarea>
            <span class="text-xs font-normal text-gray-400">แสดงเป็นข้อความธรรมดา (ขึ้นบรรทัดใหม่ได้)</span>
        </label>

        <fieldset class="space-y-2">
            <legend class="font-bold text-gray-700">เอกสารประกอบ</legend>
            @if ($lesson->hasDocument())
                <div class="flex flex-wrap items-center gap-3 rounded-xl bg-gray-50 p-3 text-sm">
                    <span>📄 <a href="{{ route('teacher-courses.lessons.document', [$course, $lesson]) }}" class="font-bold text-emerald-700 underline">{{ $lesson->document_name }}</a>
                        <span class="text-gray-400">({{ TeacherLesson::sizeLabel($lesson->document_size) }})</span></span>
                    <label class="flex items-center gap-1 text-red-700"><input type="checkbox" name="remove_document" value="1" @checked(old('remove_document'))> ลบไฟล์นี้</label>
                </div>
                <p class="text-xs text-gray-500">อัปโหลดไฟล์ใหม่เพื่อแทนที่ไฟล์เดิม</p>
            @endif
            <input type="file" name="document" accept=".{{ implode(',.', TeacherLesson::DOCUMENT_EXTENSIONS) }}"
                class="block w-full rounded-xl border-2 border-dashed border-gray-300 p-3 text-sm">
            <p class="text-xs text-gray-500">PDF, Word, PowerPoint, Excel หรือ TXT ไม่เกิน 20 MB · ผู้เรียนดาวน์โหลดได้หลังเข้าร่วมคอร์สแล้ว</p>
        </fieldset>

        <fieldset class="space-y-3">
            <legend class="font-bold text-gray-700">วิดีโอ</legend>
            <div class="flex flex-wrap gap-4 text-sm">
                <label class="flex items-center gap-2"><input type="radio" name="video_source" value="link" x-model="videoSource" @checked($videoSource === 'link')> ลิงก์ YouTube / Vimeo</label>
                <label class="flex items-center gap-2"><input type="radio" name="video_source" value="file" x-model="videoSource" @checked($videoSource === 'file')> อัปโหลดไฟล์วิดีโอ</label>
            </div>

            <div x-show="videoSource === 'link'">
                <input type="url" name="video_url" value="{{ old('video_url', $lesson->video_url) }}" maxlength="500" :disabled="videoSource !== 'link'"
                    class="w-full rounded-xl border-2 border-gray-200 px-3 py-2" placeholder="https://www.youtube.com/watch?v=...">
                <p class="mt-1 text-xs text-gray-500">ถ้าเป็นวิดีโอที่อัปโหลดไว้แล้ว การใส่ลิงก์จะแทนที่ไฟล์เดิม</p>
            </div>

            <div x-show="videoSource === 'file'" x-cloak class="space-y-2">
                @if ($lesson->hasVideoFile())
                    <div class="space-y-2 rounded-xl bg-gray-50 p-3 text-sm">
                        <video controls preload="metadata" class="max-h-64 w-full rounded-lg bg-black" src="{{ route('teacher-courses.lessons.video', [$course, $lesson]) }}"></video>
                        <p>🎬 {{ $lesson->video_name }} <span class="text-gray-400">({{ TeacherLesson::sizeLabel($lesson->video_size) }})</span></p>
                    </div>
                @endif
                <input type="file" name="video_file" accept="video/mp4,video/webm,.mp4,.webm" :disabled="videoSource !== 'file'"
                    class="block w-full rounded-xl border-2 border-dashed border-gray-300 p-3 text-sm">
                <p class="text-xs text-gray-500">MP4 หรือ WebM ไม่เกิน 100 MB (ไฟล์ใหญ่อาจถูกจำกัดโดยการตั้งค่า upload ของเซิร์ฟเวอร์ แนะนำใช้ลิงก์ YouTube สำหรับวิดีโอยาว)</p>
            </div>

            @if ($lesson->hasVideo())
                <label class="flex items-center gap-1 text-sm text-red-700"><input type="checkbox" name="remove_video" value="1" @checked(old('remove_video'))> ลบวิดีโอออกจากบทเรียนนี้</label>
            @endif
        </fieldset>

        <button type="submit" :disabled="sending" class="w-full rounded-xl bg-emerald-500 py-3 font-extrabold text-white hover:bg-emerald-600 disabled:opacity-60">
            <span x-show="! sending">{{ $editing ? 'บันทึกบทเรียน' : 'เพิ่มบทเรียน' }}</span>
            <span x-show="sending" x-cloak>กำลังอัปโหลด...</span>
        </button>
    </form>
</div>
@endsection
