@extends('layouts.app')

@section('title', 'จุดที่ควรทบทวน')

@section('content')
<div class="space-y-6">
    <div>
        <a
            href="{{ route('teacher.courses.dashboard', $course) }}"
            class="font-bold text-emerald-600"
        >
            ← กลับภาพรวมการเรียน
        </a>

        <h1 class="mt-3 text-2xl font-bold">
            จุดที่ควรทบทวน
        </h1>

        <p class="mt-2">{{ $course->title }}</p>

        <p class="mt-2 text-sm text-gray-500">
            ข้อมูล ณ {{ $report['as_of']->format('d/m/Y H:i') }}
            ({{ config('app.timezone') }})
        </p>
    </div>

    <section class="rounded-xl border bg-white p-5">
        <h2 class="text-xl font-bold">คำถามที่ผิดบ่อย</h2>

        <p class="mt-2 text-sm text-gray-500">
            จำนวนตอบผิดทั้งหมด รวมการเรียนซ้ำ
        </p>

        <ol class="mt-4 space-y-3">
            @forelse ($report['wrong_questions'] as $question)
                <li>
                    {{ $loop->iteration }}.
                    {{ $question['text'] }}
                    — ตอบผิด
                    {{ number_format($question['wrong_attempts']) }}
                    ครั้ง
                </li>
            @empty
                <li class="text-gray-500">
                    ยังไม่มีข้อมูลคำถามที่ตอบผิด
                </li>
            @endforelse
        </ol>
    </section>

    <section class="rounded-xl border bg-white p-5">
        <h2 class="text-xl font-bold">
            คำศัพท์ที่เกี่ยวข้องกับข้อที่ผิด
        </h2>

        <p class="mt-2 text-sm text-gray-500">
            แสดงคำศัพท์ที่เชื่อมกับคำถามที่ตอบผิด
            จำนวนนี้ไม่ได้ยืนยันว่าผิดที่คำศัพท์นั้นโดยตรง
        </p>

        <ol class="mt-4 space-y-3">
            @forelse ($report['related_words'] as $word)
                <li>
                    {{ $loop->iteration }}.
                    {{ $word['word'] }}
                    — เกี่ยวข้องกับการตอบผิด
                    {{ number_format($word['wrong_attempts']) }}
                    ครั้ง
                </li>
            @empty
                <li class="text-gray-500">
                    ยังไม่มีข้อมูลคำศัพท์ที่เกี่ยวข้อง
                </li>
            @endforelse
        </ol>
    </section>

    <section class="rounded-xl border bg-white p-5">
        <h2 class="text-xl font-bold">
            บทเรียนคะแนนเฉลี่ยต่ำสุด
        </h2>

        <p class="mt-2 text-sm text-gray-500">
            ใช้คะแนนรอบที่จบครั้งแรกของแต่ละคนในแต่ละบท
        </p>

        <ul class="mt-4 space-y-3">
            @forelse ($report['low_lessons'] as $lesson)
                <li>
                    <strong>{{ $lesson['title'] }}</strong>
                    — {{ number_format($lesson['average_score'], 1) }}%
                    จากผู้เรียน {{ $lesson['scored_count'] }} คน
                </li>
            @empty
                <li class="text-gray-500">
                    ยังไม่มีคะแนนที่ใช้จัดอันดับได้
                </li>
            @endforelse
        </ul>
    </section>

    <p class="text-sm text-gray-500">
        ข้อมูลอ้างอิงบทเรียนที่ใช้ร่วมกับระบบหลัก
        จึงรวมการเรียนบทเหล่านี้ก่อนเข้าคอร์สด้วย
    </p>
</div>
@endsection