@extends('layouts.app')
@section('title', 'ภารกิจประจำวัน')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/daily-quests.css') }}">
@endpush

@section('content')
<div class="learner-profile daily-quests-page">
    <a class="back-link" href="{{ route('profile') }}"><span aria-hidden="true">←</span> กลับโปรไฟล์</a>
    <header class="profile-heading daily-quests-heading">
        <div>
            <h1>ภารกิจประจำวัน<span class="heading-dot">.</span></h1>
            <p class="muted">ทำภารกิจวันนี้เพื่อรับดาวสะสม</p>
        </div>
        <div class="daily-quests-summary" aria-label="ภารกิจที่สำเร็จวันนี้">
            <p class="stat-label">สำเร็จวันนี้</p>
            <p class="stat-value">{{ $quests->whereNotNull('completed_at')->count() }} <span>/ {{ $quests->count() }}</span></p>
        </div>
    </header>

    @if(session('status'))
        <div class="profile-notice success-notice" role="status">{{ session('status') }}</div>
    @endif
    @error('quest')
        <div class="profile-notice error-notice" role="alert">{{ $message }}</div>
    @enderror

    @php
        $labels = [
            'complete_lesson' => 'เรียนจบ',
            'correct_answers' => 'ตอบคำถามถูกในครั้งแรก',
            'review_mistakes' => 'ทบทวนข้อที่เคยตอบผิด',
        ];
        $icons = [
            'complete_lesson' => '✓',
            'correct_answers' => 'ϟ',
            'review_mistakes' => '↻',
        ];
    @endphp
    <div class="daily-quests-list">
        @foreach($quests as $quest)
            @php
                $state = $quest->claimed_at ? 'claimed' : ($quest->completed_at ? 'completed' : 'in-progress');
                $title = $quest->type === 'complete_lesson'
                    ? $labels[$quest->type].' '.$quest->target.' บท'
                    : $labels[$quest->type];
                $description = match ($quest->type) {
                    'complete_lesson' => 'เรียนบทเรียนใดก็ได้ให้สำเร็จในวันนี้',
                    'correct_answers' => 'ตอบถูกครั้งแรกให้ครบ '.$quest->target.' ข้อ',
                    'review_mistakes' => 'กลับไปแก้ข้อผิดให้ถูก '.$quest->target.' ข้อ',
                };
                $percent = $quest->target > 0
                    ? max(0, min(100, (int) round($quest->progress / $quest->target * 100)))
                    : 0;
            @endphp
            <section class="profile-card quest-card xp-card quest-card--{{ $state }}" aria-labelledby="quest-title-{{ $quest->id }}">
                <div class="quest-card-top">
                    <div class="quest-heading">
                        <span class="stat-icon quest-icon" aria-hidden="true">{{ $icons[$quest->type] }}</span>
                        <div class="quest-copy">
                            <h2 id="quest-title-{{ $quest->id }}">{{ $title }}</h2>
                            <p class="field-hint quest-description">{{ $description }}</p>
                        </div>
                    </div>
                    <span class="quest-reward"><span aria-hidden="true">★</span> {{ $quest->reward_stars }} ดาว</span>
                </div>
                <div class="quest-progress">
                    <div class="progress-caption">
                        <strong>{{ $quest->progress }} / {{ $quest->target }}</strong>
                        <span>{{ $percent }}%</span>
                    </div>
                    <progress class="profile-progress" value="{{ $percent }}" max="100"
                        aria-label="ความคืบหน้า {{ $title }}">{{ $quest->progress }} / {{ $quest->target }}</progress>
                </div>
                <div class="quest-footer">
                    @if($quest->claimed_at)
                        <span class="quest-status quest-status--claimed"><span aria-hidden="true">✓</span> รับรางวัลแล้ว</span>
                    @elseif($quest->completed_at)
                        <span class="quest-status quest-status--completed"><span aria-hidden="true">✓</span> สำเร็จแล้ว</span>
                        <form class="quest-claim-form" method="POST" action="{{ route('daily-quests.claim', ['questId' => $quest->id]) }}">
                            @csrf
                            <button class="profile-button primary-button" type="submit">รับรางวัล <span aria-hidden="true">★</span></button>
                        </form>
                    @else
                        <span class="quest-status quest-status--in-progress">กำลังดำเนินการ</span>
                    @endif
                </div>
            </section>
        @endforeach
    </div>
    <p class="field-hint daily-quests-helper">บทเรียนและคำถามเดิมนับวันละครั้ง ภารกิจเริ่มใหม่วันถัดไป</p>
</div>
@endsection
