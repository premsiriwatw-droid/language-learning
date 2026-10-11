@extends('layouts.app')

@php
    use App\Models\TeacherCourse;
    $editing = $course->exists;
    $currentLanguage = (string) old('language_id', $course->language_id);
    $checked = collect(old('lessons', $selectedLessons))->map(fn ($id) => (int) $id)->all();
    $priceType = old('price_type', $course->price_type);
@endphp

@section('title', $editing ? 'แก้ไขคอร์ส' : 'สร้างคอร์สใหม่')

@section('content')
<div class="space-y-6" x-data="{ language: '{{ $currentLanguage }}', priceType: '{{ $priceType }}' }">
    <div>
        <a href="{{ route('teacher.courses.index') }}" class="text-sm font-bold text-emerald-600">← คอร์สของฉัน</a>
        <h1 class="mt-2 text-3xl font-extrabold text-gray-800">{{ $editing ? 'แก้ไขคอร์ส' : 'สร้างคอร์สใหม่' }}</h1>
        @if ($editing)
            <p class="mt-1 text-gray-500">สถานะ: <strong>{{ TeacherCourse::STATUS_LABELS[$course->status] }}</strong></p>
        @endif
    </div>

    @include('teacher-courses.partials.flash')

    @if ($editing)
        <div class="flex flex-wrap gap-2 rounded-2xl border-2 border-gray-200 bg-white p-4">
            @foreach ([TeacherCourse::PUBLISHED => 'เปิดรับสมัคร', TeacherCourse::CLOSED => 'ปิดคอร์ส', TeacherCourse::DRAFT => 'กลับเป็นฉบับร่าง'] as $status => $label)
                @continue($course->status === $status)
                <form method="POST" action="{{ route('teacher.courses.status', $course) }}">
                    @csrf
                    @method('PATCH')
                    <input type="hidden" name="status" value="{{ $status }}">
                    <button type="submit" class="rounded-xl px-4 py-2 font-bold {{ $status === TeacherCourse::PUBLISHED ? 'bg-emerald-500 text-white' : 'bg-gray-100 text-gray-700' }}">{{ $label }}</button>
                </form>
            @endforeach
            <a href="{{ route('teacher.courses.enrollments', $course) }}" class="ml-auto rounded-xl border-2 border-indigo-200 px-4 py-2 font-bold text-indigo-700">คำขอเข้าเรียน →</a>
        </div>
    @endif

    <form method="POST" action="{{ $editing ? route('teacher.courses.update', $course) : route('teacher.courses.store') }}" class="space-y-5 rounded-2xl border-2 border-gray-200 bg-white p-6">
        @csrf
        @if ($editing) @method('PUT') @endif

        <label class="block font-bold text-gray-700">ชื่อคอร์ส *
            <input type="text" name="title" value="{{ old('title', $course->title) }}" required maxlength="120" class="mt-1 w-full rounded-xl border-2 border-gray-200 px-3 py-2 font-normal" placeholder="เช่น English Conversation Beginner">
        </label>

        <label class="block font-bold text-gray-700">รายละเอียดคอร์ส *
            <textarea name="description" rows="4" required maxlength="3000" class="mt-1 w-full rounded-xl border-2 border-gray-200 px-3 py-2 font-normal" placeholder="คอร์สนี้เหมาะกับใคร เรียนแล้วได้อะไร">{{ old('description', $course->description) }}</textarea>
        </label>

        <div class="grid gap-4 sm:grid-cols-2">
            <label class="block font-bold text-gray-700">ภาษา *
                <select name="language_id" x-model="language" required class="mt-1 w-full rounded-xl border-2 border-gray-200 bg-white px-3 py-2 font-normal">
                    <option value="">เลือกภาษา</option>
                    @foreach ($languages as $language)
                        <option value="{{ $language->id }}" @selected($currentLanguage === (string) $language->id)>{{ $language->name }}</option>
                    @endforeach
                </select>
            </label>
            <label class="block font-bold text-gray-700">ระดับ *
                <select name="level" required class="mt-1 w-full rounded-xl border-2 border-gray-200 bg-white px-3 py-2 font-normal">
                    @foreach (TeacherCourse::LEVEL_LABELS as $value => $label)
                        <option value="{{ $value }}" @selected(old('level', $course->level) === $value)>{{ $label }} ({{ ucfirst($value) }})</option>
                    @endforeach
                </select>
            </label>
        </div>

        <fieldset class="grid gap-4 sm:grid-cols-2">
            <div>
                <legend class="font-bold text-gray-700">ราคา *</legend>
                <div class="mt-2 flex gap-4">
                    <label class="flex items-center gap-2"><input type="radio" name="price_type" value="free" x-model="priceType" @checked($priceType === 'free')> ฟรี</label>
                    <label class="flex items-center gap-2"><input type="radio" name="price_type" value="paid" x-model="priceType" @checked($priceType === 'paid')> เสียเงิน</label>
                </div>
                <label x-show="priceType === 'paid'" x-cloak class="mt-2 block text-sm font-bold text-gray-600">ราคา (บาท)
                    <input type="number" name="price" min="1" max="100000" step="0.01" value="{{ old('price', $course->price) }}" class="mt-1 w-full rounded-xl border-2 border-gray-200 px-3 py-2 font-normal">
                </label>
                <p x-show="priceType === 'paid'" x-cloak class="mt-1 text-xs text-gray-500">ยังไม่รับเงินจริง ผู้เรียนกดชำระแบบจำลอง แล้วคุณกดยืนยันเอง</p>
            </div>
            <div>
                <legend class="font-bold text-gray-700">วิธีรับผู้เรียน *</legend>
                @php $mode = old('enrollment_mode', $course->enrollment_mode); @endphp
                <label class="mt-2 flex items-center gap-2"><input type="radio" name="enrollment_mode" value="instant" @checked($mode === 'instant')> เข้าได้ทันที</label>
                <label class="mt-1 flex items-center gap-2"><input type="radio" name="enrollment_mode" value="approval_required" @checked($mode === 'approval_required')> ต้องให้ฉันอนุมัติก่อน</label>
            </div>
        </fieldset>

        <fieldset>
            <legend class="font-bold text-gray-700">บทเรียนในคอร์ส</legend>
            <p class="text-sm text-gray-500">เลือกจากบทเรียนของภาษาที่สอน (ลำดับตามบทเรียนหลัก) ต้องมีอย่างน้อย 1 บทก่อนเปิดรับสมัคร</p>
            <p x-show="!language" class="mt-3 rounded-xl bg-gray-50 p-3 text-sm text-gray-500">เลือกภาษาก่อน แล้วรายการบทเรียนจะแสดงที่นี่</p>
            @foreach ($languages as $language)
                <div x-show="language === '{{ $language->id }}'" x-cloak class="mt-3 max-h-96 space-y-3 overflow-y-auto rounded-xl border border-gray-200 p-3">
                    @forelse ($language->courses as $systemCourse)
                        @foreach ($systemCourse->units as $unit)
                            <div>
                                <p class="text-sm font-extrabold text-gray-500">{{ $systemCourse->title }} · {{ $unit->title }}</p>
                                @foreach ($unit->lessons as $lesson)
                                    <label class="mt-1 flex items-center gap-2 rounded-lg px-2 py-1 hover:bg-gray-50">
                                        <input type="checkbox" name="lessons[]" value="{{ $lesson->id }}"
                                            :disabled="language !== '{{ $language->id }}'"
                                            @checked(in_array($lesson->id, $checked, true))>
                                        {{ $lesson->title }}
                                    </label>
                                @endforeach
                            </div>
                        @endforeach
                    @empty
                        <p class="text-sm text-gray-400">ภาษานี้ยังไม่มีบทเรียน</p>
                    @endforelse
                </div>
            @endforeach
        </fieldset>

        <button type="submit" class="w-full rounded-xl bg-emerald-500 py-3 font-extrabold text-white hover:bg-emerald-600">
            {{ $editing ? 'บันทึกการแก้ไข' : 'สร้างคอร์ส (ฉบับร่าง)' }}
        </button>
    </form>
</div>
@endsection
