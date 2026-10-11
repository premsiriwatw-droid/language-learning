@extends('layouts.app')
@section('title', 'สมัครเป็นผู้สอน')
@section('content')
@php
    $statusLabels = ['pending' => 'รอพิจารณา', 'approved' => 'อนุมัติแล้ว', 'rejected' => 'ไม่ผ่านการพิจารณา', 'needs_more_info' => 'ต้องการข้อมูลเพิ่มเติม'];
    $selectedLanguages = old('teaching_languages', $application?->teaching_languages ?? []);
@endphp
<div class="max-w-2xl mx-auto">
    <h1 class="text-2xl font-bold mb-3">สมัครเป็นผู้สอน</h1>
    <p class="text-gray-500 mb-6">ส่งประวัติและหลักฐานให้ Admin พิจารณา ระหว่างรอยังใช้ระบบเรียนได้ตามปกติ</p>
    @if(session('status'))
        <p role="status" class="mb-4 rounded-xl bg-emerald-50 p-4 text-emerald-700">{{ session('status') }}</p>
    @endif
    @if($errors->any())
        <div role="alert" class="mb-4 rounded-xl bg-red-50 p-4 text-red-700">
            @foreach($errors->all() as $message)<p>{{ $message }}</p>@endforeach
        </div>
    @endif
    @if($application)
        <section class="mb-6 rounded-2xl border-2 border-gray-200 bg-white p-5">
            <h2 class="font-bold text-lg">สถานะ: {{ $statusLabels[$application->status] ?? $application->status }}</h2>
            @if($application->status === 'approved' && auth()->user()->isApprovedTeacher())
                <p class="mt-2 font-bold text-emerald-700">✓ Verified Instructor</p>
            @endif
            <p class="mt-3">ชื่อผู้สมัคร: {{ $application->full_name }}</p>
            <p class="mt-2">ภาษาที่สอน: {{ implode(' · ', $application->teaching_languages) }}</p>
            <p class="mt-2 whitespace-pre-line">ประวัติย่อ: {{ $application->bio }}</p>
            <p class="mt-2 whitespace-pre-line">ประสบการณ์: {{ $application->teaching_experience }}</p>
            @if($application->review_note)
                <p class="mt-3 whitespace-pre-line text-amber-800">ข้อความจากผู้ตรวจ: {{ $application->review_note }}</p>
            @endif
            <h3 class="mt-5 mb-2 font-bold">เอกสารแนบ (ส่วนตัว)</h3>
            @foreach($application->documents as $document)
                <a class="block mt-2 text-emerald-700 underline" href="{{ route('teacher.documents.download', $document) }}">ดาวน์โหลด {{ $document->original_name }}</a>
            @endforeach
            @if($application->reviews->isNotEmpty())
                <h3 class="mt-5 font-bold">ประวัติการพิจารณา</h3>
                @foreach($application->reviews as $review)
                    <div class="mt-3 border-t border-gray-200 pt-3">
                        <p class="text-sm text-gray-500">{{ $review->created_at->timezone(config('app.timezone'))->format('d/m/Y H:i') }} · {{ $statusLabels[$review->to_status] ?? $review->to_status }}</p>
                        <p class="whitespace-pre-line">{{ $review->note }}</p>
                    </div>
                @endforeach
            @endif
        </section>
    @endif
    @if($canEdit)
        <form method="POST" action="{{ route('teacher.application.store') }}" enctype="multipart/form-data" class="rounded-2xl border-2 border-gray-200 bg-white p-6 space-y-5">
            @csrf
            <div>
                <label for="full_name" class="block font-bold mb-2">ชื่อ–นามสกุล</label>
                <input id="full_name" name="full_name" value="{{ old('full_name', $application?->full_name ?? auth()->user()->name) }}" maxlength="255" required class="w-full rounded-xl border-2 border-gray-200 p-3">
            </div>
            <div>
                <label for="bio" class="block font-bold mb-2">ประวัติย่อ</label>
                <textarea id="bio" name="bio" rows="3" maxlength="3000" required class="w-full rounded-xl border-2 border-gray-200 p-3">{{ old('bio', $application?->bio) }}</textarea>
            </div>
            <div>
                <label for="teaching_experience" class="block font-bold mb-2">ประสบการณ์สอนหรือประสบการณ์เกี่ยวกับภาษา</label>
                <textarea id="teaching_experience" name="teaching_experience" rows="4" maxlength="5000" required class="w-full rounded-xl border-2 border-gray-200 p-3">{{ old('teaching_experience', $application?->teaching_experience) }}</textarea>
            </div>
            <fieldset>
                <legend class="font-bold mb-2">ภาษาที่ต้องการสอน</legend>
                @forelse($languages as $language)
                    <label class="inline-flex items-center gap-2 mr-5"><input type="checkbox" name="teaching_languages[]" value="{{ $language }}" {{ in_array($language, $selectedLanguages, true) ? 'checked' : '' }}> {{ $language }}</label>
                @empty
                    <p class="text-red-700">ยังไม่มีภาษาในระบบ กรุณาติดต่อ Admin</p>
                @endforelse
            </fieldset>
            <div>
                <label for="document_type" class="block font-bold mb-2">ประเภทเอกสารที่แนบรอบนี้</label>
                <select id="document_type" name="document_type" required class="w-full rounded-xl border-2 border-gray-200 p-3">
                    @foreach(['qualification' => 'วุฒิการศึกษา', 'certificate' => 'ใบรับรอง / ผลสอบภาษา', 'experience' => 'หลักฐานประสบการณ์', 'portfolio' => 'ผลงาน'] as $value => $label)
                        <option value="{{ $value }}" {{ old('document_type') === $value ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="documents" class="block font-bold mb-2">หลักฐานประกอบใบสมัคร</label>
                <p id="document-help" class="text-sm text-gray-500 mb-2">PDF/JPG/PNG ไฟล์ละไม่เกิน 5 MB แนบครั้งละไม่เกิน 3 ไฟล์ เอกสารเปิดได้เฉพาะคุณและ Admin ไม่จำเป็นต้องแนบบัตรประชาชน</p>
                <input id="documents" name="documents[]" type="file" multiple accept=".pdf,.jpg,.jpeg,.png" aria-describedby="document-help" {{ !$application || $application->documents->isEmpty() ? 'required' : '' }}>
                @if($application)<p class="mt-2 text-sm text-gray-500">เอกสารเดิมยังอยู่ ส่งใหม่ได้โดยไม่ต้องแนบเพิ่มหากหลักฐานครบแล้ว</p>@endif
            </div>
            <button type="submit" class="w-full rounded-xl bg-emerald-600 p-3 font-bold text-white">{{ $application ? 'ส่งใบสมัครที่แก้ไขแล้ว' : 'ส่งใบสมัครผู้สอน' }}</button>
        </form>
    @endif
    <a href="{{ route('profile') }}" class="block mt-6 font-bold text-emerald-700">← กลับโปรไฟล์</a>
</div>
@endsection
