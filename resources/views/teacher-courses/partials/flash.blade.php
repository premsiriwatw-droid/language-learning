@once
    @push('styles')
        <style>[x-cloak] { display: none !important; }</style>
    @endpush
@endonce
@if (session('success'))
    <div class="mb-5 rounded-xl border-2 border-emerald-200 bg-emerald-50 px-4 py-3 font-bold text-emerald-700" role="status">
        {{ session('success') }}
    </div>
@endif
@if ($errors->any())
    <div class="mb-5 rounded-xl border-2 border-red-200 bg-red-50 px-4 py-3 text-red-700" role="alert">
        @foreach ($errors->all() as $error)
            <p>{{ $error }}</p>
        @endforeach
    </div>
@endif
