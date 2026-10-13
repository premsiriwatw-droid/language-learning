@extends('layouts.app')

@section('title', 'งานและข้อความจากครู')

@section('content')
<div class="space-y-6">
    <a href="{{ route('teacher-courses.show', $course) }}" class="font-bold text-emerald-600">← กลับหน้าคอร์ส</a>
    <h1 class="text-2xl font-bold">งานและข้อความจากครู</h1>
    <p>{{ $course->title }} · {{ $student->name }}</p>
    @include('teacher-courses.partials.learning-panel')
</div>
@endsection
