@extends('layouts.app')

@section('title', 'เลือกภาษาที่ต้องการเรียน')

@section('content')
<div class="text-center mb-8">
    <h1 class="text-3xl font-extrabold text-gray-800">
        อยากเริ่มเรียนภาษาไหนวันนี้?
    </h1>

    <p class="text-gray-500 mt-2">
        เลือกภาษาที่คุณสนใจเพื่อเริ่มต้นการเรียน
    </p>
</div>

<div class="grid grid-cols-2 md:grid-cols-3 gap-6 max-w-2xl mx-auto">
    @forelse($languages as $language)

        @php
            $course = $language->courses->first();
        @endphp

        @if($course)
            <a href="{{ url('/courses/' . $course->id . '/units') }}"
               class="block bg-white border-2 border-gray-200 border-b-4 rounded-2xl p-6 text-center cursor-pointer transition-all hover:-translate-y-1 hover:border-emerald-400 hover:bg-emerald-50 active:translate-y-0 active:border-b-2">

                <div class="text-2xl font-black text-gray-700 mb-4">
                    {{ $language->name }}
                </div>

                <div class="inline-block bg-emerald-500 text-white font-bold py-2 px-4 rounded-xl text-sm w-full">
                    เริ่มเรียน
                </div>
            </a>
        @endif

    @empty
        <p class="col-span-full text-center text-gray-500">
            ยังไม่มีภาษาในระบบ
        </p>
    @endforelse
</div>
@endsection