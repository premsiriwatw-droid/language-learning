@extends('layouts.app')
@section('title', 'ผลการเรียนแต่ละรอบ')
@section('content')
<div class="max-w-2xl mx-auto">
    <h1 class="text-2xl font-bold">{{ $attempt->lesson_title }}</h1>
    <p class="text-gray-500 mt-2">{{ $attempt->language_name }} · {{ $attempt->completed_at->timezone(config('app.timezone'))->format('d/m/Y H:i') }}</p>
    <div class="my-6 rounded-2xl border-2 border-gray-200 bg-white p-5">
        <p class="text-2xl font-bold text-emerald-600">{{ $attempt->score_percent !== null ? $attempt->score_percent.'%' : 'เรียนคำศัพท์ครบ' }}</p>
        <p class="mt-3">ถูกครั้งแรก {{ $attempt->correct_count }} ข้อ · ผิดครั้งแรก {{ $attempt->wrong_count }} ข้อ</p>
        <p class="mt-2">เรียนศัพท์ {{ $attempt->vocabulary_count }} คำ · ตอบผิดทั้งหมด {{ $attempt->wrong_attempts }} ครั้ง</p>
        <p class="mt-2">เวลา {{ intdiv($attempt->elapsed_seconds, 60) }} นาที {{ $attempt->elapsed_seconds % 60 }} วินาที</p>
    </div>
    @if($wrongResults->isNotEmpty())
        <a href="{{ route('learning-history.review', $attempt) }}" class="block rounded-xl bg-emerald-500 p-3 text-white text-center font-bold">ฝึกข้อที่ตอบผิดครั้งแรก ({{ $wrongResults->count() }} ข้อ) →</a>
        <p class="my-3 text-sm text-gray-500">ฝึกเพิ่มได้โดยไม่เปลี่ยนคะแนนเดิมและไม่รับ XP/ดาวซ้ำ ใช้คำถามเวอร์ชันปัจจุบัน ข้อที่ลบแล้วจะข้าม</p>
    @endif
    <h2 class="mt-6 mb-3 text-xl font-bold">ผลรายข้อ</h2>
    <div class="space-y-3">
        @forelse($attempt->results as $index => $result)
            <article class="rounded-xl border-2 border-gray-200 bg-white p-4">
                <h3 class="font-bold">{{ $index + 1 }}. {{ $result['question'] }}</h3>
                <p class="mt-2 {{ $result['first_correct'] ? 'text-emerald-600' : 'text-red-600' }}">{{ $result['first_correct'] ? 'ตอบถูกครั้งแรก' : 'ตอบผิดครั้งแรก' }}</p>
                <p class="mt-2 text-sm">คำตอบครั้งแรก: {{ $result['first_answer'] ?? 'ไม่มีข้อมูลคำตอบครั้งแรกของรอบเดิม' }}</p>
                <p class="mt-1 text-sm">ตอบ {{ $result['attempts'] }} ครั้ง · ผิด {{ $result['wrong_attempts'] }} ครั้ง</p>
                @if(!empty($result['vocabulary']))
                    <p class="mt-2 text-gray-500 text-sm">คำศัพท์ที่เกี่ยวข้อง: {{ collect($result['vocabulary'])->pluck('word')->implode(' · ') }}</p>
                @endif
            </article>
        @empty
            <p class="text-gray-500">รอบนี้เป็นการเรียนคำศัพท์ ไม่มีข้อสอบ</p>
        @endforelse
    </div>
    <a href="{{ route('learning-history.index') }}" class="block mt-6 font-bold text-emerald-600">← กลับประวัติการเรียน</a>
</div>
@endsection
