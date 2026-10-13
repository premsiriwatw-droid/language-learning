@extends('layouts.app')

@section('title', 'ภาพรวมการเรียน')

@section('content')
<div class="space-y-6">
    <div>
        <a href="{{ route('teacher.courses.index') }}" class="text-sm font-bold text-emerald-600">← คอร์สของฉัน</a>
        <p class="mt-4 text-sm font-bold text-emerald-600">Teacher · Learning Dashboard</p>
        <h1 class="mt-1 text-3xl font-extrabold text-gray-800">ภาพรวมการเรียน</h1>
        <h2 class="mt-2 text-xl font-bold">{{ $course->title }}</h2>
        <div class="mt-2">@include('teacher-courses.partials.course-badges', ['course' => $course])</div>
        <p class="mt-2 text-sm text-gray-500">{{ $summary['lessons_count'] }} บทเรียน · ข้อมูล ณ {{ $summary['as_of']->format('d/m/Y H:i') }} ({{ config('app.timezone') }})</p>
    </div>

    @if ($summary['lessons_count'] === 0)
        <p class="rounded-xl border border-amber-200 bg-amber-50 p-4 text-amber-800">คอร์สนี้ยังไม่มีบทเรียน จึงยังคำนวณความคืบหน้าและคะแนนไม่ได้</p>
    @elseif ($summary['students_count'] === 0)
        <p class="rounded-xl border border-gray-200 bg-white p-4 text-gray-600">ยังไม่มีนักเรียนที่เริ่มเรียนในคอร์สนี้ เมื่อมีผู้เรียน ข้อมูลจะแสดงที่นี่</p>
    @endif

    <dl class="grid gap-4 sm:grid-cols-2">
        <div class="rounded-2xl border border-gray-200 bg-white p-5">
            <dt class="font-bold text-gray-600">จำนวนนักเรียน</dt>
            <dd class="mt-2 text-3xl font-extrabold text-gray-800">{{ number_format($summary['students_count']) }} <span class="text-base font-normal">คน</span></dd>
            <dd class="mt-2 text-sm text-gray-500">รวมผู้เรียนที่กำลังเรียนและเรียนจบแล้ว ไม่รวมคำขอที่ยังรออนุมัติหรือรอชำระเงิน</dd>
        </div>
        <div class="rounded-2xl border border-gray-200 bg-white p-5">
            <dt class="font-bold text-gray-600">ผู้เรียนที่มี activity สัปดาห์นี้</dt>
            <dd class="mt-2 text-3xl font-extrabold text-gray-800">{{ number_format($summary['active_this_week']) }} <span class="text-base font-normal">คน</span></dd>
            <dd class="mt-2 text-sm text-gray-500">เข้าเรียนหรือเรียนจบบทในคอร์ส ตั้งแต่ {{ $summary['week_started_at']->format('d/m/Y') }} (จันทร์) ถึงวันนี้ นับแต่ละคนครั้งเดียว</dd>
        </div>
        <div class="rounded-2xl border border-gray-200 bg-white p-5">
            <dt class="font-bold text-gray-600">ความคืบหน้าเฉลี่ย</dt>
            <dd class="mt-2 text-3xl font-extrabold text-gray-800">{{ $summary['average_progress'] === null ? '—' : number_format($summary['average_progress'], 1).'%' }}</dd>
            <dd class="mt-2 text-sm text-gray-500">เฉลี่ยสัดส่วนบทเรียนที่เรียนจบต่อบททั้งหมดในคอร์ส รวมผู้เรียนที่ยังไม่เริ่มเรียนด้วย</dd>
        </div>
        <div class="rounded-2xl border border-gray-200 bg-white p-5">
            <dt class="font-bold text-gray-600">คะแนนเฉลี่ยจากการเรียนครั้งแรก</dt>
            <dd class="mt-2 text-3xl font-extrabold text-gray-800">{{ $summary['average_first_attempt_score'] === null ? '—' : number_format($summary['average_first_attempt_score'], 1).'%' }}</dd>
            <dd class="mt-2 text-sm text-gray-500">รอบที่จบครั้งแรกของผู้เรียนแต่ละคนในแต่ละบทที่มีคำถาม รวม {{ number_format($summary['scored_first_attempts']) }} รอบ ไม่ใช้คะแนนรอบที่เรียนซ้ำ</dd>
            @if ($summary['average_first_attempt_score'] === null)
                <dd class="mt-2 text-sm text-gray-500">ยังไม่มีคะแนนจากรอบแรกที่ใช้คำนวณได้</dd>
            @endif
        </div>
    </dl>

    <p class="text-sm leading-relaxed text-gray-500">บทเรียนของคอร์สอ้างอิงบทเรียนเดียวกับระบบหลัก ข้อมูลจึงรวมการเรียนบทเหล่านี้ก่อนเข้าคอร์สด้วย หากบทเดียวกันอยู่ในหลายคอร์ส ความคืบหน้าและประวัติของบทนั้นจะใช้ร่วมกัน</p>

    <div class="flex flex-wrap gap-2">
        <a href="{{ route('teacher.courses.students', $course) }}" class="inline-block rounded-lg bg-emerald-500 px-4 py-2 font-bold text-white hover:bg-emerald-600">รายชื่อนักเรียน</a>
        <a href="{{ route('teacher.courses.weak-topics', $course) }}" class="inline-block rounded-lg bg-emerald-500 px-4 py-2 font-bold text-white hover:bg-emerald-600">จุดที่ควรทบทวน</a>
        <a href="{{ route('teacher.courses.enrollments', $course) }}" class="inline-block rounded-lg bg-emerald-500 px-4 py-2 font-bold text-white hover:bg-emerald-600">จัดการคำขอเข้าเรียน</a>
    </div>
</div>
@endsection
