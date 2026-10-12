@extends('layouts.app')
@section('title', 'พื้นที่ผู้สอน')
@section('content')
<div class="max-w-2xl mx-auto p-6 space-y-6">
    <header class="space-y-2">
        <h1 class="text-2xl font-bold">พื้นที่ผู้สอน</h1>
        <p>{{ auth()->user()->name }} · ผู้สอนที่ผ่านการยืนยันแล้ว ✓</p>
        <p class="text-sm text-emerald-700">ยืนยันตัวตนผู้สอนแล้ว</p>
        <p class="text-gray-600">จัดการคอร์สของคุณ ตรวจคำขอเข้าเรียน และติดตามการเรียนของนักเรียน</p>
    </header>

    <section class="rounded-2xl border border-gray-200 bg-white p-6 space-y-4" aria-labelledby="teacher-courses-heading">
        <h2 id="teacher-courses-heading" class="text-xl font-bold">คอร์สของคุณ</h2>
        <p class="text-gray-600">เลือกคอร์สเพื่อแก้ไขรายละเอียด ตรวจคำขอเข้าเรียน หรือเปิดแดชบอร์ดดูความก้าวหน้าและจุดที่นักเรียนยังทำผิดบ่อย</p>
        <div class="flex flex-wrap gap-3">
            <a class="inline-block rounded-xl bg-emerald-700 px-5 py-3 font-bold text-white" href="{{ route('teacher.courses.index') }}">จัดการคอร์สของฉัน →</a>
            <a class="inline-block rounded-xl border border-emerald-700 px-5 py-3 font-bold text-emerald-700" href="{{ route('teacher.courses.create') }}">สร้างคอร์สใหม่</a>
        </div>
        <p class="text-sm text-gray-600">หากยังไม่มีคอร์ส ให้เริ่มจากสร้างคอร์สใหม่ เมื่อมีนักเรียนเข้าเรียนแล้วจึงติดตามผลได้จากแดชบอร์ดของคอร์สนั้น</p>
    </section>

    <section class="rounded-2xl border border-gray-200 bg-white p-6 space-y-3" aria-labelledby="teacher-identity-heading">
        <h2 id="teacher-identity-heading" class="text-xl font-bold">การยืนยันผู้สอน</h2>
        <a class="block font-bold text-emerald-700" href="{{ route('teacher.application.show') }}">ดูใบสมัครและผลพิจารณา →</a>
    </section>

    <a class="inline-block text-emerald-700" href="{{ route('profile') }}">← กลับโปรไฟล์</a>
</div>
@endsection
