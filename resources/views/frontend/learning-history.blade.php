@extends('layouts.app')
@section('title', 'ประวัติการเรียน')
@section('content')
<div class="max-w-2xl mx-auto">
    <h1 class="text-2xl font-bold mb-2">ประวัติการเรียน</h1>
    <p class="text-gray-500 mb-6">คะแนนคิดจากคำตอบครั้งแรกของแต่ละข้อ แยกตามรอบที่เรียนจบ</p>
    <div class="space-y-4">
        @forelse($attempts as $attempt)
            <a href="{{ route('learning-history.show', $attempt) }}" class="block rounded-2xl border-2 border-gray-200 bg-white p-5 hover:border-emerald-500">
                <h2 class="font-bold text-lg">{{ $attempt->lesson_title }}</h2>
                <p class="text-sm text-gray-500">{{ $attempt->language_name }} · {{ $attempt->completed_at->timezone(config('app.timezone'))->format('d/m/Y H:i') }}</p>
                <p class="mt-3">คะแนน {{ $attempt->score_percent !== null ? $attempt->score_percent.'%' : 'ไม่มีข้อสอบ' }} · ถูก {{ $attempt->correct_count }} / {{ $attempt->question_count }} ข้อ</p>
                <p class="mt-1 text-sm text-gray-500">เวลา {{ intdiv($attempt->elapsed_seconds, 60) }} นาที {{ $attempt->elapsed_seconds % 60 }} วินาที</p>
                <p class="mt-3 font-bold text-emerald-600">ดูรายละเอียด / ทบทวนข้อผิด →</p>
            </a>
        @empty
            <p class="rounded-2xl bg-white p-6 text-gray-500">ยังไม่มีประวัติ เริ่มบันทึกเมื่อเรียนจบบทขณะเข้าสู่ระบบ</p>
        @endforelse
    </div>
    <div class="mt-6">{{ $attempts->links() }}</div>
    <a href="{{ route('profile') }}" class="block mt-6 text-emerald-600 font-bold">← กลับโปรไฟล์</a>
</div>
@endsection
