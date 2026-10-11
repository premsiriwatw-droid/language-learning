@extends('layouts.app')
@section('title', 'Placement Test')
@section('content')
<div class="max-w-2xl mx-auto">
    <h1 class="text-2xl font-bold mb-2">Placement Test</h1>
    <p class="text-gray-600 mb-5">แบบทดสอบ {{ $questionCount }} ข้อจากบทเรียนที่มีอยู่ เพื่อแนะนำ Learner Proficiency Level เบื้องต้น การทดสอบนี้ไม่เพิ่ม XP หรือ Stars</p>

    @if($errors->any())
        <p role="alert" class="mb-4 rounded-xl bg-red-50 p-3 text-red-700">{{ $errors->first() }}</p>
    @endif

    @if($languages->isNotEmpty())
        <form method="GET" action="{{ route('placement.show') }}" class="mb-6 flex items-end gap-3">
            <div>
                <label for="language_id" class="block font-bold mb-1">ภาษาที่ต้องการทดสอบ</label>
                <select id="language_id" name="language_id" class="rounded-lg border p-2">
                    @foreach($languages as $availableLanguage)
                        <option value="{{ $availableLanguage->id }}" @selected($language?->id === $availableLanguage->id)>{{ $availableLanguage->name }}</option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="rounded-lg bg-gray-100 p-2 font-bold">เลือกภาษา</button>
        </form>
    @endif

    @if(empty($questions))
        <p role="status" class="rounded-xl bg-amber-50 p-4">ยังมีคำถามพร้อมใช้งานไม่ครบ {{ $questionCount }} ข้อ ระดับของคุณยังรอการทดสอบ กรุณาลองอีกครั้งเมื่อมีเนื้อหาพร้อม</p>
    @else
        <form method="POST" action="{{ route('placement.submit') }}" class="space-y-5">
            @csrf
            <input type="hidden" name="token" value="{{ $token }}">
            @foreach($questions as $index => $question)
                <fieldset class="rounded-xl border bg-white p-5">
                    <legend class="px-2 font-bold">{{ $index + 1 }}. {{ $question['question'] }}</legend>
                    <div class="space-y-3">
                        @foreach($question['answers'] as $answer)
                            <label class="block rounded-lg border p-3">
                                <input type="radio" name="answers[{{ $question['id'] }}]" value="{{ $answer['id'] }}" required @checked((string) old('answers.'.$question['id']) === (string) $answer['id'])>
                                {{ $answer['answer'] }}
                            </label>
                        @endforeach
                    </div>
                </fieldset>
            @endforeach
            <button type="submit" class="w-full rounded-xl bg-emerald-600 p-3 font-bold text-white">ส่งคำตอบและดูระดับที่แนะนำ</button>
        </form>
    @endif
    <a href="{{ route('profile') }}" class="block mt-6 text-emerald-700 font-bold">กลับโปรไฟล์</a>
</div>
@endsection
