@extends('layouts.app')

@section('title', 'ตรวจคอร์สจากผู้สอน')

@section('content')
<div class="space-y-6">
    <div>
        <p class="text-sm font-bold uppercase tracking-wide text-indigo-600">Admin · Course Review</p>
        <h1 class="mt-1 text-3xl font-extrabold text-gray-800">ตรวจคอร์สจากผู้สอน</h1>
        <p class="mt-1 text-gray-500">คอร์สจะเผยแพร่ให้ผู้เรียนเห็นหลังจากแอดมินอนุมัติเท่านั้น</p>
    </div>

    @include('teacher-courses.partials.flash')

    <nav class="flex gap-2" aria-label="ตัวกรองคอร์ส">
        <a href="{{ route('admin.teacher-courses.index') }}" @if ($tab === 'pending') aria-current="page" @endif
            class="rounded-xl px-4 py-2 font-bold {{ $tab === 'pending' ? 'bg-indigo-500 text-white' : 'bg-white text-gray-600 border-2 border-gray-200' }}">
            รอตรวจ ({{ $pendingCount }})
        </a>
        <a href="{{ route('admin.teacher-courses.index', ['tab' => 'all']) }}" @if ($tab === 'all') aria-current="page" @endif
            class="rounded-xl px-4 py-2 font-bold {{ $tab === 'all' ? 'bg-indigo-500 text-white' : 'bg-white text-gray-600 border-2 border-gray-200' }}">
            ทั้งหมดที่เคยส่ง
        </a>
    </nav>

    <div class="space-y-3">
        @forelse ($courses as $course)
            <a href="{{ route('admin.teacher-courses.show', $course) }}" class="block rounded-2xl border-2 border-gray-200 bg-white p-5 hover:border-indigo-300">
                <div class="flex flex-wrap items-start justify-between gap-3">
                    <div class="min-w-0 space-y-1">
                        <h2 class="text-lg font-extrabold text-gray-800">{{ $course->title }}</h2>
                        <p class="text-sm text-gray-500">
                            {{ $course->teacher->teacherApplication?->full_name ?? $course->teacher->name }} ·
                            {{ $course->language?->name ?? '-' }} · {{ $course->levelLabel() }} · {{ $course->priceLabel() }} ·
                            {{ $course->teacher_lessons_count }} บทของผู้สอน + {{ $course->lessons_count }} แบบฝึกจากระบบ
                        </p>
                    </div>
                    <div class="text-right text-sm">
                        <span class="rounded-full px-2.5 py-1 font-bold {{ $course->isPendingReview() ? 'bg-amber-100 text-amber-800' : ($course->isPublished() ? 'bg-emerald-100 text-emerald-800' : 'bg-gray-100 text-gray-600') }}">{{ $course->statusLabel() }}</span>
                        @if ($course->isPendingReview() && $course->submitted_at)
                            <p class="mt-2 text-gray-500">ส่งเมื่อ {{ $course->submitted_at->format('d/m/Y H:i') }}</p>
                            @if ($course->published_at)<p class="text-xs text-amber-700">แก้ไขหลังเผยแพร่</p>@endif
                        @endif
                    </div>
                </div>
            </a>
        @empty
            <div class="rounded-2xl border-2 border-dashed border-gray-300 bg-white py-12 text-center text-gray-500">
                {{ $tab === 'pending' ? 'ไม่มีคอร์สรอตรวจ' : 'ยังไม่มีคอร์สที่ส่งตรวจ' }}
            </div>
        @endforelse
    </div>

    {{ $courses->links() }}
</div>
@endsection
