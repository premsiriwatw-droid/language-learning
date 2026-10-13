@php use App\Models\TeacherLesson; @endphp

<section id="my-lessons" class="space-y-4 rounded-2xl border-2 border-gray-200 bg-white p-6" aria-labelledby="my-lessons-heading">
    <div class="flex flex-wrap items-end justify-between gap-3">
        <div>
            <h2 id="my-lessons-heading" class="text-xl font-extrabold text-gray-800">บทเรียนของฉัน</h2>
            <p class="text-sm text-gray-500">เขียนเนื้อหาเอง แนบเอกสาร (PDF, Word, PowerPoint, Excel) และวิดีโอ (ลิงก์ YouTube/Vimeo หรืออัปโหลด MP4/WebM)</p>
        </div>
        <a href="{{ route('teacher.courses.teacher-lessons.create', $course) }}" class="rounded-xl bg-emerald-500 px-4 py-2 font-bold text-white hover:bg-emerald-600">+ เพิ่มบทเรียน</a>
    </div>

    <ol class="space-y-2">
        @forelse ($teacherLessons as $lesson)
            <li class="flex flex-wrap items-center justify-between gap-3 rounded-xl border border-gray-200 p-3">
                <div class="min-w-0">
                    <p class="font-bold text-gray-800"><span class="mr-1 text-gray-400">{{ $loop->iteration }}.</span> {{ $lesson->title }}</p>
                    <p class="mt-0.5 flex flex-wrap gap-2 text-xs text-gray-500">
                        @if ($lesson->body) <span>📝 ข้อความ</span> @endif
                        @if ($lesson->hasDocument()) <span>📄 {{ $lesson->document_name }} ({{ TeacherLesson::sizeLabel($lesson->document_size) }})</span> @endif
                        @if ($lesson->hasVideoFile()) <span>🎬 {{ $lesson->video_name }} ({{ TeacherLesson::sizeLabel($lesson->video_size) }})</span>
                        @elseif ($lesson->video_url) <span>🎬 วิดีโอจากลิงก์</span> @endif
                    </p>
                </div>
                <div class="flex flex-wrap items-center gap-1.5">
                    @foreach (['up' => ['↑', 'เลื่อนขึ้น'], 'down' => ['↓', 'เลื่อนลง']] as $direction => [$icon, $label])
                        <form method="POST" action="{{ route('teacher.courses.teacher-lessons.move', [$course, $lesson]) }}">
                            @csrf
                            @method('PATCH')
                            <input type="hidden" name="direction" value="{{ $direction }}">
                            <button type="submit" class="rounded-lg bg-gray-100 px-2.5 py-1.5 font-bold text-gray-600 disabled:opacity-30" aria-label="{{ $label }} {{ $lesson->title }}"
                                @disabled(($direction === 'up' && $loop->parent->first) || ($direction === 'down' && $loop->parent->last))>{{ $icon }}</button>
                        </form>
                    @endforeach
                    <a href="{{ route('teacher-courses.lessons.show', [$course, $lesson]) }}" class="rounded-lg bg-gray-100 px-3 py-1.5 text-sm font-bold text-gray-600">ดู</a>
                    <a href="{{ route('teacher.courses.teacher-lessons.edit', [$course, $lesson]) }}" class="rounded-lg bg-blue-50 px-3 py-1.5 text-sm font-bold text-blue-700">แก้ไข</a>
                    <form method="POST" action="{{ route('teacher.courses.teacher-lessons.destroy', [$course, $lesson]) }}" x-data data-title="{{ $lesson->title }}"
                        @submit="if (! confirm('ลบบทเรียน «' + $el.dataset.title + '» และไฟล์ที่แนบ?')) $event.preventDefault()">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="rounded-lg bg-red-50 px-3 py-1.5 text-sm font-bold text-red-700">ลบ</button>
                    </form>
                </div>
            </li>
        @empty
            <li class="rounded-xl border-2 border-dashed border-gray-300 py-8 text-center text-gray-500">
                ยังไม่มีบทเรียนของคุณ กด "+ เพิ่มบทเรียน" เพื่อเริ่มเขียน
            </li>
        @endforelse
    </ol>
</section>
