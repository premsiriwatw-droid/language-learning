@extends('layouts.app')

@section('title', 'คำขอเข้าเรียน')

@php use App\Models\CourseEnrollment; @endphp

@section('content')
<div class="space-y-6">
    <div>
        <a href="{{ route('teacher.courses.index') }}" class="text-sm font-bold text-emerald-600">← คอร์สของฉัน</a>
        <h1 class="mt-2 text-3xl font-extrabold text-gray-800">คำขอเข้าเรียน</h1>
        <p class="mt-1 text-gray-500">{{ $course->title }} · {{ $course->priceLabel() }}</p>
    </div>

    @include('teacher-courses.partials.flash')

    @foreach (CourseEnrollment::STATUSES as $status)
        @continue(! isset($enrollments[$status]))
        <section class="space-y-3">
            <h2 class="text-lg font-extrabold text-gray-700">{{ CourseEnrollment::STATUS_LABELS[$status] }} ({{ $enrollments[$status]->count() }})</h2>
            @foreach ($enrollments[$status] as $enrollment)
                <div class="rounded-2xl border-2 border-gray-200 bg-white p-4" x-data="{ note: false }">
                    <div class="flex flex-wrap items-start justify-between gap-3">
                        <div>
                            <p class="font-extrabold text-gray-800">{{ $enrollment->user->name }}</p>
                            <p class="text-sm text-gray-500">{{ $enrollment->user->email }} · ขอเมื่อ {{ $enrollment->requested_at->format('d/m/Y H:i') }}</p>
                            @if ($enrollment->payment_status !== CourseEnrollment::PAYMENT_NOT_REQUIRED)
                                <p class="mt-1 text-sm {{ $enrollment->payment_status === CourseEnrollment::PAYMENT_SUBMITTED ? 'font-bold text-amber-700' : 'text-gray-500' }}">
                                    การชำระเงิน: {{ $enrollment->paymentLabel() }}
                                    @if ($enrollment->price_at_request) (฿{{ number_format((float) $enrollment->price_at_request, 2) }}) @endif
                                </p>
                            @endif
                        </div>
                        <div class="flex flex-wrap gap-2">
                            @php
                                $actions = match ($enrollment->status) {
                                    CourseEnrollment::PENDING => ['approve' => 'อนุมัติ', 'reject' => 'ปฏิเสธ'],
                                    CourseEnrollment::APPROVED => ['confirm_payment' => 'ยืนยันรับชำระแล้ว', 'reject' => 'ปฏิเสธ'],
                                    CourseEnrollment::ACTIVE => ['complete' => 'บันทึกว่าเรียนจบ'],
                                    default => [],
                                };
                            @endphp
                            @foreach ($actions as $action => $label)
                                <form method="POST" action="{{ route('teacher.courses.enrollments.update', $enrollment) }}" class="flex items-start gap-2">
                                    @csrf
                                    @method('PATCH')
                                    <input type="hidden" name="action" value="{{ $action }}">
                                    @if (in_array($action, ['approve', 'reject'], true))
                                        <input type="text" name="note" maxlength="500" x-show="note" x-cloak placeholder="ข้อความถึงผู้เรียน (ไม่บังคับ)" class="w-48 rounded-lg border-2 border-gray-200 px-2 py-1 text-sm">
                                    @endif
                                    <button type="submit" class="rounded-lg px-3 py-1.5 font-bold {{ $action === 'reject' ? 'bg-red-50 text-red-600' : 'bg-emerald-500 text-white' }}">{{ $label }}</button>
                                </form>
                            @endforeach
                            @if (in_array($enrollment->status, [CourseEnrollment::PENDING, CourseEnrollment::APPROVED], true))
                                <button type="button" @click="note = !note" class="text-sm font-bold text-gray-500 underline">ใส่ข้อความ</button>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </section>
    @endforeach

    @if ($enrollments->isEmpty())
        <div class="rounded-2xl border-2 border-dashed border-gray-300 bg-white py-12 text-center text-gray-500">ยังไม่มีผู้ขอเข้าเรียน</div>
    @endif
</div>
@endsection
