@extends('layouts.app')

@section('title', $lesson->title ?? 'เรียนคำศัพท์')

@section('content')

<div class="max-w-xl mx-auto">

    {{-- ========================= --}}
    {{-- Progress --}}
    {{-- ========================= --}}
    <div class="mb-8">
        <div class="flex justify-between text-sm text-gray-500 mb-2">
            <span>{{ $lesson->title ?? 'Lesson' }}</span>
            <span>{{ $step }} / {{ $total }}</span>
        </div>

        <div class="w-full bg-gray-200 rounded-full h-3">
            <div
                class="bg-emerald-500 h-3 rounded-full transition-all"
                style="width: {{ ($step / $total) * 100 }}%"
            >
            </div>
        </div>
    </div>


    {{-- ========================= --}}
    {{-- Vocabulary Step --}}
    {{-- ========================= --}}
    @if($current['type'] === 'vocabulary')

        @php
            $vocabulary = $current['vocabulary'];
        @endphp

        <div class="bg-white border-2 border-gray-200 border-b-4 rounded-3xl p-8 text-center">

            <p class="text-gray-400 text-sm font-bold mb-4">
                คำศัพท์ใหม่
            </p>

            <div class="text-6xl font-extrabold text-emerald-500 mb-4">
                {{ $vocabulary->word }}
            </div>

            @if($vocabulary->pinyin)
                <div class="text-xl text-gray-500 mb-3">
                    {{ $vocabulary->pinyin }}
                </div>
            @endif

            <div class="text-2xl font-bold text-gray-800">
                {{ $vocabulary->meaning }}
            </div>

            @if($vocabulary->example_sentence)
                <div class="mt-8 pt-6 border-t border-gray-200">

                    <p class="text-gray-400 text-sm font-bold mb-3">
                        ตัวอย่างประโยค
                    </p>

                    <div class="text-xl font-bold text-gray-800">
                        {{ $vocabulary->example_sentence }}
                    </div>

                    @if($vocabulary->example_pinyin)
                        <div class="text-gray-500 mt-2">
                            {{ $vocabulary->example_pinyin }}
                        </div>
                    @endif

                    @if($vocabulary->example_meaning)
                        <div class="text-gray-600 mt-2">
                            {{ $vocabulary->example_meaning }}
                        </div>
                    @endif

                </div>
            @endif

        </div>


        {{-- ========================= --}}
        {{-- Vocabulary Navigation --}}
        {{-- ========================= --}}
        <div class="mt-6 flex gap-3">

            @if($step > 1)
                <a
                    href="{{ route('lessons.learn.step', [
                        'lesson' => $lesson->id,
                        'step' => $step - 1
                    ]) }}"
                    class="w-1/3 text-center py-3 rounded-xl border-2 border-gray-300 font-bold text-gray-600 hover:bg-gray-100"
                >
                    ← ย้อนกลับ
                </a>
            @endif

            <a
                href="{{ route('lessons.learn.step', [
                    'lesson' => $lesson->id,
                    'step' => $step + 1
                ]) }}"
                class="flex-1 text-center py-3 rounded-xl bg-emerald-500 text-white font-bold border-b-4 border-emerald-600 hover:bg-emerald-600 active:border-b-0"
            >
                ถัดไป →
            </a>

        </div>


    {{-- ========================= --}}
    {{-- Review Step --}}
    {{-- ========================= --}}
    @elseif($current['type'] === 'review')

        @php
            $question = $current['question'];
            $exerciseType = $current['exercise_type'] ?? null;

            $selectedAnswer = session('selected_answer');
            $reviewResult = session('review_result');
        @endphp

        <div class="bg-white border-2 border-gray-200 border-b-4 rounded-3xl p-8">

            {{-- ========================= --}}
            {{-- Review Header --}}
            {{-- ========================= --}}
            <div class="text-center mb-6">

                <div class="text-5xl mb-3">
                    @if($exerciseType === 'fill_blank')
                        🧩
                    @elseif($exerciseType === 'listening')
                        🔊
                    @elseif($exerciseType === 'image_choice')
                        🖼️
                    @else
                        🎯
                    @endif
                </div>

                <p class="text-emerald-500 font-bold mb-2">
                    @if($exerciseType === 'fill_blank')
                        Word Bank
                    @elseif($exerciseType === 'listening')
                        Listening
                    @elseif($exerciseType === 'image_choice')
                        Image Choice
                    @else
                        Mini Review
                    @endif
                </p>

                <h2 class="text-2xl font-extrabold text-gray-800">
                    {{ $question->question }}
                </h2>

            </div>


            {{-- ========================= --}}
            {{-- Listening Audio --}}
            {{-- ========================= --}}
            @if($exerciseType === 'listening')

                <div class="mb-6 text-center">

                    @if($question->audio_path)

                        <div class="bg-emerald-50 border-2 border-emerald-100 rounded-2xl p-6">

                            <p class="text-sm text-gray-500 font-bold mb-4">
                                กดฟังเสียงแล้วเลือกคำตอบ
                            </p>

                            <audio
                                controls
                                class="w-full"
                            >
                                <source
                                    src="{{ asset($question->audio_path) }}"
                                    type="audio/mpeg"
                                >

                                เบราว์เซอร์ของคุณไม่รองรับการเล่นเสียง
                            </audio>

                        </div>

                    @else

                        <div class="bg-gray-50 border-2 border-gray-200 rounded-2xl p-6 text-gray-500">
                            🔊 ยังไม่มีไฟล์เสียงสำหรับข้อนี้
                        </div>

                    @endif

                </div>

            @endif


            {{-- ========================= --}}
            {{-- Image Choice Image --}}
            {{-- ========================= --}}
            @if($exerciseType === 'image_choice')

                <div class="mb-6">

                    @if($question->image_path)

                        <div class="bg-gray-50 border-2 border-gray-200 rounded-2xl p-4">

                            <img
                                src="{{ asset($question->image_path) }}"
                                alt="รูปภาพสำหรับคำถาม"
                                class="w-full max-h-72 object-contain rounded-xl"
                            >

                        </div>

                    @else

                        <div class="bg-gray-50 border-2 border-gray-200 rounded-2xl p-6 text-center text-gray-500">
                            🖼️ ยังไม่มีรูปภาพสำหรับข้อนี้
                        </div>

                    @endif

                </div>

            @endif


            {{-- ========================= --}}
            {{-- Result Message --}}
            {{-- ========================= --}}
            @if($reviewResult === 'wrong')

                <div class="mb-5 bg-red-50 border-2 border-red-200 text-red-600 rounded-xl p-4 text-center font-bold">
                    ❌ ยังไม่ถูก ลองอีกครั้ง
                </div>

            @endif


            @if($reviewResult === 'correct')

                <div class="mb-5 bg-emerald-50 border-2 border-emerald-200 text-emerald-600 rounded-xl p-4 text-center font-bold">
                    ✅ ถูกต้อง!
                </div>

            @endif


            {{-- ========================= --}}
            {{-- Answer Form --}}
            {{-- ========================= --}}
            <form
                method="POST"
                action="{{ route('lessons.learn.submit', [
                    'lesson' => $lesson->id,
                    'step' => $step
                ]) }}"
            >

                @csrf

                <div class="
                    {{ in_array(
                        $exerciseType,
                        ['fill_blank', 'listening', 'image_choice'],
                        true
                    )
                        ? 'grid grid-cols-2 gap-3'
                        : 'grid grid-cols-1 gap-3' }}
                ">

                    @foreach($question->answers as $answer)

                        @php
                            /*
                             * multiple_choice / image_choice
                             * ใช้ Answer ID
                             *
                             * fill_blank / listening
                             * ใช้ข้อความคำตอบ
                             */
                            $answerValue = in_array(
                                $exerciseType,
                                ['fill_blank', 'listening'],
                                true
                            )
                                ? $answer->answer
                                : $answer->id;

                            $isSelected =
                                (string) $selectedAnswer ===
                                (string) $answerValue;

                            $isCorrectAnswer =
                                (bool) $answer->is_correct;

                            $answerClasses =
                                'border-gray-200 text-gray-700';

                            if (
                                $reviewResult === 'wrong' &&
                                $isSelected
                            ) {
                                $answerClasses =
                                    'border-red-400 bg-red-50 text-red-600';
                            }

                            if (
                                $reviewResult === 'correct' &&
                                $isCorrectAnswer
                            ) {
                                $answerClasses =
                                    'border-emerald-500 bg-emerald-50 text-emerald-600';
                            }
                        @endphp


                        <label
                            class="{{ $reviewResult === 'correct'
                                ? ''
                                : 'cursor-pointer' }}"
                        >

                            <input
                                type="radio"
                                name="answer"
                                value="{{ $answerValue }}"
                                class="hidden peer"
                                {{ $isSelected && $reviewResult !== 'wrong' ? 'checked' : '' }}
                                {{ $reviewResult === 'correct' ? 'disabled' : '' }}
                                required
                            >


                            <div
                                class="
                                    border-2
                                    border-b-4
                                    rounded-xl
                                    p-4
                                    text-center
                                    font-bold
                                    {{ $answerClasses }}
                                    {{ $reviewResult === 'correct'
                                        ? ''
                                        : 'hover:border-emerald-400 peer-checked:border-emerald-500 peer-checked:bg-emerald-50' }}
                                    transition-all
                                "
                            >

                                {{ $answer->answer }}

                                @if(
                                    $reviewResult === 'correct' &&
                                    $isCorrectAnswer
                                )
                                    <span class="ml-2">
                                        ✓
                                    </span>
                                @endif

                                @if(
                                    $reviewResult === 'wrong' &&
                                    $isSelected
                                )
                                    <span class="ml-2">
                                        ✕
                                    </span>
                                @endif

                            </div>

                        </label>

                    @endforeach

                </div>


                {{-- ========================= --}}
                {{-- Review Navigation --}}
                {{-- ========================= --}}
                @if($reviewResult === 'correct')

                    <a
                        href="{{ route('lessons.learn.step', [
                            'lesson' => $lesson->id,
                            'step' => $step + 1
                        ]) }}"
                        class="mt-6 block w-full text-center py-3 rounded-xl bg-emerald-500 text-white font-bold border-b-4 border-emerald-600 hover:bg-emerald-600 active:border-b-0"
                    >
                        {{ $step >= $total ? 'จบบทเรียน 🎉' : 'ถัดไป →' }}
                    </a>

                @else

                    <button
                        type="submit"
                        class="mt-6 w-full py-3 rounded-xl bg-emerald-500 text-white font-bold border-b-4 border-emerald-600 hover:bg-emerald-600 active:border-b-0"
                    >
                        ตรวจคำตอบ
                    </button>

                @endif

            </form>

        </div>

    @endif

</div>

@endsection