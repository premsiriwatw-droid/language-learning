@extends('layouts.app')

@section('title', 'เลือก Unit')

@section('content')
<div class="mb-6">
    <h2 class="mb-1 text-2xl font-extrabold text-gray-800">
        {{ $course->title ?? $course->name ?? 'Course' }}
    </h2>
    <p class="mb-6 font-medium text-gray-500">เลือก Unit ที่ต้องการเรียน</p>

    <div class="flex flex-col gap-4">
        @forelse ($course->units as $unit)
            @php
                $state = $unitStates[$unit->id];
            @endphp
            <div class="flex flex-wrap items-center justify-between gap-4 rounded-2xl border-2 border-b-4 border-gray-200 bg-white p-4">
                <div>
                    <h3 class="text-lg font-bold text-gray-800">
                        {{ $state['completed'] ? '✅' : ($state['available'] ? '📘' : '🔒') }}
                        {{ $unit->title ?? $unit->name ?? ('Unit ' . $unit->id) }}
                    </h3>
                    <p class="mt-1 text-sm text-gray-500">
                        เรียนจบ {{ $state['completed_count'] }} / {{ $state['lesson_count'] }} บท
                    </p>
                    @if (!$state['available'])
                        <p class="mt-1 text-sm text-gray-500">{{ $state['reason'] }}</p>
                    @endif
                </div>

                @if ($state['available'])
                    <a href="{{ route('units.lessons', $unit) }}"
                       class="rounded-xl border-b-4 border-emerald-600 bg-emerald-500 px-6 py-2.5 font-bold text-white hover:bg-emerald-600">
                        {{ $state['completed'] ? 'ทบทวน' : 'ดูบทเรียน' }}
                    </a>
                @else
                    <button type="button" disabled
                            class="cursor-not-allowed rounded-xl bg-gray-100 px-6 py-2.5 font-bold text-gray-400">
                        ล็อก
                    </button>
                @endif
            </div>
        @empty
            <p class="py-8 text-center text-gray-500">ยังไม่มี Unit ใน Course นี้</p>
        @endforelse
    </div>
</div>
@endsection
