@extends('layouts.app')
@section('title', 'สถานะผู้สอน')
@section('content')
<div class="max-w-2xl mx-auto p-6 space-y-4">
    <h1 class="text-2xl font-bold">ผู้สอนที่ผ่านการยืนยันแล้ว</h1>
    <p>{{ auth()->user()->name }} · ยืนยันตัวตนผู้สอนแล้ว ✓</p>
    <p>คุณได้รับสิทธิ์ผู้สอนแล้ว ส่วนการจัดการคอร์สและแดชบอร์ดการสอนจะเชื่อมต่อในขั้นถัดไป</p>
    <a class="block text-emerald-700" href="{{ route('teacher.application.show') }}">ดูใบสมัครและผลพิจารณา</a>
    <a class="block text-emerald-700" href="{{ route('profile') }}">กลับโปรไฟล์</a>
</div>
@endsection
