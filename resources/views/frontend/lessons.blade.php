@extends('layouts.app')

@section('title', 'บทเรียน')

@section('content')
<div class="mb-6">
    <h2 class="text-2xl font-extrabold text-gray-800 mb-1">
        {{ $unit->name ?? 'บทเรียน' }}
    </h2>

    <p class="text-gray-500 mb-6 font-medium">
        เลือกบทเรียนที่ต้องการเรียน
    </p>

    <div class="flex flex-col gap-4">
        @forelse($unit->lessons as $lesson)
            <div class="bg-white border-2 border-gray-200 border-b-4 rounded-2xl p-4 flex items-center justify-between transition-all hover:border-emerald-400">
                <div class="flex items-center gap-4">
                    <div class="text-2xl">
                        📘
                    </div>

                    <div>
                        <h3 class="font-bold text-gray-800 text-lg">
                            {{ $lesson->title ?? $lesson->name ?? ('Lesson ' . $lesson->id) }}
                        </h3>

                        <p class="text-sm text-gray-500">
                            กดเพื่อดูคำศัพท์และแบบฝึกหัด
                        </p>
                    </div>
                </div>

                <a href="{{ url('/lessons/' . $lesson->id) }}"
                   class="px-6 py-2.5 rounded-xl font-bold transition-all bg-emerald-500 text-white hover:bg-emerald-600 border-b-4 border-emerald-600 active:border-b-0">
                    เริ่มเรียน
                </a>
            </div>
        @empty
            <div class="text-center text-gray-500 py-8">
                ยังไม่มีบทเรียนใน Unit นี้
            </div>
        @endforelse
    </div>
</div>
@endsection