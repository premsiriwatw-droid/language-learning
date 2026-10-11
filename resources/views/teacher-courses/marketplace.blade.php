@extends('layouts.app')

@section('title', 'คอร์สจากผู้สอน')

@section('content')
<div class="space-y-6">
    <div class="flex flex-wrap items-end justify-between gap-4">
        <div>
            <p class="text-sm font-bold uppercase tracking-wide text-emerald-600">Teacher Courses</p>
            <h1 class="mt-1 text-3xl font-extrabold text-gray-800">คอร์สจากผู้สอน</h1>
            <p class="mt-1 text-gray-500">คอร์สที่ผู้สอนซึ่งผ่านการยืนยันตัวตนเปิดสอนเอง แยกจากบทเรียนหลักของเว็บ</p>
        </div>
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('teacher-courses.mine') }}" class="rounded-xl border-2 border-gray-200 bg-white px-4 py-2 font-bold text-gray-700 hover:border-emerald-400">คอร์สที่ฉันสมัคร</a>
            @if ($isTeacher)
                <a href="{{ route('teacher.courses.index') }}" class="rounded-xl bg-emerald-500 px-4 py-2 font-bold text-white hover:bg-emerald-600">จัดการคอร์สของฉัน (ผู้สอน)</a>
            @endif
        </div>
    </div>

    @include('teacher-courses.partials.flash')

    <form method="GET" action="{{ route('teacher-courses.index') }}" class="grid gap-3 rounded-2xl border-2 border-gray-200 bg-white p-4 sm:grid-cols-4" aria-label="ตัวกรองคอร์ส">
        <label class="text-sm font-bold text-gray-600">ภาษา
            <select name="language" class="mt-1 w-full rounded-xl border-2 border-gray-200 bg-white px-3 py-2">
                <option value="">ทุกภาษา</option>
                @foreach ($languages as $language)
                    <option value="{{ $language->id }}" @selected((string) ($filters['language'] ?? '') === (string) $language->id)>{{ $language->name }}</option>
                @endforeach
            </select>
        </label>
        <label class="text-sm font-bold text-gray-600">ระดับ
            <select name="level" class="mt-1 w-full rounded-xl border-2 border-gray-200 bg-white px-3 py-2">
                <option value="">ทุกระดับ</option>
                @foreach (\App\Models\TeacherCourse::LEVEL_LABELS as $value => $label)
                    <option value="{{ $value }}" @selected(($filters['level'] ?? '') === $value)>{{ $label }}</option>
                @endforeach
            </select>
        </label>
        <label class="text-sm font-bold text-gray-600">ราคา
            <select name="price" class="mt-1 w-full rounded-xl border-2 border-gray-200 bg-white px-3 py-2">
                <option value="">ฟรีและเสียเงิน</option>
                <option value="free" @selected(($filters['price'] ?? '') === 'free')>ฟรี</option>
                <option value="paid" @selected(($filters['price'] ?? '') === 'paid')>เสียเงิน</option>
            </select>
        </label>
        <div class="flex items-end gap-2">
            <button type="submit" class="flex-1 rounded-xl bg-emerald-500 px-4 py-2.5 font-bold text-white hover:bg-emerald-600">กรอง</button>
            <a href="{{ route('teacher-courses.index') }}" class="rounded-xl bg-gray-100 px-4 py-2.5 font-bold text-gray-600">ล้าง</a>
        </div>
    </form>

    <div class="grid gap-4 sm:grid-cols-2">
        @forelse ($courses as $course)
            <a href="{{ route('teacher-courses.show', $course) }}" class="flex flex-col gap-3 rounded-2xl border-2 border-b-4 border-gray-200 bg-white p-5 transition hover:-translate-y-0.5 hover:border-emerald-400">
                @include('teacher-courses.partials.course-badges', ['course' => $course])
                <h2 class="text-xl font-extrabold text-gray-800">{{ $course->title }}</h2>
                <p class="line-clamp-2 text-sm text-gray-500">{{ $course->description }}</p>
                <div class="mt-auto flex flex-wrap items-center justify-between gap-2 text-sm">
                    <span class="font-bold text-gray-700">
                        {{ $course->teacher->teacherApplication?->full_name ?? $course->teacher->name }}
                        <span class="ml-1 rounded-full bg-emerald-100 px-2 py-0.5 text-xs text-emerald-700" title="ผู้สอนที่ผ่านการยืนยัน">✓ Verified</span>
                    </span>
                    <span class="text-gray-500">{{ $course->lessons_count }} บท · {{ $course->learner_count }} ผู้เรียน</span>
                </div>
                @if (isset($myStatuses[$course->id]))
                    <span class="self-start rounded-lg bg-indigo-50 px-2.5 py-1 text-xs font-bold text-indigo-700">
                        {{ \App\Models\CourseEnrollment::STATUS_LABELS[$myStatuses[$course->id]] ?? $myStatuses[$course->id] }}
                    </span>
                @endif
            </a>
        @empty
            <div class="col-span-full rounded-2xl border-2 border-dashed border-gray-300 bg-white py-12 text-center text-gray-500">
                ยังไม่มีคอร์สจากผู้สอนที่ตรงกับตัวกรองนี้
            </div>
        @endforelse
    </div>

    {{ $courses->links() }}
</div>
@endsection
