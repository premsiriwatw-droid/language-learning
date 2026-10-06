@extends('layouts.app')

@section('title', 'บทเรียน')

@section('content')

<div class="mb-6">
    <h2 class="text-2xl font-extrabold text-gray-800 mb-1">
        {{ $unit->title ?? $unit->name ?? 'บทเรียน' }}
    </h2>

    <p class="text-gray-500 mb-6 font-medium">
        เรียนตามลำดับเพื่อเปิดบทถัดไป
    </p>

    <div class="flex flex-col gap-4">
        @forelse($unit->lessons as $lesson)
            @php
                $state = $lessonStates[$lesson->id] ?? [
                    'available' => false,
                    'completed' => false,
                    'reason' => 'ยังไม่เปิดให้เรียน',
                ];
            @endphp

            <div class="bg-white border-2 border-b-4 rounded-2xl p-4 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 {{ $state['available'] ? 'border-emerald-200' : 'border-gray-200' }}">
                <div class="flex items-center gap-4 min-w-0">
                    <div class="text-2xl shrink-0" aria-hidden="true">
                        @if($state['completed'])
                            ✅
                        @elseif($state['available'])
                            📘
                        @else
                            🔒
                        @endif
                    </div>

                    <div class="min-w-0">
                        <h3 class="font-bold text-gray-800 text-lg break-words">
                            {{ $lesson->title ?? $lesson->name ?? ('Lesson ' . $lesson->id) }}
                        </h3>

                        @if($state['completed'])
                            <p class="text-sm text-emerald-700">
                                เรียนจบแล้ว กลับมาทบทวนได้
                            </p>
                        @elseif($state['available'])
                            <p class="text-sm text-gray-500">
                                พร้อมเรียนคำศัพท์และแบบฝึกหัด
                            </p>
                        @else
                            <p class="text-sm text-gray-500">
                                {{ $state['reason'] }}
                            </p>
                        @endif
                    </div>
                </div>

                @if($state['available'])
                    <a
                        href="{{ route('lessons.learn', [
                            'lesson' => $lesson->id
                        ]) }}"
                        class="shrink-0 px-6 py-2.5 rounded-xl text-center font-bold bg-emerald-500 text-white hover:bg-emerald-600 border-b-4 border-emerald-600"
                    >
                        {{ $state['completed'] ? 'ทบทวน' : 'เริ่มเรียน' }}
                    </a>
                @else
                    <button
                        type="button"
                        disabled
                        class="shrink-0 px-6 py-2.5 rounded-xl text-center font-bold bg-gray-100 text-gray-500 border-b-4 border-gray-200 cursor-not-allowed"
                    >
                        ยังไม่เปิด
                    </button>
                @endif
            </div>
        @empty
            <div class="text-center text-gray-500 py-8">
                ยังไม่มีบทเรียนใน Unit นี้
            </div>
        @endforelse
    </div>
</div>

@endsection