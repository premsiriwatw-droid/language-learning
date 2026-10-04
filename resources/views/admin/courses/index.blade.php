@extends('layouts.app')

@section('title', 'จัดการคอร์ส')

@section('content')
<div class="space-y-8">

    {{-- Breadcrumb --}}
    <div class="text-sm font-bold text-gray-400">
        <a href="{{ route('admin.languages.index') }}" class="hover:text-emerald-500">
            ภาษา
        </a>
        <span class="mx-1">/</span>
        <span class="text-gray-600">{{ $language->name }}</span>
    </div>


    {{-- Header --}}
    <div>
        <p class="text-sm font-bold text-emerald-500 uppercase tracking-wide">
            Admin · Learning Structure
        </p>

        <h1 class="text-3xl font-extrabold text-gray-800 mt-1">
            คอร์สของ {{ $language->name }}
        </h1>

        <p class="text-gray-500 mt-1">
            แต่ละคอร์สจะมี Unit และ Lesson อยู่ภายใต้ตัวมันเอง
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


    {{-- Add Course --}}
    <div class="bg-gray-50 border border-gray-200 rounded-xl p-5">

        <h3 class="font-extrabold text-gray-700 mb-4">
            เพิ่มคอร์สใหม่ใน {{ $language->name }}
        </h3>

        <form
            action="{{ route('admin.languages.courses.store', $language) }}"
            method="POST"
            class="flex flex-col md:flex-row gap-3"
        >
            @csrf

            <input
                type="text"
                name="title"
                value="{{ old('title') }}"
                required
                placeholder="เช่น Chinese Beginner"
                class="flex-1 border-2 border-gray-200 rounded-xl px-3 py-2 focus:outline-none focus:border-emerald-400"
            >

            <button
                type="submit"
                class="px-5 py-2.5 rounded-xl bg-emerald-500 text-white font-extrabold hover:bg-emerald-600 transition"
            >
                + เพิ่มคอร์ส
            </button>
        </form>
    </div>


    {{-- Course List --}}
    <div class="space-y-4">

        @forelse ($courses as $course)

            <div
                x-data="{ editing: false }"
                class="bg-white border-2 border-gray-200 rounded-2xl p-5"
            >

                <div x-show="!editing" class="flex items-center justify-between gap-4">

                    <div>
                        <h3 class="text-xl font-extrabold text-gray-800">
                            {{ $course->title }}
                        </h3>

                        <p class="text-sm text-gray-500 mt-1">
                            {{ $course->units_count }} Unit
                        </p>
                    </div>

                    <div class="flex items-center gap-2">

                        <a
                            href="{{ route('admin.courses.units.index', $course) }}"
                            class="px-3 py-1.5 rounded-lg bg-emerald-50 text-emerald-600 font-bold hover:bg-emerald-100"
                        >
                            จัดการ Unit
                        </a>

                        <button
                            type="button"
                            @click="editing = true"
                            class="px-3 py-1.5 rounded-lg bg-blue-50 text-blue-600 font-bold hover:bg-blue-100"
                        >
                            แก้ไข
                        </button>

                        <form
                            action="{{ route('admin.courses.destroy', $course) }}"
                            method="POST"
                            onsubmit="return confirm('ลบคอร์ส &quot;{{ $course->title }}&quot; พร้อมทั้ง {{ $course->units_count }} Unit ที่อยู่ภายใต้คอร์สนี้ (รวมถึง Lesson และเนื้อหาทั้งหมด)? ย้อนกลับไม่ได้')"
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


                {{-- Edit Course --}}
                <form
                    x-show="editing"
                    x-cloak
                    action="{{ route('admin.courses.update', $course) }}"
                    method="POST"
                    class="flex flex-col md:flex-row gap-3"
                >
                    @csrf
                    @method('PUT')

                    <input
                        type="text"
                        name="title"
                        value="{{ $course->title }}"
                        required
                        class="flex-1 border-2 border-gray-200 rounded-xl px-3 py-2"
                    >

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
                ยังไม่มีคอร์สในภาษานี้
            </div>

        @endforelse

    </div>

</div>
@endsection
