@extends('layouts.app')

@section('title', 'เนื้อหาบทเรียน')

@section('content')

<div class="mb-8">
    <h1 class="text-3xl font-extrabold text-gray-800">
        {{ $lesson->title ?? $lesson->name ?? ('Lesson ' . $lesson->id) }}
    </h1>

    <p class="text-gray-500 mt-2">
        เรียนคำศัพท์ก่อน แล้วค่อยทำแบบฝึกหัด
    </p>
</div>


{{-- Vocabulary --}}
<div class="mb-10">
    <h2 class="text-2xl font-bold text-gray-800 mb-4">
        คำศัพท์
    </h2>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

        @forelse($lesson->vocabularies as $vocabulary)

            <div class="bg-white border-2 border-gray-200 border-b-4 rounded-2xl p-5">

                <div class="text-2xl font-extrabold text-emerald-500">
                    {{ $vocabulary->word }}
                </div>

                @if($vocabulary->pinyin)
                    <div class="text-gray-500 mt-1">
                        {{ $vocabulary->pinyin }}
                    </div>
                @endif

                <div class="text-gray-800 font-bold mt-2">
                    {{ $vocabulary->meaning }}
                </div>

                @if($vocabulary->example_sentence)
                    <div class="mt-4 border-t pt-3 text-sm text-gray-600">

                        <div>
                            {{ $vocabulary->example_sentence }}
                        </div>

                        @if($vocabulary->example_pinyin)
                            <div class="text-gray-400">
                                {{ $vocabulary->example_pinyin }}
                            </div>
                        @endif

                        @if($vocabulary->example_meaning)
                            <div class="text-gray-500">
                                {{ $vocabulary->example_meaning }}
                            </div>
                        @endif

                    </div>
                @endif

            </div>

        @empty
            <div class="text-gray-500">
                ยังไม่มีคำศัพท์ในบทเรียนนี้
            </div>
        @endforelse

    </div>
</div>


{{-- Exercises --}}
<div>
    <h2 class="text-2xl font-bold text-gray-800 mb-4">
        แบบฝึกหัด
    </h2>

    <div class="flex flex-col gap-4">

        @forelse($lesson->exercises as $exercise)

            <div class="bg-white border-2 border-gray-200 border-b-4 rounded-2xl p-5 flex items-center justify-between">

                <div>
                    <h3 class="font-bold text-gray-800 text-lg">
                        {{ $exercise->title }}
                    </h3>

                    <p class="text-sm text-gray-500 mt-1">
                        ประเภท: {{ $exercise->type }}
                    </p>
                </div>

                <a href="{{ route('quiz.show', $exercise) }}"
                   class="bg-emerald-500 hover:bg-emerald-600 text-white font-bold px-6 py-3 rounded-xl border-b-4 border-emerald-600 active:border-b-0">
                    ทำแบบฝึกหัด
                </a>

            </div>

        @empty
            <div class="text-gray-500">
                ยังไม่มีแบบฝึกหัดในบทเรียนนี้
            </div>
        @endforelse

    </div>
</div>

@endsection