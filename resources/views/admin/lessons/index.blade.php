@extends('layouts.app')

@section('title', 'จัดการ Lesson')

@section('content')
<div class="space-y-8">

    {{-- Breadcrumb --}}
    <div class="text-sm font-bold text-gray-400">
        <a href="{{ route('admin.languages.index') }}" class="hover:text-emerald-500">
            ภาษา
        </a>
        <span class="mx-1">/</span>
        <a href="{{ route('admin.languages.courses.index', $unit->course->language) }}" class="hover:text-emerald-500">
            {{ $unit->course->language->name }}
        </a>
        <span class="mx-1">/</span>
        <a href="{{ route('admin.courses.units.index', $unit->course) }}" class="hover:text-emerald-500">
            {{ $unit->course->title }}
        </a>
        <span class="mx-1">/</span>
        <span class="text-gray-600">{{ $unit->title }}</span>
    </div>


    {{-- Header --}}
    <div>
        <p class="text-sm font-bold text-emerald-500 uppercase tracking-wide">
            Admin · Learning Structure
        </p>

        <h1 class="text-3xl font-extrabold text-gray-800 mt-1">
            Lesson ของ {{ $unit->title }}
        </h1>

        <p class="text-gray-500 mt-1">
            ลำดับด้านล่างคือลำดับที่ผู้เรียนจะเจอ Lesson เหล่านี้ ใช้ลูกศร ↑ ↓ เพื่อจัดลำดับใหม่
            คำศัพท์/แบบฝึกหัดของแต่ละ Lesson จัดการแยกในหน้า Content
        </p>
    </div>


    {{-- Success Message --}}
    @if (session('success'))
        <div class="bg-emerald-50 border-2 border-emerald-200 text-emerald-700 px-4 py-3 rounded-xl font-bold">
            {{ session('success') }}
        </div>
    @endif


    {{-- Validation Errors --}}
    @if ($errors->any())
        <div class="bg-red-50 border-2 border-red-200 text-red-700 px-4 py-4 rounded-xl">
            <p class="font-extrabold mb-2">
                กรุณาตรวจสอบข้อมูลด้านล่าง
            </p>

            <ul class="list-disc pl-5 space-y-1">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif


    {{-- Add Lesson --}}
    <div class="bg-gray-50 border border-gray-200 rounded-xl p-5">

        <h3 class="font-extrabold text-gray-700 mb-4">
            เพิ่ม Lesson ใหม่ใน {{ $unit->title }}
        </h3>

        <form
            action="{{ route('admin.units.lessons.store', $unit) }}"
            method="POST"
            class="space-y-3"
        >
            @csrf

            <input
                type="text"
                name="title"
                value="{{ old('title') }}"
                required
                placeholder="เช่น Greetings"
                class="w-full border-2 border-gray-200 rounded-xl px-3 py-2 focus:outline-none focus:border-emerald-400"
            >

            <textarea
                name="content"
                rows="2"
                placeholder="คำอธิบาย/สรุปเนื้อหาของ Lesson นี้ (ไม่บังคับ)"
                class="w-full border-2 border-gray-200 rounded-xl px-3 py-2 focus:outline-none focus:border-emerald-400"
            >{{ old('content') }}</textarea>

            <button
                type="submit"
                class="px-5 py-2.5 rounded-xl bg-emerald-500 text-white font-extrabold hover:bg-emerald-600 transition"
            >
                + เพิ่ม Lesson
            </button>
        </form>
    </div>


    {{-- Lesson List --}}
    @php
        $lessonIds = $lessons->pluck('id')->all();
    @endphp

    <div class="space-y-4">

        @forelse ($lessons as $index => $lesson)

            <div
                x-data="{ editing: false }"
                class="bg-white border-2 border-gray-200 rounded-2xl p-5"
            >

                <div x-show="!editing" class="flex items-center justify-between gap-4">

                    <div class="flex items-center gap-3">

                        {{-- Reorder --}}
                        <div class="flex flex-col gap-1">

                            @if ($index > 0)
                                @php
                                    $swapped = $lessonIds;
                                    [$swapped[$index - 1], $swapped[$index]] = [$swapped[$index], $swapped[$index - 1]];
                                @endphp
                                <form action="{{ route('admin.units.lessons.reorder', $unit) }}" method="POST">
                                    @csrf
                                    @method('PATCH')
                                    @foreach ($swapped as $id)
                                        <input type="hidden" name="order[]" value="{{ $id }}">
                                    @endforeach
                                    <button type="submit" title="เลื่อนขึ้น" class="w-7 h-7 rounded-md bg-gray-100 hover:bg-gray-200 text-gray-600 font-bold">
                                        ↑
                                    </button>
                                </form>
                            @else
                                <div class="w-7 h-7"></div>
                            @endif

                            @if (! $loop->last)
                                @php
                                    $swappedDown = $lessonIds;
                                    [$swappedDown[$index], $swappedDown[$index + 1]] = [$swappedDown[$index + 1], $swappedDown[$index]];
                                @endphp
                                <form action="{{ route('admin.units.lessons.reorder', $unit) }}" method="POST">
                                    @csrf
                                    @method('PATCH')
                                    @foreach ($swappedDown as $id)
                                        <input type="hidden" name="order[]" value="{{ $id }}">
                                    @endforeach
                                    <button type="submit" title="เลื่อนลง" class="w-7 h-7 rounded-md bg-gray-100 hover:bg-gray-200 text-gray-600 font-bold">
                                        ↓
                                    </button>
                                </form>
                            @else
                                <div class="w-7 h-7"></div>
                            @endif

                        </div>

                        <div>
                            <p class="text-xs font-bold text-gray-400">
                                ลำดับที่ {{ $index + 1 }}
                            </p>

                            <h3 class="text-xl font-extrabold text-gray-800">
                                {{ $lesson->title }}
                            </h3>

                            <p class="text-sm text-gray-500 mt-1">
                                {{ $lesson->vocabularies_count }} คำศัพท์ · {{ $lesson->exercises_count }} แบบฝึกหัด
                            </p>
                        </div>

                    </div>

                    <div class="flex items-center gap-2">

                        <a
                            href="{{ route('content.index', $lesson) }}"
                            class="px-3 py-1.5 rounded-lg bg-emerald-50 text-emerald-600 font-bold hover:bg-emerald-100"
                        >
                            จัดการเนื้อหา
                        </a>

                        <button
                            type="button"
                            @click="editing = true"
                            class="px-3 py-1.5 rounded-lg bg-blue-50 text-blue-600 font-bold hover:bg-blue-100"
                        >
                            แก้ไข
                        </button>

                        <form
                            action="{{ route('admin.lessons.destroy', $lesson) }}"
                            method="POST"
                            onsubmit="return confirm('ลบ Lesson &quot;{{ $lesson->title }}&quot; พร้อมทั้ง {{ $lesson->vocabularies_count }} คำศัพท์ และ {{ $lesson->exercises_count }} แบบฝึกหัดที่อยู่ภายใต้ Lesson นี้? ย้อนกลับไม่ได้')"
                        >
                            @csrf
                            @method('DELETE')

                            <button
                                type="submit"
                                class="px-3 py-1.5 rounded-lg bg-red-50 text-red-600 font-bold hover:bg-red-100"
                            >
                                ลบ
                            </button>
                        </form>

                    </div>

                </div>


                {{-- Edit Lesson --}}
                <form
                    x-show="editing"
                    x-cloak
                    action="{{ route('admin.lessons.update', $lesson) }}"
                    method="POST"
                    class="space-y-3"
                >
                    @csrf
                    @method('PUT')

                    <input
                        type="text"
                        name="title"
                        value="{{ $lesson->title }}"
                        required
                        class="w-full border-2 border-gray-200 rounded-xl px-3 py-2"
                    >

                    <textarea
                        name="content"
                        rows="2"
                        class="w-full border-2 border-gray-200 rounded-xl px-3 py-2"
                    >{{ $lesson->content }}</textarea>

                    <div class="flex gap-2">

                        <button
                            type="submit"
                            class="px-4 py-2 bg-emerald-500 text-white rounded-lg font-bold"
                        >
                            บันทึก
                        </button>

                        <button
                            type="button"
                            @click="editing = false"
                            class="px-4 py-2 bg-gray-100 text-gray-600 rounded-lg font-bold"
                        >
                            ยกเลิก
                        </button>

                    </div>
                </form>

            </div>

        @empty

            <div class="text-center text-gray-400 py-8">
                ยังไม่มี Lesson ใน Unit นี้
            </div>

        @endforelse

    </div>

</div>
@endsection
