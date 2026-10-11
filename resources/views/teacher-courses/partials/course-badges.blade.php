{{-- expects $course --}}
<div class="flex flex-wrap gap-2 text-xs font-bold">
    <span class="rounded-full bg-sky-50 px-2.5 py-1 text-sky-700">{{ $course->language?->name ?? 'ไม่ระบุภาษา' }}</span>
    <span class="rounded-full bg-amber-50 px-2.5 py-1 text-amber-700">ระดับ{{ $course->levelLabel() }}</span>
    <span class="rounded-full px-2.5 py-1 {{ $course->isFree() ? 'bg-emerald-50 text-emerald-700' : 'bg-rose-50 text-rose-700' }}">{{ $course->priceLabel() }}</span>
    <span class="rounded-full bg-gray-100 px-2.5 py-1 text-gray-600">{{ $course->enrollment_mode === \App\Models\TeacherCourse::INSTANT ? 'เข้าได้ทันที' : 'ผู้สอนอนุมัติก่อน' }}</span>
</div>
