@extends('layouts.app')

@section('title', 'รายละเอียดนักเรียน')

@section('content')
<div class="space-y-6">
    <a
        href="{{ route('teacher.courses.students', $course) }}"
        class="font-bold text-emerald-600"
    >
        ← กลับรายชื่อนักเรียน
    </a>

    <div class="rounded-xl border border-gray-200 bg-white p-6">
        <h1 class="text-2xl font-bold">
            รายละเอียดนักเรียน
        </h1>

        <p class="mt-4">
            นักเรียน: {{ $student->name }}
        </p>

        <p class="mt-2 text-gray-600">
            คอร์ส: {{ $course->title }}
        </p>
    </div>
    @include('teacher-courses.partials.learning-panel')
</div>
@endsection
