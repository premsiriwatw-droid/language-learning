@extends('layouts.app')

@section('title', $course->title)

@php
    use App\Models\CourseEnrollment;
    $teacherName = $course->teacher->teacherApplication?->full_name ?? $course->teacher->name;
@endphp

@section('content')
<div class="space-y-6">
    <a href="{{ route('teacher-courses.index') }}" class="text-sm font-bold text-emerald-600">← คอร์สจากผู้สอนทั้งหมด</a>

    @include('teacher-courses.partials.flash')

    <section class="space-y-4 rounded-2xl border-2 border-gray-200 bg-white p-6">
        @include('teacher-courses.partials.course-badges', ['course' => $course])
        <h1 class="text-3xl font-extrabold text-gray-800">{{ $course->title }}</h1>

        <div class="flex flex-wrap items-center gap-2 text-gray-700">
            <span class="font-bold">ผู้สอน: {{ $teacherName }}</span>
            @if ($teacherVerified)
                <span class="rounded-full bg-emerald-100 px-2.5 py-0.5 text-xs font-bold text-emerald-700">✓ Verified Instructor</span>
            @endif
        </div>

        <p class="whitespace-pre-line text-gray-600">{{ $course->description }}</p>

        <dl class="grid grid-cols-3 gap-3 text-center">
            <div class="rounded-xl bg-gray-50 p-3"><dt class="text-xs text-gray-500">ราคา</dt><dd class="text-lg font-extrabold">{{ $course->priceLabel() }}</dd></div>
            <div class="rounded-xl bg-gray-50 p-3"><dt class="text-xs text-gray-500">บทเรียน</dt><dd class="text-lg font-extrabold">{{ $course->lessons->count() + $course->teacherLessons->count() }} บท</dd></div>
            <div class="rounded-xl bg-gray-50 p-3"><dt class="text-xs text-gray-500">ผู้เรียน</dt><dd class="text-lg font-extrabold">{{ $course->learner_count }} คน</dd></div>
        </dl>

        @unless ($course->isFree())
            <p class="rounded-xl bg-amber-50 p-3 text-sm text-amber-800">
                คอร์สเสียเงิน: ระบบยังไม่รับชำระเงินจริง ใช้ปุ่ม "ชำระเงิน (จำลอง)" แล้วรอผู้สอนยืนยัน
            </p>
        @endunless

        {{-- Enrollment panel --}}
        <div class="rounded-xl border-2 border-emerald-100 bg-emerald-50/40 p-4">
            @if ($canReview)
                <p class="font-bold text-gray-700">มุมมองแอดมิน · สถานะ: {{ $course->statusLabel() }}</p>
                <a href="{{ route('admin.teacher-courses.show', $course) }}" class="mt-3 inline-block rounded-xl bg-indigo-500 px-4 py-2 font-bold text-white">{{ $course->isPendingReview() ? 'ตรวจคอร์สนี้' : 'ดูผลการตรวจ' }}</a>
            @elseif ($isOwner)
                <p class="font-bold text-gray-700">นี่คือคอร์สของคุณ ({{ $course->statusLabel() }})</p>
                <div class="mt-3 flex flex-wrap gap-2">
                    <a href="{{ route('teacher.courses.edit', $course) }}" class="rounded-xl bg-emerald-500 px-4 py-2 font-bold text-white">แก้ไขคอร์ส</a>
                    <a href="{{ route('teacher.courses.enrollments', $course) }}" class="rounded-xl border-2 border-emerald-500 px-4 py-2 font-bold text-emerald-700">คำขอเข้าเรียน</a>
                </div>
            @elseif (! $enrollment || $enrollment->status === CourseEnrollment::REJECTED)
                @if ($enrollment)
                    <p class="mb-3 text-sm text-red-700">คำขอครั้งก่อนไม่ผ่านการอนุมัติ{{ $enrollment->teacher_note ? ': '.$enrollment->teacher_note : '' }}</p>
                @endif
                @if ($course->isPublished())
                    <form method="POST" action="{{ route('teacher-courses.enroll', $course) }}">
                        @csrf
                        <button type="submit" class="w-full rounded-xl bg-emerald-500 py-3 font-extrabold text-white hover:bg-emerald-600">
                            @if ($course->enrollment_mode === \App\Models\TeacherCourse::INSTANT)
                                {{ $course->isFree() ? 'เข้าร่วมคอร์สฟรี' : 'สมัครคอร์ส ('.$course->priceLabel().')' }}
                            @else
                                ขอเข้าเรียน (รอผู้สอนอนุมัติ)
                            @endif
                        </button>
                    </form>
                @else
                    <p class="font-bold text-gray-600">{{ $course->isPendingReview() ? 'คอร์สนี้กำลังปรับปรุงเนื้อหา ยังไม่รับผู้เรียนใหม่' : 'คอร์สนี้ปิดรับผู้เรียนใหม่แล้ว' }}</p>
                @endif
            @else
                <p class="font-bold text-gray-800">สถานะของคุณ: {{ $enrollment->statusLabel() }}</p>
                @if ($enrollment->payment_status !== CourseEnrollment::PAYMENT_NOT_REQUIRED)
                    <p class="mt-1 text-sm text-gray-600">การชำระเงิน: {{ $enrollment->paymentLabel() }} · ยอดของคุณ {{ $enrollment->amountLabel() }}</p>
                @endif
                @if ($enrollment->teacher_note)
                    <p class="mt-1 text-sm text-gray-600">ข้อความจากผู้สอน: {{ $enrollment->teacher_note }}</p>
                @endif

                <div class="mt-3 flex flex-wrap gap-2">
                    @if (in_array($enrollment->status, [CourseEnrollment::PENDING, CourseEnrollment::APPROVED], true) && $enrollment->payment_status === CourseEnrollment::PAYMENT_UNPAID)
                        <form method="POST" action="{{ route('teacher-courses.enroll.pay', $course) }}">
                            @csrf
                            <button type="submit" class="rounded-xl bg-amber-500 px-4 py-2 font-bold text-white">ชำระเงิน (จำลอง) {{ $enrollment->amountLabel() }}</button>
                        </form>
                    @endif
                    @if (in_array($enrollment->status, [CourseEnrollment::PENDING, CourseEnrollment::APPROVED], true) && $enrollment->payment_status !== CourseEnrollment::PAYMENT_CONFIRMED)
                        <form method="POST" action="{{ route('teacher-courses.enroll.cancel', $course) }}">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="rounded-xl bg-gray-100 px-4 py-2 font-bold text-gray-600">ยกเลิกคำขอ</button>
                        </form>
                    @endif
                </div>
            @endif
        </div>
    </section>

    @if ($enrollment?->canStudy() && ! $isOwner)
        <a href="{{ route('teacher-courses.learning-tools', $course) }}"
            class="inline-block rounded bg-emerald-600 px-4 py-2 font-bold text-white">
            งาน คำแนะนำ และข้อความจากครู
        </a>
    @endif

    @if ($course->teacherLessons->isNotEmpty())
        <section class="rounded-2xl border-2 border-gray-200 bg-white p-6" aria-labelledby="teacher-lessons-heading">
            <h2 id="teacher-lessons-heading" class="text-xl font-extrabold text-gray-800">บทเรียนจากผู้สอน</h2>
            @unless ($canOpenMaterials)
                <p class="mt-1 text-sm text-gray-500">เข้าร่วมคอร์สเพื่อเปิดเนื้อหา เอกสาร และวิดีโอ</p>
            @endunless
            <ol class="mt-4 space-y-2">
                @foreach ($course->teacherLessons as $teacherLesson)
                    <li class="flex items-center justify-between gap-3 rounded-xl border border-gray-100 p-3">
                        <span class="min-w-0">
                            <span class="mr-2 font-bold text-gray-400">{{ $loop->iteration }}.</span>
                            <span class="font-bold text-gray-700">{{ $teacherLesson->title }}</span>
                            <span class="mt-0.5 flex gap-2 text-xs text-gray-400">
                                @if ($teacherLesson->hasVideo()) <span>🎬 วิดีโอ</span> @endif
                                @if ($teacherLesson->hasDocument()) <span>📄 เอกสาร</span> @endif
                                @if ($teacherLesson->body) <span>📝 เนื้อหา</span> @endif
                            </span>
                        </span>
                        @if ($canOpenMaterials)
                            <a href="{{ route('teacher-courses.lessons.show', [$course, $teacherLesson]) }}" class="shrink-0 rounded-lg bg-emerald-500 px-3 py-1.5 text-sm font-bold text-white">เปิดบทเรียน</a>
                        @else
                            <span class="shrink-0 text-xs text-gray-400">🔒</span>
                        @endif
                    </li>
                @endforeach
            </ol>
        </section>
    @endif

    @if ($course->lessons->isNotEmpty() || $course->teacherLessons->isEmpty())
    <section class="rounded-2xl border-2 border-gray-200 bg-white p-6">
        <h2 class="text-xl font-extrabold text-gray-800">{{ $course->teacherLessons->isEmpty() ? 'บทเรียนในคอร์ส' : 'แบบฝึกจากบทเรียนหลัก' }}</h2>
        @if (! $enrollment?->canStudy() && ! $isOwner)
            <p class="mt-1 text-sm text-gray-500">เข้าร่วมคอร์สเพื่อเริ่มเรียน</p>
        @endif
        <ol class="mt-4 space-y-2">
            @forelse ($course->lessons as $lesson)
                <li class="flex items-center justify-between gap-3 rounded-xl border border-gray-100 p-3">
                    <span>
                        <span class="mr-2 font-bold text-gray-400">{{ $loop->iteration }}.</span>
                        <span class="font-bold text-gray-700">{{ $lesson->title }}</span>
                        <span class="block text-xs text-gray-400">{{ $lesson->unit?->title }}</span>
                    </span>
                    @if ($enrollment?->canStudy())
                        @if ($lessonOpen[$lesson->id] ?? false)
                            <a href="{{ route('lessons.learn', $lesson) }}" class="shrink-0 rounded-lg bg-emerald-500 px-3 py-1.5 text-sm font-bold text-white">เรียน</a>
                        @else
                            <span class="shrink-0 text-xs text-gray-400" title="ต้องเรียนบทก่อนหน้าในบทเรียนหลักให้จบก่อน">🔒 ยังไม่เปิด</span>
                        @endif
                    @endif
                </li>
            @empty
                <li class="text-gray-400">ยังไม่มีบทเรียน</li>
            @endforelse
        </ol>
    </section>
    @endif
</div>
@endsection
