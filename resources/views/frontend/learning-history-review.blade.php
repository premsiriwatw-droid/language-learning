@extends('layouts.app')
@section('title', 'ทบทวนข้อผิด')
@section('content')
<div class="max-w-xl mx-auto">
    <h1 class="text-2xl font-bold mb-2">ทบทวนข้อผิด: {{ $attempt->lesson_title }}</h1>
    <p class="text-sm text-gray-500 mb-5">การฝึกนี้ไม่เปลี่ยนคะแนนเดิม และไม่ให้ XP/ดาวเพิ่ม</p>
    @if($skippedCount > 0)
        <p class="mb-4 rounded-xl bg-amber-50 p-3">ข้าม {{ $skippedCount }} ข้อที่ถูกลบ ย้ายบท หรือไม่มีคำตอบพร้อมใช้งาน</p>
    @endif
    @if(!$question)
        <div class="rounded-2xl border-2 border-emerald-200 bg-white p-6 text-center">
            <h2 class="text-xl font-bold text-emerald-600">{{ $total > 0 ? 'ทบทวนครบแล้ว 🎉' : 'ไม่มีข้อผิดที่พร้อมทบทวน' }}</h2>
            <p class="mt-3">{{ $total > 0 ? 'ผ่านครบ '.$total.' ข้อ' : 'เลือกประวัติรอบอื่นเพื่อฝึกต่อได้' }}</p>
        </div>
    @else
        <p class="mb-3 text-gray-500">ข้อ {{ $position }} / {{ $total }}</p>
        <div class="rounded-2xl border-2 border-gray-200 bg-white p-6">
            <h2 class="text-xl font-bold mb-5">{{ $question->question }}</h2>
            @if($question->exercise->type === 'listening' && $question->audio_path)
                <audio controls preload="none" class="w-full mb-5"><source src="{{ asset($question->audio_path) }}"></audio>
            @endif
            @if($question->exercise->type === 'image_choice' && $question->image_path)
                <img src="{{ asset($question->image_path) }}" alt="ภาพสำหรับคำถาม" class="w-full max-h-72 object-contain mb-5">
            @endif
            @if($errors->any())
                <p role="alert" class="mb-4 text-red-600">{{ $errors->first() }}</p>
            @endif
            @if($result)
                <p role="status" class="mb-4 font-bold {{ $result['solved'] ? 'text-emerald-600' : 'text-red-600' }}">{{ $result['solved'] ? 'ถูกต้อง! ผ่านแล้ว' : 'ยังไม่ถูก ลองอีกครั้ง' }}</p>
            @endif
            <form method="POST" action="{{ route('learning-history.review.submit', $attempt) }}">
                @csrf
                <input type="hidden" name="question_id" value="{{ $question->id }}">
                <input type="hidden" name="action" value="{{ ($result['solved'] ?? false) ? 'next' : 'answer' }}">
                <div class="space-y-3">
                    @foreach($choices as $answer)
                        @php
                            $value = in_array($question->exercise->type, ['multiple_choice', 'image_choice'], true) ? $answer->id : $answer->answer;
                            $selected = $result !== null && (string) $result['selected_answer'] === (string) $value;
                        @endphp
                        <label class="block rounded-xl border-2 p-4 {{ $selected ? (($result['solved'] ?? false) ? 'border-emerald-500 bg-emerald-50' : 'border-red-400 bg-red-50') : 'border-gray-200' }}">
                            <input type="radio" name="answer" value="{{ $value }}" required
                                {{ ($result['solved'] ?? false) ? 'disabled' : '' }}
                                {{ $selected && ($result['solved'] ?? false) ? 'checked' : '' }}>
                            {{ $answer->answer }}
                        </label>
                    @endforeach
                </div>
                <button type="submit" class="mt-5 w-full rounded-xl bg-emerald-500 p-3 font-bold text-white">{{ ($result['solved'] ?? false) ? 'ถัดไป →' : 'ตรวจคำตอบ' }}</button>
            </form>
        </div>
    @endif
    <a href="{{ route('learning-history.show', $attempt) }}" class="block mt-6 text-emerald-600 font-bold">← กลับผลรอบนี้</a>
</div>
@endsection
