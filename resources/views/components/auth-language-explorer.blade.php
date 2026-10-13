<section class="login-story" data-explorer aria-label="สำรวจบทเรียนภาษา">
    <div class="explorer-wind" aria-hidden="true"><span></span><span></span><span></span></div>
    <div class="explorer-stage">
        <div class="story-view" data-explorer-home>
            <div class="book-stage" aria-hidden="true">
                <div class="book-shadow"></div>
                <div class="book" data-book>
                    <div class="book-back"></div><div class="book-pages"></div><div class="book-spine"></div>
                    <div class="book-leaf book-leaf-three"></div><div class="book-leaf book-leaf-two"></div><div class="book-leaf book-leaf-one"></div>
                    <div class="book-cover">
                        <div class="book-cover-face"><span class="book-monogram">Ll.</span><span class="book-cover-rule"></span><span class="book-volume">VOL. 01</span></div>
                        <div class="book-cover-inside"></div>
                    </div>
                </div>
            </div>
            <p class="story-eyebrow"><span class="eyebrow-line"></span> YOUR NEXT CHAPTER</p>
            <h2 id="story-heading">Learn languages.<br><span>Open your world.</span></h2>
            <p class="story-description">เรียนรู้ภาษา เป็นเพื่อนกับทุกเชื้อชาติ<br>เริ่มต้นจากบทเรียนที่ใช่สำหรับคุณ</p>
            <div class="language-explorer">
                <p class="explorer-label" id="explorer-hint">เลือกภาษาเพื่อดูสารบัญบทเรียน</p>
                <div class="language-buttons" role="group" aria-label="เลือกภาษาที่สนใจ" aria-describedby="explorer-hint">
                    <button class="language-button" type="button" data-scene-trigger="england" aria-controls="outline-england" aria-expanded="false" disabled>
                        <svg class="language-flag" viewBox="0 0 60 40" aria-hidden="true"><path fill="#253b72" d="M0 0h60v40H0z"></path><path stroke="#fff" stroke-width="9" d="m0 0 60 40M60 0 0 40"></path><path stroke="#d33d50" stroke-width="3" d="m0 0 60 40M60 0 0 40"></path><path stroke="#fff" stroke-width="13" d="M30 0v40M0 20h60"></path><path stroke="#d33d50" stroke-width="7" d="M30 0v40M0 20h60"></path></svg>
                        English <span class="language-arrow" aria-hidden="true">↗</span>
                    </button>
                    <button class="language-button" type="button" data-scene-trigger="china" aria-controls="outline-china" aria-expanded="false" disabled>
                        <svg class="language-flag" viewBox="0 0 60 40" aria-hidden="true"><path fill="#df3545" d="M0 0h60v40H0z"></path><path fill="#ffe089" d="m12 5 2 6h6l-5 4 2 6-5-4-5 4 2-6-5-4h6zM25 4l1 2h2l-2 2 1 2-2-1-2 1 1-2-2-2h2zM31 10l1 2h2l-2 2 1 2-2-1-2 1 1-2-2-2h2zM31 20l1 2h2l-2 2 1 2-2-1-2 1 1-2-2-2h2zM25 26l1 2h2l-2 2 1 2-2-1-2 1 1-2-2-2h2z"></path></svg>
                        <span lang="zh">中文</span> <span class="language-arrow" aria-hidden="true">↗</span>
                    </button>
                </div>
                <noscript><p class="field-hint">เปิด JavaScript เพื่อเลือกดูสารบัญบทเรียน</p></noscript>
            </div>
            <div class="scene-caption" aria-hidden="true"><span class="scene-dot"></span><span data-scene-name>เลือกภาษา แล้วเริ่มบทใหม่ไปด้วยกัน</span></div>
        </div>

        @foreach ($curricula as $scene => $language)
            @php
                $label = $scene === 'england' ? 'ภาษาอังกฤษ' : 'ภาษาจีน';
                $courses = $language?->courses ?? collect();
                $lessonCount = $courses->sum(fn ($course) => $course->units->sum(fn ($unit) => $unit->lessons->count()));
            @endphp
            <section class="outline-view" id="outline-{{ $scene }}" data-outline="{{ $scene }}" aria-labelledby="outline-heading-{{ $scene }}" hidden>
                <button type="button" class="outline-back" data-outline-back><span aria-hidden="true">←</span> กลับหน้าแนะนำ</button>
                <div class="outline-header">
                    <p class="story-eyebrow"><span class="eyebrow-line"></span> EXPLORE THE LESSONS</p>
                    <h2 id="outline-heading-{{ $scene }}" data-outline-heading tabindex="-1">สารบัญ<span>{{ $label }}</span></h2>
                    <p class="outline-summary">{{ $courses->count() }} คอร์ส <span aria-hidden="true">·</span> {{ $lessonCount }} บทเรียน</p>
                </div>
                <div class="outline-scroll" tabindex="0" role="region" aria-label="รายชื่อบทเรียน{{ $label }}">
                    @if ($lessonCount === 0)
                        <p class="outline-empty">ยังไม่มีบทเรียนสำหรับภาษานี้</p>
                    @else
                        @foreach ($courses as $course)
                            <section class="outline-course">
                                <h3>{{ $course->title }}</h3>
                                @forelse ($course->units as $unit)
                                    <details class="outline-unit" @if ($loop->first && $loop->parent->first) open @endif>
                                        <summary><span>{{ $unit->title }}</span><span class="unit-count">{{ $unit->lessons->count() }} บท</span><span class="unit-chevron" aria-hidden="true">⌄</span></summary>
                                        <ol class="outline-lessons">
                                            @forelse ($unit->lessons as $lesson)
                                                <li><span class="lesson-number" aria-hidden="true">{{ str_pad((string) $loop->iteration, 2, '0', STR_PAD_LEFT) }}</span><span>{{ $lesson->title }}</span></li>
                                            @empty
                                                <li class="outline-unit-empty">ยังไม่มีบทเรียนใน Unit นี้</li>
                                            @endforelse
                                        </ol>
                                    </details>
                                @empty
                                    <p class="field-hint">ยังไม่มี Unit ในคอร์สนี้</p>
                                @endforelse
                            </section>
                        @endforeach
                    @endif
                </div>
                <p class="outline-note">ดูหัวข้อก่อนเริ่มเรียน แล้วเข้าสู่ระบบหรือสมัครสมาชิกเพื่อเรียนต่อ</p>
            </section>
        @endforeach
    </div>
</section>
