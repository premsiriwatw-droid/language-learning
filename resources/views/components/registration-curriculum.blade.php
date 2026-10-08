@foreach ($curricula as $scene => $curriculum)
    @php
        $courses = $curriculum['courses'];
        $units = $courses->flatMap(fn ($course) => $course->units);
        $lessonCount = $units->sum(fn ($unit) => $unit->lessons->count());
    @endphp
    <div id="curriculum-{{ $scene }}" data-curriculum-panel="{{ $scene }}" aria-labelledby="curriculum-heading-{{ $scene }}" hidden>
        <button type="button" class="curriculum-back" data-curriculum-back>
            <span aria-hidden="true">←</span> กลับไปเลือกภาษา
        </button>
        <p class="curriculum-kicker">{{ $curriculum['label'] }}</p>
        <h2 class="curriculum-heading" id="curriculum-heading-{{ $scene }}" tabindex="-1">สารบัญ{{ $curriculum['name'] }}</h2>
        @if ($courses->isNotEmpty())
            <p class="curriculum-intro">{{ $courses->count() }} หลักสูตร · {{ $units->count() }} หน่วยการเรียน · {{ $lessonCount }} บทเรียน</p>
            <div class="curriculum-scroll" role="region" aria-label="สารบัญ{{ $curriculum['name'] }}" tabindex="0">
                @foreach ($courses as $course)
                    <section class="curriculum-course" aria-labelledby="curriculum-course-{{ $course->id }}">
                        <h3 class="curriculum-course-title" id="curriculum-course-{{ $course->id }}">{{ $course->title }}</h3>
                        <ol class="curriculum-units">
                            @forelse ($course->units as $unit)
                                <li class="curriculum-unit">
                                    <h4 class="curriculum-unit-heading">{{ $unit->title }}</h4>
                                    <ul class="curriculum-lessons">
                                        @forelse ($unit->lessons->take(3) as $lesson)
                                            <li>{{ $lesson->title }}</li>
                                        @empty
                                            <li class="curriculum-empty">ยังไม่มีบทเรียนในหน่วยนี้</li>
                                        @endforelse
                                    </ul>
                                    @if ($unit->lessons->count() > 3)
                                        <p class="curriculum-more">และอีก {{ $unit->lessons->count() - 3 }} บทเรียน</p>
                                    @endif
                                </li>
                            @empty
                                <li class="curriculum-empty">ยังไม่มีหน่วยการเรียนในหลักสูตรนี้</li>
                            @endforelse
                        </ol>
                    </section>
                @endforeach
            </div>
        @else
            <p class="curriculum-intro">ดูหัวข้อที่จะได้เรียนก่อนเริ่มต้นเส้นทางของคุณ</p>
            <p class="curriculum-empty">ยังไม่มีบทเรียน{{ $curriculum['name'] }}ในระบบ</p>
        @endif
    </div>
@endforeach
