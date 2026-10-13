@extends('layouts.app')

@php use App\Models\TeacherLesson; @endphp

@section('title', 'ตรวจคอร์ส: '.$course->title)

@section('content')
<div class="space-y-6">
    <a href="{{ route('admin.teacher-courses.index') }}" class="text-sm font-bold text-indigo-600">← รายการคอร์สรอตรวจ</a>

    @include('teacher-courses.partials.flash')

    <section class="space-y-4 rounded-2xl border-2 border-gray-200 bg-white p-6">
        <div class="flex flex-wrap items-center gap-2">
            @include('teacher-courses.partials.course-badges', ['course' => $course])
            <span class="rounded-full px-2.5 py-1 text-xs font-bold {{ $course->isPendingReview() ? 'bg-amber-100 text-amber-800' : 'bg-gray-100 text-gray-600' }}">{{ $course->statusLabel() }}</span>
        </div>
        <h1 class="text-3xl font-extrabold text-gray-800">{{ $course->title }}</h1>
        <p class="text-gray-700">
            ผู้สอน: <strong>{{ $course->teacher->teacherApplication?->full_name ?? $course->teacher->name }}</strong>
            <span class="text-gray-500">({{ $course->teacher->email }})</span>
            @if ($teacherVerified)
                <span class="ml-1 rounded-full bg-emerald-100 px-2.5 py-0.5 text-xs font-bold text-emerald-700">✓ Verified</span>
            @else
                <span class="ml-1 rounded-full bg-red-100 px-2.5 py-0.5 text-xs font-bold text-red-700">ผู้สอนยังไม่ผ่านการยืนยัน (คอร์สจะไม่แสดงแม้อนุมัติ)</span>
            @endif
        </p>
        <p class="whitespace-pre-line text-gray-600">{{ $course->description }}</p>
        <p class="text-sm text-gray-500">
            ส่งตรวจ: {{ $course->submitted_at?->format('d/m/Y H:i') ?? '-' }}
            @if ($course->published_at) · เคยเผยแพร่เมื่อ {{ $course->published_at->format('d/m/Y') }} · ผู้เรียนปัจจุบัน {{ $course->learner_count }} คน @endif
            @if ($course->reviewed_at) · ตรวจล่าสุด {{ $course->reviewed_at->format('d/m/Y H:i') }} โดย {{ $course->reviewer?->name ?? '-' }} @endif
        </p>
        @if ($course->review_note)
            <p class="rounded-xl bg-gray-50 p-3 text-sm text-gray-700">หมายเหตุล่าสุด: {{ $course->review_note }}</p>
        @endif
    </section>

    <section class="space-y-3 rounded-2xl border-2 border-gray-200 bg-white p-6" aria-labelledby="review-lessons-heading">
        <h2 id="review-lessons-heading" class="text-xl font-extrabold text-gray-800">บทเรียนของผู้สอน ({{ $course->teacherLessons->count() }})</h2>
        @forelse ($course->teacherLessons as $lesson)
            <details class="rounded-xl border border-gray-200 p-4" @if ($loop->first) open @endif>
                <summary class="cursor-pointer font-bold text-gray-800">{{ $loop->iteration }}. {{ $lesson->title }}</summary>
                <div class="mt-3 space-y-3">
                    @if ($lesson->videoEmbedUrl())
                        <div class="aspect-video overflow-hidden rounded-xl bg-black">
                            <iframe src="{{ $lesson->videoEmbedUrl() }}" title="วิดีโอ: {{ $lesson->title }}" class="h-full w-full" loading="lazy"
                                allow="encrypted-media; picture-in-picture; fullscreen" allowfullscreen referrerpolicy="strict-origin-when-cross-origin"></iframe>
                        </div>
                        <p class="text-xs text-gray-500 break-all">ลิงก์: {{ $lesson->video_url }}</p>
                    @elseif ($lesson->hasVideoFile())
                        <video controls preload="metadata" class="max-h-80 w-full rounded-xl bg-black" src="{{ route('teacher-courses.lessons.video', [$course, $lesson]) }}"></video>
                    @endif
                    @if ($lesson->body)
                        <div class="whitespace-pre-line rounded-xl bg-gray-50 p-3 text-gray-700">{{ $lesson->body }}</div>
                    @endif
                    @if ($lesson->hasDocument())
                        <a href="{{ route('teacher-courses.lessons.document', [$course, $lesson]) }}" class="inline-block rounded-lg bg-emerald-50 px-3 py-2 text-sm font-bold text-emerald-700">
                            📄 ดาวน์โหลด {{ $lesson->document_name }} ({{ TeacherLesson::sizeLabel($lesson->document_size) }})
                        </a>
                    @endif
                </div>
            </details>
        @empty
            <p class="text-gray-400">ไม่มีบทเรียนที่ผู้สอนเขียนเอง</p>
        @endforelse

        @if ($course->lessons->isNotEmpty())
            <h3 class="pt-2 font-extrabold text-gray-700">แบบฝึกจากบทเรียนหลักของระบบ ({{ $course->lessons->count() }})</h3>
            <ul class="list-inside list-disc text-sm text-gray-600">
                @foreach ($course->lessons as $lesson)
                    <li>{{ $lesson->title }} <span class="text-gray-400">· {{ $lesson->unit?->title }}</span></li>
                @endforeach
            </ul>
        @endif
    </section>

    @if ($course->isPendingReview())
        <form method="POST" action="{{ route('admin.teacher-courses.review', $course) }}" class="space-y-4 rounded-2xl border-2 border-indigo-200 bg-white p-6"
            x-data="{ decision: '{{ old('decision', 'approve') === 'reject' ? 'reject' : 'approve' }}' }">
            @csrf
            {{-- The exact version on screen; a stale page cannot approve newer content. --}}
            <input type="hidden" name="revision" value="{{ $course->content_revision }}">
            <h2 class="text-xl font-extrabold text-gray-800">ผลการตรวจ</h2>
            <div class="flex flex-wrap gap-4">
                <label class="flex items-center gap-2 font-bold text-emerald-700"><input type="radio" name="decision" value="approve" x-model="decision" @checked(old('decision', 'approve') === 'approve')> อนุมัติ เผยแพร่ได้</label>
                <label class="flex items-center gap-2 font-bold text-red-700"><input type="radio" name="decision" value="reject" x-model="decision" @checked(old('decision') === 'reject')> ไม่อนุมัติ ส่งกลับให้แก้ไข</label>
            </div>
            <label class="block font-bold text-gray-700">
                <span x-text="decision === 'reject' ? 'เหตุผล / สิ่งที่ต้องแก้ *' : 'หมายเหตุถึงผู้สอน (ไม่บังคับ)'">หมายเหตุถึงผู้สอน</span>
                <textarea name="review_note" rows="3" maxlength="1000" :required="decision === 'reject'"
                    class="mt-1 w-full rounded-xl border-2 border-gray-200 px-3 py-2 font-normal">{{ old('review_note') }}</textarea>
            </label>
            <button type="submit" class="w-full rounded-xl bg-indigo-500 py-3 font-extrabold text-white"
                :class="decision === 'reject' ? '!bg-red-500 hover:!bg-red-600' : '!bg-emerald-500 hover:!bg-emerald-600'">
                <span x-text="decision === 'reject' ? 'ส่งกลับให้ผู้สอนแก้ไข' : 'อนุมัติและเผยแพร่'">บันทึกผลการตรวจ</span>
            </button>
        </form>
    @else
        <p class="rounded-2xl border-2 border-dashed border-gray-300 bg-white p-4 text-center text-gray-500">คอร์สนี้ไม่ได้อยู่ระหว่างรอตรวจ</p>
    @endif
</div>
@endsection
