<div class="cosmetic-preview {{ $item->type !== 'sticker' ? $item->appearance() : '' }}" aria-label="ตัวอย่าง {{ $item->name }}">
    @if($item->type === 'sticker')
        <span aria-hidden="true">{{ $item->appearance() }}</span>
    @elseif($item->type === 'streak_style')
        <span>7 วัน</span>
    @else
        <span>LangLearn</span>
    @endif
</div>
