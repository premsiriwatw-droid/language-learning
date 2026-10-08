@extends('layouts.app')

@section('title', 'เลือกภาษาที่ต้องการเรียน')
@section('body-class', 'language-selection-page')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/languages.css') }}">
@endpush

@section('content')
@php
    $availableLanguages = $languages->filter(fn ($language) => $language->courses->isNotEmpty());
@endphp
<section class="language-world" aria-labelledby="language-heading" data-language-world>
    <div class="language-scenery" aria-hidden="true">
        <img class="flags-backdrop" src="{{ asset('images/languages/flags-backdrop.png') }}" alt="" width="1672" height="941" fetchpriority="high">
        <div class="scenery-wash"></div>
        <span class="scene-word scene-word-china">你好</span>
        <span class="scene-word scene-word-england">Hello.</span>
    </div>
    <div class="language-content">
        <p class="language-eyebrow"><span aria-hidden="true"></span> Developed By ทีมงานช่างเจ็ค</p>
        <h1 id="language-heading">อยากเริ่มเรียน<br><span>ภาษาไหนวันนี้?</span></h1>
        <p class="language-intro">เลือกภาษาที่คุณสนใจ แล้วลุยไปด้วยกัน</p>
        <div class="language-picker">
            <div class="picker-heading">
                <h2>เลือกภาษาของคุณ</h2>
                <span>{{ $availableLanguages->count() }} ภาษาพร้อมเรียน</span>
            </div>
            <div class="language-options">
                @forelse($availableLanguages as $language)
                    @php
                        $course = $language->courses->first();
                        $languageName = mb_strtolower(trim($language->name));
                        $isChinese = in_array($languageName, ['chinese', 'mandarin', 'จีน', 'ภาษาจีน', '中文'], true);
                        $isEnglish = in_array($languageName, ['english', 'อังกฤษ', 'ภาษาอังกฤษ'], true);
                    @endphp
                    <a href="{{ url('/courses/' . $course->id . '/units') }}" class="language-choice {{ $isEnglish ? 'language-choice-english' : '' }}" aria-label="เริ่มเรียน {{ $language->name }}">
                        <span class="language-symbol" aria-hidden="true">{{ $isChinese ? '中' : ($isEnglish ? 'En' : mb_substr($language->name, 0, 2)) }}</span>
                        <span class="language-choice-copy">
                            <strong>{{ $isChinese ? 'ภาษาจีน' : ($isEnglish ? 'ภาษาอังกฤษ' : $language->name) }}</strong>
                            <span>{{ $language->name }}</span>
                        </span>
                        <span class="language-choice-action">เริ่มเรียน <span aria-hidden="true">↗</span></span>
                    </a>
                @empty
                    <p class="language-empty">ยังไม่มีภาษาที่พร้อมเรียนในระบบ<br><span>เมื่อมีคอร์สพร้อมแล้ว ภาษาจะปรากฏที่นี่</span></p>
                @endforelse
            </div>
            <p class="picker-note"><span aria-hidden="true">✦</span> เตรียมตัวออกเดินทางได้</p>
        </div>
        <p class="language-signature">สองวัฒนธรรม <span aria-hidden="true">/</span> โลกแห่งการเรียนรู้ใบเดียวกัน</p>
    </div>
    <button class="motion-control" type="button" data-motion-toggle aria-pressed="false" hidden>
        <span data-motion-icon aria-hidden="true">Ⅱ</span>
        <span data-motion-label>หยุดภาพเคลื่อนไหว</span>
    </button>
</section>
@endsection

@push('scripts')
<script src="{{ asset('js/languages.js') }}" defer></script>
@endpush
