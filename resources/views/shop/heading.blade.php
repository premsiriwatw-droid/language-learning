<header class="hub-heading">
    <p class="hub-eyebrow">รางวัลสำหรับความตั้งใจของคุณ</p>
    <h1>@yield('shop-heading')</h1>
    <p>ดาวพร้อมใช้ <strong data-testid="star-balance">{{ number_format($wallet['balance']) }} ★</strong></p>
    <p class="hub-muted">ได้รับทั้งหมด {{ number_format($wallet['earned']) }} · ใช้ไป {{ number_format($wallet['spent']) }} ดวง</p>
    <nav class="hub-tabs" aria-label="ร้านค้าและคลัง">
        <a href="{{ route('shop.index') }}" @if(request()->routeIs('shop.index')) aria-current="page" @endif>ร้านค้า</a>
        <a href="{{ route('inventory.index') }}" @if(request()->routeIs('inventory.index')) aria-current="page" @endif>คลังของฉัน</a>
        <a href="{{ route('profile') }}">ดูโปรไฟล์</a>
    </nav>
</header>
@if(session('status'))<p class="hub-notice" role="status">{{ session('status') }}</p>@endif
@if($errors->any())<div class="hub-error" role="alert">{{ $errors->first() }}</div>@endif
