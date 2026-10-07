@extends('layouts.app')
@section('title', 'โปรไฟล์ของฉัน')

@section('content')
<div class="learner-profile">
    <a class="back-link" href="{{ url('/languages') }}"><span aria-hidden="true">←</span> กลับหน้าหลัก</a>

    <header class="profile-heading">
        <div>
            <p class="eyebrow">พื้นที่การเรียนรู้ของคุณ</p>
            <h1>โปรไฟล์ของฉัน<span class="heading-dot">.</span></h1>
            <p class="muted">ค่อย ๆ เรียนรู้ เก่งขึ้นในทุกวัน</p>
        </div>
        <span class="learning-badge"><span aria-hidden="true">✦</span> เรียนรู้ในแบบของคุณ</span>
    </header>

    @if(session('status'))
        <div class="profile-notice success-notice" role="status">{{ session('status') }}</div>
    @endif
    @if($errors->has('photo'))
        <div class="profile-notice error-notice" role="alert" id="photo-error">{{ $errors->first('photo') }}</div>
    @endif

    <section class="profile-card identity-card" aria-labelledby="learner-name">
        <div class="identity-main">
            <div class="profile-avatar" id="profile-avatar">
                @if($hasPhoto)
                    <img id="avatar-preview" src="{{ route('profile.photo') }}" alt="รูปโปรไฟล์ของ {{ $user->name }}" width="100" height="100">
                @else
                    <span id="avatar-initial" aria-hidden="true">{{ mb_substr($user->name, 0, 1) }}</span>
                    <img id="avatar-preview" alt="ตัวอย่างรูปโปรไฟล์ที่เลือก" width="100" height="100" hidden>
                @endif
            </div>
            <div class="identity-copy">
                <p class="eyebrow">ยินดีต้อนรับกลับมา</p>
                <h2 id="learner-name">{{ $user->name }}</h2>
                <p class="muted user-email">{{ $user->email }}</p>
            </div>
        </div>
        <form class="photo-form" action="{{ route('profile.photo.upload') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <label for="profile-photo" class="field-label">เปลี่ยนรูปโปรไฟล์</label>
            <p id="photo-help" class="field-hint">JPG, PNG หรือ WebP ไม่เกิน 20 MB ระบบจะย่อรูปใหญ่ให้ก่อนบันทึก</p>
            <noscript><p class="field-hint">หากปิด JavaScript กรุณาใช้รูปไม่เกิน 2 MB และ 4096 × 4096 พิกเซล</p></noscript>
            <input id="profile-photo" name="photo" type="file" accept="image/jpeg,image/png,image/webp" required
                aria-describedby="photo-help photo-feedback{{ $errors->has('photo') ? ' photo-error' : '' }}"
                @if($errors->has('photo')) aria-invalid="true" @endif>
            <p id="photo-feedback" class="field-hint" role="status" aria-live="polite"></p>
            <button class="profile-button secondary-button" type="submit">บันทึกรูปโปรไฟล์ <span aria-hidden="true">↗</span></button>
        </form>
    </section>

    <div class="profile-stats">
        <section class="profile-card stat-card hsk-card" aria-labelledby="hsk-label">
            <span class="stat-icon" aria-hidden="true">文</span>
            <p id="hsk-label" class="stat-label">ระดับภาษาจีน</p>
            <p class="stat-value">{{ $hskLevel !== null ? 'HSK '.$hskLevel : 'ยังไม่ระบุ' }}</p>
            <p class="field-hint">{{ $hskLevel !== null ? 'ระดับภาษาที่บันทึกในโปรไฟล์' : 'เมื่อมีข้อมูลระดับภาษา จะแสดงที่นี่' }}</p>
        </section>
        <section class="profile-card stat-card stars-card" aria-labelledby="stars-label">
            <span class="stat-icon" aria-hidden="true">★</span>
            <p id="stars-label" class="stat-label">ดาวสะสม</p>
            <p class="stat-value">{{ number_format($progress['stars']) }} <span>ดวง</span></p>
            <p class="field-hint">รางวัลจากบทเรียนที่ผ่านแล้ว</p>
        </section>
        <section class="profile-card stat-card xp-card" aria-labelledby="xp-label">
            <span class="stat-icon" aria-hidden="true">ϟ</span>
            <p id="xp-label" class="stat-label">ประสบการณ์</p>
            <p class="stat-value">{{ number_format($progress['xp']) }} <span>XP</span></p>
            <p class="field-hint">สะสมจากผลการเรียนของคุณ</p>
        </section>
    </div>

    <section class="profile-card learning-card" aria-labelledby="learning-title">
        <div class="section-heading">
            <div>
                <p class="eyebrow">เส้นทางการเรียนรู้</p>
                <h2 id="learning-title">ความคืบหน้าของฉัน</h2>
            </div>
            <span class="progress-pill">{{ $progress['completed'] }} / {{ $progress['total'] }} บทเรียน</span>
        </div>
        <div class="progress-caption">
            <span>บทเรียนที่เรียนจบแล้ว จากบทเรียนทั้งหมด</span>
            <strong>{{ $progress['percentage'] }}%</strong>
        </div>
        <progress class="profile-progress" aria-label="ความคืบหน้าบทเรียนทั้งหมด"
            value="{{ $progress['percentage'] }}" max="100">{{ $progress['percentage'] }}%</progress>
        @if($progress['latest']?->lesson)
            @php($latest = $progress['latest'])
            <div class="resume-card">
                <span class="resume-icon" aria-hidden="true">↗</span>
                <div class="resume-copy">
                    <p class="eyebrow">บทเรียนที่เปิดล่าสุด</p>
                    <h3>{{ $latest->lesson->title }}</h3>
                    <p class="field-hint">{{ $latest->lesson->unit?->course?->title }} · {{ $latest->lesson->unit?->title }}</p>
                    <p class="field-hint">ขั้นที่ {{ $latest->last_step }}@if($latest->completed_at) · เรียนจบแล้ว@endif</p>
                </div>
                <a class="profile-button primary-button" href="{{ route('lessons.learn.step', ['lesson' => $latest->lesson_id, 'step' => $latest->last_step]) }}">
                    {{ $latest->completed_at ? 'ทบทวนบทเรียน' : 'เรียนต่อ' }} <span aria-hidden="true">→</span>
                </a>
            </div>
        @else
            <div class="resume-card">
                <span class="resume-icon" aria-hidden="true">✦</span>
                <div class="resume-copy">
                    <h3>{{ $progress['total'] ? 'พร้อมเริ่มบทเรียนแรกหรือยัง?' : 'บทเรียนกำลังมา' }}</h3>
                    <p class="field-hint">{{ $progress['total'] ? 'เลือกบทเรียน แล้วกลับมาเรียนต่อได้จากหน้านี้' : 'กลับมาเลือกภาษาได้เมื่อมีบทเรียนพร้อมให้เรียน' }}</p>
                </div>
                <a class="profile-button primary-button" href="{{ url('/languages') }}">เลือกบทเรียน <span aria-hidden="true">→</span></a>
            </div>
        @endif
        @unless($progress['available'])
            <p class="field-hint progress-note">ขณะนี้ยังบันทึกความคืบหน้าไม่ได้ กรุณาลองใหม่ภายหลัง</p>
        @endunless
    </section>
    <p class="profile-footer"><span aria-hidden="true">✦</span> ทุกบทเรียนเล็ก ๆ คืออีกก้าวของคุณ</p>
</div>
@endsection

@push('scripts')
<script src="{{ asset('js/learner-profile.js') }}" defer></script>
@endpush
