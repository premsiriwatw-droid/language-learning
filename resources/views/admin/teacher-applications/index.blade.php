@extends('layouts.app')
@section('title', 'ตรวจใบสมัครผู้สอน')
@section('content')
@php($labels = ['all' => 'ทั้งหมด', 'pending' => 'รอพิจารณา', 'approved' => 'อนุมัติแล้ว', 'rejected' => 'ปฏิเสธ', 'needs_more_info' => 'ขอข้อมูลเพิ่มเติม'])
<div class="max-w-4xl mx-auto p-6">
    <h1 class="text-2xl font-bold mb-6">ตรวจใบสมัครผู้สอน</h1>
    <form method="GET" class="mb-6">
        <label for="status">สถานะ</label>
        <select id="status" name="status" class="border rounded p-2">
            @foreach($labels as $value => $label)
                <option value="{{ $value }}" @selected($status === $value)>{{ $label }}</option>
            @endforeach
        </select>
        <button class="border rounded p-2" type="submit">แสดงรายการ</button>
    </form>
    @forelse($applications as $application)
        <div class="bg-white border rounded p-4 mb-3">
            <a class="text-emerald-700 font-bold" href="{{ route('admin.teacher-applications.show', $application) }}">{{ $application->full_name }}</a>
            <p>{{ $application->user->email }} · {{ $labels[$application->status] ?? $application->status }}</p>
            <p>{{ implode(', ', $application->teaching_languages ?? []) }} · เอกสาร {{ $application->documents_count }} ไฟล์</p>
            <p>ส่งเมื่อ {{ $application->submitted_at->format('d/m/Y H:i') }}</p>
        </div>
    @empty
        <p>ไม่มีใบสมัครในสถานะนี้</p>
    @endforelse
    {{ $applications->links() }}
    <a class="block mt-6 text-emerald-700" href="{{ route('admin.dashboard') }}">← กลับแดชบอร์ดแอดมิน</a>
</div>
@endsection
