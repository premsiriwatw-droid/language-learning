@extends('layouts.app')
@section('title', 'ภารกิจประจำวัน')

@section('content')
<div class="learner-profile">
    <a class="back-link" href="{{ route('profile') }}">← กลับโปรไฟล์</a>
    <h1>ภารกิจประจำวัน</h1>
    <p class="muted">{{ now()->format('Y-m-d') }} · ทำภารกิจวันนี้แล้วรับดาวสะสม</p>

    @if(session('status'))
        <div class="profile-notice success-notice" role="status">{{ session('status') }}</div>
    @endif
    @error('quest')
        <div class="profile-notice error-notice" role="alert">{{ $message }}</div>
    @enderror

    @php
        $labels = [
            'complete_lesson' => 'เรียนบทเรียนให้จบ',
            'correct_answers' => 'ตอบคำถามถูกในครั้งแรก',
            'review_mistakes' => 'ทบทวนข้อที่เคยตอบผิดจนตอบถูก',
        ];
    @endphp
    @foreach($quests as $quest)
        <section class="profile-card">
            <h2>{{ $labels[$quest->type] }}</h2>
            <p>{{ $quest->progress }} / {{ $quest->target }} · รางวัล {{ $quest->reward_stars }} ดาว</p>
            <progress class="profile-progress" value="{{ $quest->progress }}" max="{{ $quest->target }}"
                aria-label="ความคืบหน้า {{ $labels[$quest->type] }}">{{ $quest->progress }} / {{ $quest->target }}</progress>
            @if($quest->claimed_at)
                <p>รับรางวัลแล้ว</p>
            @elseif($quest->completed_at)
                <p>สำเร็จแล้ว</p>
                <form method="POST" action="{{ route('daily-quests.claim', ['questId' => $quest->id]) }}">
                    @csrf
                    <button class="profile-button" type="submit">รับรางวัล</button>
                </form>
            @else
                <p>กำลังดำเนินการ</p>
            @endif
        </section>
    @endforeach
    <p class="field-hint">คำถามและบทเรียนเดิมนับได้ครั้งเดียวต่อวัน ภารกิจเริ่มใหม่ในวันถัดไป</p>
</div>
@endsection
