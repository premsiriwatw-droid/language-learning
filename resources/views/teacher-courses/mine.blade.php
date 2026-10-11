@extends('layouts.app')

@section('title', 'คอร์สที่ฉันสมัคร')

@section('content')
<div class="space-y-6">
    <div>
        <a href="{{ route('teacher-courses.index') }}" class="text-sm font-bold text-emerald-600">← คอร์สจากผู้สอน</a>
        <h1 class="mt-2 text-3xl font-extrabold text-gray-800">คอร์สที่ฉันสมัคร</h1>
    </div>

    @include('teacher-courses.partials.flash')

    <div class="space-y-3">
        @forelse ($enrollments as $enrollment)
            <a href="{{ route('teacher-courses.show', $enrollment->course) }}" class="flex flex-wrap items-center justify-between gap-3 rounded-2xl border-2 border-gray-200 bg-white p-4 hover:border-emerald-400">
                <span>
                    <span class="block text-lg font-extrabold text-gray-800">{{ $enrollment->course->title }}</span>
                    <span class="text-sm text-gray-500">{{ $enrollment->course->language?->name ?? 'ไม่ระบุภาษา' }} · ผู้สอน {{ $enrollment->course->teacher->name }}</span>
                </span>
                <span class="rounded-lg bg-indigo-50 px-3 py-1 text-sm font-bold text-indigo-700">{{ $enrollment->statusLabel() }}</span>
            </a>
        @empty
            <div class="rounded-2xl border-2 border-dashed border-gray-300 bg-white py-12 text-center text-gray-500">
                ยังไม่ได้สมัครคอร์สจากผู้สอน
            </div>
        @endforelse
    </div>
</div>
@endsection
