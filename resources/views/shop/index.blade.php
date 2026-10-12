@extends('layouts.app')
@section('title', 'ร้านค้าดาว')
@section('shop-heading', 'เปลี่ยนดาวเป็นสไตล์ของคุณ')
@section('content')
<div class="reward-hub">
    @include('shop.heading')
    <p class="hub-muted">ของตกแต่งถาวร ซื้อแต่ละชิ้นครั้งเดียว และใส่ได้ประเภทละหนึ่งชิ้น</p>
    @forelse($items->groupBy('type') as $type => $group)
        <section class="hub-section" aria-labelledby="category-{{ $type }}">
            <h2 id="category-{{ $type }}">{{ config('cosmetics.types')[$type] ?? $type }}</h2>
            <div class="hub-grid">
                @foreach($group as $item)
                    <article class="hub-card">
                        @include('shop.preview')
                        <h3>{{ $item->name }}</h3>
                        <p>{{ number_format($item->price_stars) }} ★</p>
                        @if(in_array($item->id, $ownedIds, true))
                            <a class="hub-button hub-secondary" href="{{ route('inventory.index') }}">มีแล้ว · ไปคลัง</a>
                        @else
                            <form action="{{ route('shop.purchase', $item->id) }}" method="POST">
                                @csrf
                                <button class="hub-button" @disabled($wallet['balance'] < $item->price_stars)>
                                    {{ $wallet['balance'] < $item->price_stars ? 'ดาวยังไม่พอ' : 'ซื้อ '.$item->name }}</button>
                            </form>
                        @endif
                    </article>
                @endforeach
            </div>
        </section>
    @empty
        <p class="hub-empty">ร้านค้ากำลังเตรียมสินค้าใหม่ กลับมาอีกครั้งนะ</p>
    @endforelse
</div>
@endsection
