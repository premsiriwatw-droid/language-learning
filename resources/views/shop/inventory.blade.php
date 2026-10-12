@extends('layouts.app')
@section('title', 'คลังไอเทม')
@section('shop-heading', 'เลือกสไตล์ที่เป็นคุณ')
@section('content')
<div class="reward-hub">
    @include('shop.heading')
    <div class="hub-grid">
        @forelse($items as $owned)
            @php($item = $owned->shopItem)
            <article class="hub-card">
                @include('shop.preview')
                <p class="hub-muted">{{ config('cosmetics.types')[$item->type] ?? $item->type }}</p>
                <h2>{{ $item->name }}</h2>
                @if(in_array($owned->id, $equippedIds, true))
                    <p class="hub-equipped">กำลังใช้อยู่</p>
                    <form action="{{ route('inventory.unequip', $item->type) }}" method="POST">
                        @csrf @method('DELETE')
                        <button class="hub-button hub-secondary">ถอด {{ $item->name }}</button>
                    </form>
                @else
                    <form action="{{ route('inventory.equip', $owned->id) }}" method="POST">
                        @csrf @method('PUT')
                        <button class="hub-button">ใส่ {{ $item->name }}</button>
                    </form>
                @endif
            </article>
        @empty
            <p class="hub-empty">คลังยังว่างอยู่ <a href="{{ route('shop.index') }}">เลือกไอเทมชิ้นแรกที่ร้านค้า</a></p>
        @endforelse
    </div>
    <section class="hub-section">
        <h2>ประวัติการซื้อ</h2>
        @forelse($purchases as $purchase)
            <p class="hub-history">{{ $purchase->shopItem->name }} · {{ number_format($purchase->price_stars) }} ★
                <time datetime="{{ $purchase->created_at->toIso8601String() }}">{{ $purchase->created_at->format('d/m/Y H:i') }}</time></p>
        @empty
            <p class="hub-muted">ยังไม่มีรายการซื้อ</p>
        @endforelse
        {{ $purchases->links() }}
    </section>
</div>
@endsection
