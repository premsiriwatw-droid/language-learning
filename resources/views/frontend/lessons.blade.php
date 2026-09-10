@extends('layouts.app')

@section('title', 'เส้นทางการเรียน')

@section('content')
<!-- Progress Bar Section -->
<div class="bg-white p-6 rounded-3xl border-2 border-gray-200 mb-8 flex items-center gap-4 shadow-sm">
    <div class="w-12 h-12 rounded-full bg-emerald-100 flex items-center justify-center text-2xl">
        🏆
    </div>
    <div class="flex-1">
        <div class="flex justify-between font-bold mb-2">
            <span class="text-gray-700">ความคืบหน้าภาพรวม</span>
            <span class="text-emerald-500">{{ $progressPercent ?? 0 }}%</span>
        </div>
        <div class="h-4 w-full bg-gray-200 rounded-full overflow-hidden">
            <div class="h-full bg-emerald-500 rounded-full transition-all duration-500" style="width: {{ $progressPercent ?? 0 }}%;"></div>
        </div>
    </div>
</div>

<!-- Unit Section -->
<div class="mb-6">
    <h2 class="text-2xl font-extrabold text-gray-800 mb-1">Unit 1: พื้นฐานการทักทาย</h2>
    <p class="text-gray-500 mb-6 font-medium">เรียนรู้คำศัพท์และประโยคพื้นฐานในชีวิตประจำวัน</p>

    <div class="flex flex-col gap-4">
        @foreach($lessons as $lesson)
            @php
                // กำหนดสไตล์ตามสถานะของบทเรียน
                if($lesson['is_completed']) {
                    $cardStyle = "bg-white border-2 border-emerald-400 border-b-4";
                    $textStyle = "text-emerald-600";
                    $icon = "✅";
                    $btnText = "ทบทวน";
                    $btnStyle = "bg-white border-2 border-gray-200 text-gray-600 hover:bg-gray-50";
                } elseif($lesson['is_locked']) {
                    $cardStyle = "bg-gray-50 border-2 border-gray-200";
                    $textStyle = "text-gray-400";
                    $icon = "🔒";
                    $btnText = "ล็อกอยู่";
                    $btnStyle = "bg-gray-200 text-gray-400 cursor-not-allowed";
                } else {
                    // บทเรียนปัจจุบัน (ด่านที่ต้องเล่น)
                    $cardStyle = "bg-white border-2 border-blue-400 border-b-4 scale-[1.02] shadow-md";
                    $textStyle = "text-blue-600";
                    $icon = "⭐";
                    $btnText = "เริ่มเรียน";
                    $btnStyle = "bg-blue-500 text-white hover:bg-blue-600 hover:-translate-y-0.5 border-b-4 border-blue-600 active:border-b-0 active:translate-y-0";
                }
            @endphp

            <div class="{{ $cardStyle }} rounded-2xl p-4 flex items-center justify-between transition-all">
                <div class="flex items-center gap-4">
                    <div class="text-2xl">{{ $icon }}</div>
                    <div>
                        <h3 class="font-bold {{ $textStyle }} text-lg">{{ $lesson['title'] }}</h3>
                        @if(!$lesson['is_locked'])
                            <p class="text-sm text-gray-500">พร้อมสำหรับเก็บ XP แล้ว</p>
                        @endif
                    </div>
                </div>
                
                <a href="#" class="px-6 py-2.5 rounded-xl font-bold transition-all {{ $btnStyle }}">
                    {{ $btnText }}
                </a>
            </div>
        @endforeach
    </div>
</div>
@endsection