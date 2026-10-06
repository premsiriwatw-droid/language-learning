@extends('layouts.app')

@section('title', 'สรุปบทเรียน')

@section('content')

<div class="max-w-xl mx-auto">
    <div class="bg-white border-2 border-gray-200 border-b-4 rounded-3xl p-6 sm:p-8">

        <div class="text-center mb-8">
            <div class="text-6xl mb-4" aria-hidden="true">🎉</div>

            <h1 class="text-3xl font-extrabold text-emerald-600">
                เรียนครบแล้ว!
            </h1>

            <p class="mt-3 text-lg font-bold text-gray-700">
                {{ $lesson->title }}
            </p>
        </div>

        <div class="bg-emerald-50 border-2 border-emerald-100 rounded-2xl p-6 text-center mb-6">
            <p class="text-sm font-bold text-gray-500 mb-2">
                คะแนนจากคำตอบครั้งแรก
            </p>

            @if($summary['score_percent'] !== null)
                <div class="text-5xl font-extrabold text-emerald-600">
                    {{ $summary['score_percent'] }}%
                </div>

                <p class="mt-3 text-gray-600">
                    ตอบถูกครั้งแรก
                    {{ $summary['correct_count'] }}
                    จาก {{ $summary['question_count'] }} ข้อ
                </p>
            @else
                <div class="text-xl font-bold text-emerald-600">
                    เรียนคำศัพท์ครบแล้ว
                </div>

                <p class="mt-3 text-gray-600">
                    บทนี้ไม่มีข้อสอบสำหรับคิดคะแนน
                </p>
            @endif
        </div>

        @if($summary['progress_saved'] ?? false)
            <div class="mb-6 rounded-2xl border-2 border-amber-200 bg-amber-50 p-5 text-center">
                <h2 class="font-extrabold text-amber-800">
                    รางวัลประจำบท
                </h2>

                <div class="mt-4 grid grid-cols-2 gap-4">
                    <div>
                        <p class="text-sm text-amber-800">XP ที่บันทึก</p>
                        <p class="mt-2 text-3xl font-extrabold text-amber-700">
                            {{ $summary['saved_xp'] }} XP
                        </p>
                    </div>

                    <div>
                        <p class="text-sm text-amber-800">ดาวที่บันทึก</p>

                        <div
                            class="mt-2 text-3xl"
                            role="img"
                            aria-label="{{ $summary['saved_stars'] }} จาก 3 ดาว"
                        >
                            @for($star = 1; $star <= 3; $star++)
                                <span
                                    aria-hidden="true"
                                    class="{{ $star <= $summary['saved_stars'] ? 'text-amber-500' : 'text-gray-300' }}"
                                >★</span>
                            @endfor
                        </div>

                        <p class="mt-1 text-sm font-bold text-amber-800">
                            {{ $summary['saved_stars'] }} / 3 ดาว
                        </p>
                    </div>
                </div>

                <p class="mt-4 text-sm text-amber-800">
                    บันทึกความคืบหน้าแล้ว
                    รางวัลเก็บจากการจบบทครั้งแรก
                    การเรียนซ้ำจะไม่เพิ่ม XP หรือเปลี่ยนดาว
                </p>
            </div>
        @else
            <div class="mb-6 rounded-2xl border-2 border-gray-200 bg-gray-50 p-5 text-center">
                @auth
                    <p class="font-bold text-gray-700">
                        ยังไม่ได้บันทึกรางวัลของรอบนี้
                    </p>

                    <p class="mt-2 text-sm text-gray-600">
                        ผลการเรียนรอบนี้แสดงอยู่ด้านล่าง
                        หากต้องการบันทึกรางวัล ให้เริ่มเรียนใหม่
                        หากยังเกิดปัญหา กรุณาแจ้งผู้ดูแล
                    </p>
                @else
                    <p class="font-bold text-gray-700">
                        เข้าสู่ระบบเพื่อเก็บความคืบหน้าและรางวัล
                    </p>

                    <p class="mt-2 text-sm text-gray-600">
                        หลังเข้าสู่ระบบ ให้เริ่มเรียนบทนี้ใหม่
                        เพื่อบันทึกผลเข้าบัญชีของคุณ
                    </p>
                @endauth
            </div>
        @endif

        <div class="grid grid-cols-2 gap-4">
            <div class="rounded-2xl border-2 border-gray-200 p-4 text-center">
                <p class="text-sm text-gray-500 mb-2">คำศัพท์ที่เรียน</p>
                <p class="text-2xl font-extrabold text-gray-800">
                    {{ $summary['vocabulary_count'] }} คำ
                </p>
            </div>

            <div class="rounded-2xl border-2 border-gray-200 p-4 text-center">
                <p class="text-sm text-gray-500 mb-2">คำถามที่ทำครบ</p>
                <p class="text-2xl font-extrabold text-gray-800">
                    {{ $summary['question_count'] }} ข้อ
                </p>
            </div>

            <div class="rounded-2xl border-2 border-emerald-200 bg-emerald-50 p-4 text-center">
                <p class="text-sm text-emerald-700 mb-2">ตอบถูกครั้งแรก</p>
                <p class="text-2xl font-extrabold text-emerald-600">
                    {{ $summary['correct_count'] }} ข้อ
                </p>
            </div>

            <div class="rounded-2xl border-2 border-red-200 bg-red-50 p-4 text-center">
                <p class="text-sm text-red-700 mb-2">ตอบผิดครั้งแรก</p>
                <p class="text-2xl font-extrabold text-red-600">
                    {{ $summary['wrong_count'] }} ข้อ
                </p>
            </div>
        </div>

        <div class="mt-6 rounded-2xl bg-gray-50 p-5">
            <div class="flex justify-between gap-4">
                <span class="text-gray-500">เวลาที่ใช้</span>
                <span class="font-bold text-gray-800">
                    {{ $summary['elapsed_display'] }}
                </span>
            </div>

            <div class="mt-3 flex justify-between gap-4">
                <span class="text-gray-500">จำนวนครั้งที่ตอบผิดทั้งหมด</span>
                <span class="font-bold text-gray-800">
                    {{ $summary['wrong_attempts'] }} ครั้ง
                </span>
            </div>
        </div>

        @if($summary['question_count'] > 0)
            <p class="mt-4 text-sm text-gray-500 text-center">
                การลองใหม่จนตอบถูกช่วยให้เรียนครบ
                แต่คะแนนยังคิดจากคำตอบครั้งแรกของแต่ละข้อ
            </p>
        @endif

        <div class="mt-8 flex flex-col gap-3">
            @if($nextLesson)
                <a
                    href="{{ route('lessons.learn', [
                        'lesson' => $nextLesson->id
                    ]) }}"
                    class="block w-full rounded-xl bg-emerald-500 border-b-4 border-emerald-600 py-3 text-center font-bold text-white hover:bg-emerald-600"
                >
                    ไปบทถัดไป →
                </a>
            @endif

            <a
                href="{{ route('lessons.learn', [
                    'lesson' => $lesson->id
                ]) }}"
                class="block w-full rounded-xl border-2 border-emerald-500 py-3 text-center font-bold text-emerald-600 hover:bg-emerald-50"
            >
                เรียนใหม่ ↻
            </a>

            <a
                href="{{ route('units.lessons', [
                    'unit' => $lesson->unit_id
                ]) }}"
                class="block py-2 text-center font-bold text-gray-500 hover:text-gray-700"
            >
                กลับหน้าบทเรียน
            </a>
        </div>

    </div>
</div>

@endsection