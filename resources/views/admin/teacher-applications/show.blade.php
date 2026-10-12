@extends('layouts.app')
@section('title', 'พิจารณาใบสมัครผู้สอน')
@section('content')
@php($labels = ['pending' => 'รอพิจารณา', 'approved' => 'อนุมัติแล้ว', 'rejected' => 'ปฏิเสธ', 'needs_more_info' => 'ขอข้อมูลเพิ่มเติม'])
<div class="max-w-3xl mx-auto p-6 space-y-6">
    <h1 class="text-2xl font-bold">ใบสมัคร: {{ $application->full_name }}</h1>
    @if(session('status'))<p role="status" class="text-emerald-700">{{ session('status') }}</p>@endif
    @if($errors->any())
        <div role="alert" class="text-red-700">@foreach($errors->all() as $error)<p>{{ $error }}</p>@endforeach</div>
    @endif
    <section class="bg-white border rounded p-5 space-y-3">
        <p>สถานะ: {{ $labels[$application->status] ?? $application->status }}</p>
        <p>บัญชี: {{ $application->user->email }}</p>
        <p>ภาษาที่สอน: {{ implode(', ', $application->teaching_languages ?? []) }}</p>
        <h2 class="font-bold">แนะนำตัว</h2><p class="whitespace-pre-wrap">{{ $application->bio }}</p>
        <h2 class="font-bold">ประสบการณ์การสอน</h2><p class="whitespace-pre-wrap">{{ $application->teaching_experience }}</p>
        <h2 class="font-bold">เอกสารประกอบ</h2>
        @forelse($application->documents as $document)
            <a class="block text-emerald-700" href="{{ route('teacher.documents.download', $document) }}">ดาวน์โหลด {{ $document->original_name }}</a>
        @empty <p>ไม่มีเอกสาร</p> @endforelse
    </section>
    @if($application->status === 'pending')
        <form method="POST" action="{{ route('admin.teacher-applications.review', $application) }}" class="bg-white border rounded p-5 space-y-4">
            @csrf
            <input type="hidden" name="review_version" value="{{ $application->reviews->count() }}">
            <input type="hidden" name="submitted_at" value="{{ $application->submitted_at->toISOString() }}">
            <label for="note" class="block">เหตุผล / ข้อมูลที่ต้องการเพิ่มเติม (จำเป็นเมื่อปฏิเสธหรือขอข้อมูลเพิ่มเติม)</label>
            <textarea id="note" name="note" maxlength="3000" rows="4" class="border rounded w-full p-3">{{ old('note') }}</textarea>
            <div class="flex flex-wrap gap-3">
                <button type="submit" name="decision" value="approved" class="bg-emerald-600 text-white rounded px-4 py-2">อนุมัติเป็นผู้สอน</button>
                <button type="submit" name="decision" value="needs_more_info" class="border rounded px-4 py-2">ขอข้อมูลเพิ่มเติม</button>
                <button type="submit" name="decision" value="rejected" class="border text-red-700 rounded px-4 py-2">ปฏิเสธ</button>
            </div>
        </form>
    @else
        <p>ใบสมัครนี้พิจารณาแล้ว หากผู้สมัครส่งใหม่ จะกลับมารอพิจารณาอีกครั้ง</p>
    @endif
    <section class="bg-white border rounded p-5">
        <h2 class="font-bold mb-3">ประวัติการพิจารณา</h2>
        @forelse($application->reviews as $review)
            <div class="border-b py-3">
                <p>{{ $review->created_at->format('d/m/Y H:i') }} · {{ $review->reviewer?->name ?? 'ผู้สมัคร' }}</p>
                <p>{{ $labels[$review->from_status] ?? $review->from_status }} → {{ $labels[$review->to_status] ?? $review->to_status }}</p>
                <p class="whitespace-pre-wrap">{{ $review->note }}</p>
            </div>
        @empty <p>ยังไม่มีผลพิจารณา</p> @endforelse
    </section>
    <a class="block text-emerald-700" href="{{ route('admin.teacher-applications.index') }}">← กลับรายการใบสมัคร</a>
</div>
@endsection
