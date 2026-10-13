@php
    $isTeacher = $course->isOwnedBy(auth()->user());
    $teacherParams = ['teacherCourse' => $course->id, 'student' => $student->id];
@endphp

@include('teacher-courses.partials.flash')

<section class="space-y-4 rounded-xl border bg-white p-5">
    <h2 class="text-xl font-bold">บทเรียนที่แนะนำให้ทบทวน</h2>
    @if ($isTeacher)
        <form method="POST" action="{{ route('teacher.courses.students.recommend', $teacherParams) }}" class="space-y-3">
            @csrf
            <label class="block">เลือกบทเรียน
                <select name="lesson_id" required class="block w-full rounded border p-2">
                    <option value="">เลือกบทเรียนในคอร์ส</option>
                    @foreach ($panel['lessons'] as $lesson)
                        <option value="{{ $lesson->id }}">{{ $lesson->title }}</option>
                    @endforeach
                </select>
            </label>
            <label class="block">เหตุผลที่ควรทบทวน
                <textarea name="reason" required maxlength="2000" rows="3" class="block w-full rounded border p-2">{{ old('reason') }}</textarea>
            </label>
            <button class="rounded bg-emerald-600 px-4 py-2 text-white">ส่งคำแนะนำ</button>
        </form>
    @endif

    @forelse ($panel['recommendations'] as $recommendation)
        <article class="border-t pt-3">
            <strong>{{ $recommendation->lesson_title }}</strong>
            <p class="whitespace-pre-wrap">{{ $recommendation->reason }}</p>
            @if ($recommendation->lesson)
                <a class="font-bold text-emerald-600" href="{{ route('lessons.show', $recommendation->lesson_id) }}">เปิดบทเรียน →</a>
            @else
                <p class="text-gray-500">บทเรียนนี้ไม่พร้อมใช้งานแล้ว</p>
            @endif
        </article>
    @empty
        <p class="text-gray-500">ยังไม่มีคำแนะนำ</p>
    @endforelse
    {{ $panel['recommendations']->links() }}
</section>

<section class="space-y-4 rounded-xl border bg-white p-5">
    <h2 class="text-xl font-bold">งานที่มอบหมาย</h2>
    @if ($isTeacher)
        <form method="POST" action="{{ route('teacher.courses.students.assign', $teacherParams) }}" class="space-y-3">
            @csrf
            <label class="block">ชื่องาน
                <input name="title" required maxlength="160" value="{{ old('title') }}" class="block w-full rounded border p-2">
            </label>
            <label class="block">เนื้อหาที่ต้องเรียน
                <select name="target" required class="block w-full rounded border p-2">
                    <option value="">เลือกบทเรียนหรือ Unit</option>
                    <optgroup label="บทเรียน">
                        @foreach ($panel['lessons'] as $lesson)
                            <option value="lesson:{{ $lesson->id }}">{{ $lesson->title }}</option>
                        @endforeach
                    </optgroup>
                    <optgroup label="Unit (เฉพาะบทในคอร์สนี้)">
                        @foreach ($panel['units'] as $unit)
                            <option value="unit:{{ $unit->id }}">{{ $unit->title }}</option>
                        @endforeach
                    </optgroup>
                </select>
            </label>
            <label class="block">คำแนะนำเพิ่มเติม
                <textarea name="instructions" maxlength="2000" rows="3" class="block w-full rounded border p-2">{{ old('instructions') }}</textarea>
            </label>
            <label class="block">กำหนดส่ง ({{ config('app.timezone') }})
                <input type="datetime-local" name="due_at" required value="{{ old('due_at') }}" class="block rounded border p-2">
            </label>
            <button class="rounded bg-emerald-600 px-4 py-2 text-white">มอบหมายงาน</button>
        </form>
    @endif

    @forelse ($panel['assignments'] as $receipt)
        @php
            $assignment = $receipt->assignment;
            $done = $receipt->status === \App\Models\AssignmentStatus::COMPLETED;
        @endphp
        <article class="space-y-2 border-t pt-3">
            <h3 class="font-bold">{{ $assignment->title }}</h3>
            <p>{{ $assignment->target_kind === 'unit' ? 'Unit' : 'บทเรียน' }}: {{ $assignment->target_label }}</p>
            <p class="whitespace-pre-wrap">{{ $assignment->instructions }}</p>
            <p>กำหนดส่ง {{ $assignment->due_at->format('d/m/Y H:i') }} ({{ config('app.timezone') }})</p>
            <p>สถานะ: {{ \App\Models\AssignmentStatus::LABELS[$receipt->status] ?? $receipt->status }}
                @if ($done && $receipt->completed_at?->greaterThan($assignment->due_at))
                    · ส่งช้า
                @elseif (! $done && now()->greaterThan($assignment->due_at))
                    · เลยกำหนดส่ง
                @endif
            </p>
            @if (! $isTeacher && ! $done)
                <p class="text-sm text-gray-500">เรียนบทในงานนี้ให้ครบหลังได้รับงาน แล้วกดยืนยัน ระบบจะตรวจประวัติการเรียนให้</p>
                <ul>
                    @foreach ($assignment->lesson_ids as $lessonId)
                        <li>
                            @if (isset($panel['assignment_lessons'][$lessonId]))
                                <a class="text-emerald-600" href="{{ route('lessons.show', $lessonId) }}">{{ $panel['assignment_lessons'][$lessonId] }} →</a>
                            @else
                                <span class="text-gray-500">บทเรียนไม่พร้อมใช้งาน กรุณาติดต่อผู้สอน</span>
                            @endif
                        </li>
                    @endforeach
                </ul>
                <div class="flex flex-wrap gap-2">
                    @if ($receipt->status === \App\Models\AssignmentStatus::PENDING)
                        <form method="POST" action="{{ route('teacher-courses.assignments.start', ['teacherCourse' => $course->id, 'assignment' => $assignment->id]) }}">
                            @csrf
                            <button class="rounded border px-3 py-2">เริ่มทำ</button>
                        </form>
                    @endif
                    <form method="POST" action="{{ route('teacher-courses.assignments.complete', ['teacherCourse' => $course->id, 'assignment' => $assignment->id]) }}">
                        @csrf
                        <button class="rounded bg-emerald-600 px-3 py-2 text-white">ตรวจและยืนยันว่าเสร็จ</button>
                    </form>
                </div>
            @endif
        </article>
    @empty
        <p class="text-gray-500">ยังไม่มีงานที่มอบหมาย</p>
    @endforelse
    {{ $panel['assignments']->links() }}
</section>

<section id="messages" class="space-y-4 rounded-xl border bg-white p-5">
    <h2 class="text-xl font-bold">ข้อความระหว่างครูกับนักเรียน</h2>
    @forelse ($panel['messages']->getCollection()->reverse() as $message)
        <article class="rounded border p-3">
            <p class="font-bold">{{ $message->sender?->name ?? 'บัญชีที่ถูกลบ' }}</p>
            <p class="whitespace-pre-wrap">{{ $message->body }}</p>
            <p class="text-sm text-gray-500">{{ $message->created_at->format('d/m/Y H:i') }} ({{ config('app.timezone') }})</p>
        </article>
    @empty
        <p class="text-gray-500">ยังไม่มีข้อความ</p>
    @endforelse
    {{ $panel['messages']->links() }}
    <p class="text-sm text-gray-500">รีเฟรชหน้าเพื่ออ่านข้อความใหม่ หน้าถัดไปแสดงข้อความเก่า</p>

    <form method="POST"
        action="{{ $isTeacher ? route('teacher.courses.students.message', $teacherParams) : route('teacher-courses.messages.store', $course) }}"
        class="space-y-3">
        @csrf
        <label class="block">ข้อความ
            <textarea name="body" required maxlength="2000" rows="3" class="block w-full rounded border p-2">{{ old('body') }}</textarea>
        </label>
        <button class="rounded bg-emerald-600 px-4 py-2 text-white">ส่งข้อความ</button>
    </form>
</section>
