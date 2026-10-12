@extends('layouts.app')

@section('title', 'รายชื่อนักเรียน')

@section('content')
<div class="space-y-6">
    <div>
        <a href="{{ route('teacher.courses.dashboard', $course) }}" class="text-sm font-bold text-emerald-600">← ภาพรวมการเรียน</a>
        <h1 class="mt-3 text-3xl font-extrabold text-gray-800">รายชื่อนักเรียน</h1>
        <h2 class="mt-2 text-xl font-bold">{{ $course->title }}</h2>
        <div class="mt-2">@include('teacher-courses.partials.course-badges', ['course' => $course])</div>
        <p class="mt-2 text-sm text-gray-500">{{ number_format($students->total()) }} คน · {{ $lessonsCount }} บทเรียน · ข้อมูล ณ {{ $asOf->format('d/m/Y H:i') }} ({{ config('app.timezone') }})</p>
    </div>

    <p class="text-sm leading-relaxed text-gray-600">แสดงผู้เรียนที่กำลังเรียนและเรียนจบแล้ว ความคืบหน้า กิจกรรมล่าสุด และบทเรียนปัจจุบันอ้างอิงเฉพาะบทในคอร์สนี้ ส่วน XP / Level เป็นยอดรวมของผู้เรียนจากทุกบทเรียน</p>
    @if ($lessonsCount === 0)
        <p class="rounded-xl border border-amber-200 bg-amber-50 p-4 text-amber-800">คอร์สนี้ยังไม่มีบทเรียน จึงยังแสดงความคืบหน้าและบทเรียนปัจจุบันไม่ได้</p>
    @endif

    @if ($students->isNotEmpty())
        <div class="overflow-x-auto rounded-2xl border border-gray-200 bg-white">
            <table class="min-w-full text-left text-sm">
                <caption class="sr-only">ความคืบหน้าของนักเรียนใน {{ $course->title }}</caption>
                <thead class="bg-gray-50 text-gray-600">
                    <tr>
                        <th scope="col" class="px-4 py-3">นักเรียน</th>
                        <th scope="col" class="px-4 py-3">ความคืบหน้า</th>
                        <th scope="col" class="px-4 py-3">XP / Level</th>
                        <th scope="col" class="px-4 py-3">กิจกรรมล่าสุดในคอร์ส</th>
                        <th scope="col" class="px-4 py-3">บทเรียนปัจจุบัน</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach ($students as $student)
                        <tr>
                            <th scope="row" class="px-4 py-4 font-bold text-gray-800">{{ $student['name'] }}</th>
                            <td class="px-4 py-4">
                                @if ($student['progress_percent'] === null)
                                    —
                                @else
                                    <span class="block font-bold">{{ number_format($student['progress_percent'], 1) }}%</span>
                                    <span class="whitespace-nowrap text-gray-500">{{ $student['completed_lessons'] }} / {{ $lessonsCount }} บท</span>
                                    <progress class="mt-1 block h-2 w-24 accent-emerald-500" value="{{ $student['progress_percent'] }}" max="100" aria-label="ความคืบหน้าของ {{ $student['name'] }}">{{ $student['progress_percent'] }}%</progress>
                                @endif
                            </td>
                            <td class="whitespace-nowrap px-4 py-4">
                                <span class="block font-bold">{{ number_format($student['xp']) }} XP</span>
                                <span class="text-gray-500">Level {{ $student['level'] }}</span>
                            </td>
                            <td class="whitespace-nowrap px-4 py-4 text-gray-600">{{ $student['last_activity_at']?->format('d/m/Y H:i') ?? 'ยังไม่มีกิจกรรม' }}</td>
                            <td class="px-4 py-4">
                                @if ($lessonsCount === 0)
                                    <span class="text-gray-500">ยังไม่มีบทเรียน</span>
                                @elseif ($student['completed_lessons'] === $lessonsCount)
                                    <span class="font-bold text-emerald-700">เรียนครบทุกบทแล้ว</span>
                                @else
                                    <span class="block text-gray-500">{{ $student['current_lesson_started'] ? 'กำลังเรียน' : ($student['last_activity_at'] ? 'บทถัดไปในหลักสูตร' : 'ยังไม่เริ่มเรียน') }}</span>
                                    <span class="font-bold">{{ $student['current_lesson']?->title }}</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @elseif ($students->total() === 0)
        <p class="rounded-xl border border-gray-200 bg-white p-6 text-center text-gray-500">ยังไม่มีนักเรียนที่เริ่มเรียนในคอร์สนี้</p>
    @else
        <p class="rounded-xl border border-gray-200 bg-white p-6 text-gray-500">ไม่มีรายการนักเรียนในหน้านี้ <a class="font-bold text-emerald-600" href="{{ route('teacher.courses.students', $course) }}">กลับไปหน้าแรก</a></p>
    @endif

    {{ $students->links() }}
    <p class="text-sm leading-relaxed text-gray-500">บทเรียนใช้ร่วมกับระบบหลัก จึงรวมการเรียนก่อนเข้าคอร์สด้วย “บทถัดไปในหลักสูตร” คือบทที่ยังไม่จบตามลำดับคอร์ส ส่วนการปลดล็อกบทเรียนยังใช้กฎเดิมของระบบ</p>
</div>
@endsection
