<a href="{{ route('shop.index') }}" @if(request()->routeIs('shop.*', 'inventory.*')) aria-current="page" @endif>ร้านค้า</a>
<a href="{{ route('notifications.index') }}" @if(request()->routeIs('notifications.*')) aria-current="page" @endif>
    แจ้งเตือน <span class="notification-count" aria-label="ยังไม่อ่าน">{{ auth()->user()->unreadNotifications()->count() }}</span>
</a>
