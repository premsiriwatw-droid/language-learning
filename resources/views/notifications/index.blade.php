@extends('layouts.app')
@section('title', 'การแจ้งเตือน')
@section('content')
<div class="reward-hub">
    <header class="hub-heading"><p class="hub-eyebrow">ติดตามทุกก้าวของคุณ</p><h1>การแจ้งเตือน</h1>
        <p>ยังไม่อ่าน {{ $unreadCount }} รายการ</p>
    </header>
    @if(session('status'))<p class="hub-notice" role="status">{{ session('status') }}</p>@endif
    <nav class="hub-tabs" aria-label="กรองการแจ้งเตือน">
        <a href="{{ route('notifications.index') }}" @if($filter === 'all') aria-current="page" @endif>ทั้งหมด</a>
        <a href="{{ route('notifications.index', ['filter' => 'unread']) }}" @if($filter === 'unread') aria-current="page" @endif>ยังไม่อ่าน</a>
    </nav>
    @if($unreadCount > 0)
        <form action="{{ route('notifications.read-all') }}" method="POST">@csrf @method('PATCH')
            <button class="hub-button hub-secondary">อ่านทั้งหมดแล้ว</button>
        </form>
    @endif
    <div class="hub-section">
        @forelse($notifications as $notification)
            <article class="hub-card notification-card {{ $notification->read_at === null ? 'notification-unread' : '' }}">
                <p class="hub-muted">{{ $notification->read_at === null ? 'ยังไม่อ่าน' : 'อ่านแล้ว' }} ·
                    <time datetime="{{ $notification->created_at->toIso8601String() }}">{{ $notification->created_at->format('d/m/Y H:i') }}</time></p>
                <h2>{{ $notification->data['title'] }}</h2><p>{{ $notification->data['body'] }}</p>
                <div class="hub-actions">
                    <form method="POST" action="{{ route('notifications.open', $notification->id) }}">
                        @csrf @method('PATCH')
                        <button type="submit" class="hub-button hub-secondary">ดูรายละเอียด</button>
                    </form>
                    <form method="POST" action="{{ route($notification->read_at === null ? 'notifications.read' : 'notifications.unread', $notification->id) }}">
                        @csrf @method('PATCH')
                        <button class="hub-button hub-secondary">{{ $notification->read_at === null ? 'อ่านแล้ว' : 'ทำเครื่องหมายว่ายังไม่อ่าน' }}</button>
                    </form>
                </div>
            </article>
        @empty
            <p class="hub-empty">{{ $filter === 'unread' ? 'อ่านครบแล้ว ไม่มีการแจ้งเตือนใหม่' : 'ยังไม่มีการแจ้งเตือน เมื่อมีกิจกรรมสำคัญจะแสดงที่นี่' }}</p>
        @endforelse
    </div>
    {{ $notifications->links() }}
</div>
@endsection
