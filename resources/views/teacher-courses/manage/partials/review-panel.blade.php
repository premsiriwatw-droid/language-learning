@php
    use App\Models\TeacherCourse;

    $actions = match ($course->status) {
        TeacherCourse::DRAFT => ['submit' => 'ส่งให้แอดมินตรวจ'],
        TeacherCourse::PENDING_REVIEW => ['withdraw' => 'ถอนคำขอตรวจ (กลับไปแก้ไข)'],
        TeacherCourse::PUBLISHED => ['close' => 'ปิดคอร์ส'],
        TeacherCourse::CLOSED => $course->isApproved() ? ['reopen' => 'เปิดรับสมัครอีกครั้ง'] : ['submit' => 'ส่งให้แอดมินตรวจ'],
        default => [],
    };

    $wasRejected = $course->reviewed_at !== null && ! $course->isApproved()
        && in_array($course->status, [TeacherCourse::DRAFT, TeacherCourse::CLOSED], true)
        && $course->review_note;

    $steps = [
        TeacherCourse::DRAFT => 'เตรียมเนื้อหา',
        TeacherCourse::PENDING_REVIEW => 'แอดมินตรวจ',
        TeacherCourse::PUBLISHED => 'เผยแพร่',
    ];
    $current = array_search($course->status === TeacherCourse::CLOSED ? TeacherCourse::PUBLISHED : $course->status, array_keys($steps), true);
@endphp

<section class="space-y-4 rounded-2xl border-2 border-gray-200 bg-white p-4" aria-labelledby="review-panel-heading">
    <h2 id="review-panel-heading" class="sr-only">สถานะการเผยแพร่</h2>

    <ol class="flex flex-wrap items-center gap-2 text-sm font-bold">
        @foreach ($steps as $status => $label)
            <li class="flex items-center gap-2">
                <span class="flex h-7 w-7 items-center justify-center rounded-full {{ $loop->index < $current ? 'bg-emerald-500 text-white' : ($loop->index === $current ? 'bg-emerald-100 text-emerald-700 ring-2 ring-emerald-500' : 'bg-gray-100 text-gray-400') }}">
                    {{ $loop->index < $current ? '✓' : $loop->iteration }}
                </span>
                <span class="{{ $loop->index === $current ? 'text-gray-800' : 'text-gray-400' }}">{{ $label }}</span>
                @unless ($loop->last)<span class="text-gray-300" aria-hidden="true">—</span>@endunless
            </li>
        @endforeach
        @if ($course->status === TeacherCourse::CLOSED)
            <li class="rounded-full bg-gray-100 px-2.5 py-1 text-gray-600">ปิดคอร์สอยู่</li>
        @endif
    </ol>

    @if ($wasRejected)
        <div class="rounded-xl border-2 border-red-200 bg-red-50 p-3 text-sm text-red-800" role="alert">
            <p class="font-extrabold">แอดมินยังไม่อนุมัติ · {{ $course->reviewed_at->format('d/m/Y H:i') }}</p>
            <p class="mt-1 whitespace-pre-line">{{ $course->review_note }}</p>
            <p class="mt-1 text-xs">แก้ไขตามหมายเหตุ แล้วกด "ส่งให้แอดมินตรวจ" อีกครั้ง</p>
        </div>
    @elseif ($course->isPendingReview())
        <p class="rounded-xl bg-amber-50 p-3 text-sm text-amber-800">
            ส่งตรวจเมื่อ {{ $course->submitted_at?->format('d/m/Y H:i') }} · ผู้เรียนใหม่จะยังไม่เห็นคอร์สจนกว่าแอดมินจะอนุมัติ
            @if ($course->published_at) (ผู้เรียนที่เข้าร่วมแล้วยังเรียนต่อได้) @endif
        </p>
    @elseif ($course->isPublished() && $course->review_note)
        <p class="rounded-xl bg-emerald-50 p-3 text-sm text-emerald-800">หมายเหตุจากแอดมิน: {{ $course->review_note }}</p>
    @elseif ($course->status === TeacherCourse::DRAFT)
        <p class="text-sm text-gray-500">เพิ่มบทเรียนอย่างน้อย 1 บท แล้วส่งให้แอดมินตรวจ คอร์สจะเผยแพร่เมื่อได้รับอนุมัติ</p>
    @endif

    <div class="flex flex-wrap gap-2">
        @foreach ($actions as $action => $label)
            <form method="POST" action="{{ route('teacher.courses.status', $course) }}">
                @csrf
                @method('PATCH')
                <input type="hidden" name="action" value="{{ $action }}">
                <button type="submit" class="rounded-xl px-4 py-2 font-bold {{ in_array($action, ['submit', 'reopen'], true) ? 'bg-emerald-500 text-white hover:bg-emerald-600' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">{{ $label }}</button>
            </form>
        @endforeach
        <a href="{{ route('teacher-courses.show', $course) }}" class="rounded-xl border-2 border-gray-200 px-4 py-2 font-bold text-gray-700">ดูหน้าคอร์สแบบผู้เรียน</a>
        <a href="{{ route('teacher.courses.enrollments', $course) }}" class="ml-auto rounded-xl border-2 border-indigo-200 px-4 py-2 font-bold text-indigo-700">คำขอเข้าเรียน →</a>
    </div>
</section>
