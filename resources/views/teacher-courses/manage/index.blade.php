@extends('layouts.app')

@section('title', 'คอร์สของฉัน (ผู้สอน)')

@section('content')
<div class="space-y-6">
    <div class="flex flex-wrap items-end justify-between gap-4">
        <div>
            <p class="text-sm font-bold uppercase tracking-wide text-emerald-600">Teacher · Course Management</p>
            <h1 class="mt-1 text-3xl font-extrabold text-gray-800">คอร์สของฉัน</h1>
            <p class="mt-1 text-gray-500">เปิดได้หลายคอร์ส กำหนดฟรีหรือมีราคา และเลือกว่าจะอนุมัติผู้เรียนเองหรือไม่</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('teacher-courses.index') }}" class="rounded-xl border-2 border-gray-200 bg-white px-4 py-2 font-bold text-gray-700">ดูหน้าคอร์สจากผู้สอน</a>
            <a href="{{ route('teacher.courses.create') }}" class="rounded-xl bg-emerald-500 px-4 py-2 font-bold text-white hover:bg-emerald-600">+ สร้างคอร์สใหม่</a>
        </div>
    </div>

    @include('teacher-courses.partials.flash')

    <div class="space-y-3">
        @forelse ($courses as $course)
            <div class="rounded-2xl border-2 border-gray-200 bg-white p-5">
                <div class="flex flex-wrap items-start justify-between gap-3">
                    <div class="space-y-2">
                        @include('teacher-courses.partials.course-badges', ['course' => $course])
                        <h2 class="text-xl font-extrabold text-gray-800">{{ $course->title }}</h2>
                        <p class="text-sm text-gray-500">
                            {{ \App\Models\TeacherCourse::STATUS_LABELS[$course->status] }} ·
                            {{ $course->lessons_count }} บท · {{ $course->learner_count }} ผู้เรียน
                            @if ($course->waiting_count > 0)
                                · <span class="font-bold text-amber-600">{{ $course->waiting_count }} คำขอรอดำเนินการ</span>
                            @endif
                        </p>
                    </div>
                    <div class="flex flex-wrap gap-2">
                        <a href="{{ route('teacher.courses.dashboard', $course) }}" class="rounded-lg bg-emerald-50 px-3 py-1.5 font-bold text-emerald-700">ภาพรวมการเรียน</a>
                        <a href="{{ route('teacher.courses.students', $course) }}" class="rounded-lg bg-emerald-50 px-3 py-1.5 font-bold text-emerald-700">รายชื่อนักเรียน</a>
                        <a href="{{ route('teacher.courses.enrollments', $course) }}" class="rounded-lg bg-indigo-50 px-3 py-1.5 font-bold text-indigo-700">คำขอเข้าเรียน</a>
                        <a href="{{ route('teacher.courses.edit', $course) }}" class="rounded-lg bg-blue-50 px-3 py-1.5 font-bold text-blue-700">แก้ไข</a>
                        <a href="{{ route('teacher-courses.show', $course) }}" class="rounded-lg bg-gray-100 px-3 py-1.5 font-bold text-gray-600">ดูหน้าคอร์ส</a>
                    </div>
                </div>
            </div>
        @empty
            <div class="rounded-2xl border-2 border-dashed border-gray-300 bg-white py-12 text-center text-gray-500">
                ยังไม่มีคอร์ส เริ่มสร้างคอร์สแรกได้เลย
            </div>
        @endforelse
    </div>
</div>
@endsection
