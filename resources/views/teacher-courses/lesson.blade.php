@extends('layouts.app')

@php use App\Models\TeacherLesson; @endphp

@section('title', $lesson->title)

@section('content')
<div class="space-y-6">
    <div class="flex flex-wrap items-center justify-between gap-2">
        <a href="{{ route('teacher-courses.show', $course) }}" class="text-sm font-bold text-emerald-600">← {{ $course->title }}</a>
        @if ($isOwner)
            <a href="{{ route('teacher.courses.teacher-lessons.edit', [$course, $lesson]) }}" class="rounded-lg bg-blue-50 px-3 py-1.5 text-sm font-bold text-blue-700">แก้ไขบทเรียนนี้</a>
        @endif
    </div>

    <article class="space-y-5 rounded-2xl border-2 border-gray-200 bg-white p-6">
        <header>
            <p class="text-sm font-bold text-emerald-600">บทที่ {{ $number }} จาก {{ $total }}</p>
            <h1 class="mt-1 text-3xl font-extrabold text-gray-800">{{ $lesson->title }}</h1>
        </header>

        @if ($lesson->videoEmbedUrl())
            <div class="aspect-video overflow-hidden rounded-xl bg-black">
                <iframe src="{{ $lesson->videoEmbedUrl() }}" title="วิดีโอ: {{ $lesson->title }}" class="h-full w-full"
                    allow="accelerometer; encrypted-media; gyroscope; picture-in-picture; fullscreen" allowfullscreen
                    referrerpolicy="strict-origin-when-cross-origin" loading="lazy"></iframe>
            </div>
        @elseif ($lesson->hasVideoFile())
            <video controls preload="metadata" controlslist="nodownload" class="w-full rounded-xl bg-black"
                src="{{ route('teacher-courses.lessons.video', [$course, $lesson]) }}">
                เบราว์เซอร์นี้เล่นวิดีโอไม่ได้
            </video>
        @endif

        @if ($lesson->body)
            <div class="whitespace-pre-line text-lg leading-relaxed text-gray-700">{{ $lesson->body }}</div>
        @endif

        @if ($lesson->hasDocument())
            <a href="{{ route('teacher-courses.lessons.document', [$course, $lesson]) }}"
                class="flex items-center gap-3 rounded-xl border-2 border-emerald-200 bg-emerald-50 p-4 hover:bg-emerald-100">
                <span class="text-2xl" aria-hidden="true">📄</span>
                <span class="min-w-0">
                    <span class="block truncate font-bold text-gray-800">{{ $lesson->document_name }}</span>
                    <span class="text-sm text-gray-500">ดาวน์โหลดเอกสารประกอบ · {{ TeacherLesson::sizeLabel($lesson->document_size) }}</span>
                </span>
            </a>
        @endif
    </article>

    <nav class="flex justify-between gap-3" aria-label="บทเรียนก่อนหน้าและถัดไป">
        @if ($previous)
            <a href="{{ route('teacher-courses.lessons.show', [$course, $previous]) }}" class="rounded-xl border-2 border-gray-200 bg-white px-4 py-2 font-bold text-gray-700">← {{ $previous->title }}</a>
        @else
            <span></span>
        @endif
        @if ($next)
            <a href="{{ route('teacher-courses.lessons.show', [$course, $next]) }}" class="rounded-xl bg-emerald-500 px-4 py-2 font-bold text-white hover:bg-emerald-600">{{ $next->title }} →</a>
        @endif
    </nav>
</div>
@endsection
