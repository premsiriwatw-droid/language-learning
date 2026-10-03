@extends('layouts.app')

@section('title', 'ยืนยันการลบ'.$kind)

@section('content')
<div class="space-y-6">

    <div>
        <p class="text-sm font-bold text-red-500 uppercase tracking-wide">
            Admin · ยืนยันการลบ
        </p>

        <h1 class="text-3xl font-extrabold text-gray-800 mt-1">
            ลบ{{ $kind }} "{{ $name }}"?
        </h1>

        <p class="text-gray-500 mt-1">
            การลบย้อนกลับไม่ได้
        </p>
    </div>


    <div class="bg-red-50 border-2 border-red-200 rounded-2xl p-5">

        @if (empty($impact))
            <p class="font-bold text-red-700">
                รายการนี้ไม่มีข้อมูลอื่นผูกอยู่ จะลบเฉพาะ{{ $kind }}นี้เท่านั้น
            </p>
        @else
            <p class="font-extrabold text-red-700 mb-3">
                ข้อมูลต่อไปนี้จะถูกลบตามไปด้วยทั้งหมด:
            </p>

            <ul class="space-y-1 text-red-700">
                @foreach ($impact as $label => $count)
                    <li class="flex justify-between max-w-sm">
                        <span>{{ $label }}</span>
                        <strong>{{ number_format($count) }}</strong>
                    </li>
                @endforeach
            </ul>
        @endif

    </div>


    <form
        action="{{ $action }}"
        method="POST"
        class="bg-white border-2 border-gray-200 rounded-2xl p-5 space-y-4"
    >
        @csrf
        @method('DELETE')

        <label for="confirm_name" class="block font-bold text-gray-700">
            พิมพ์ <span class="font-extrabold text-red-600">{{ $name }}</span> เพื่อยืนยัน
        </label>

        <input
            id="confirm_name"
            type="text"
            name="confirm_name"
            required
            autocomplete="off"
            class="w-full border-2 border-gray-200 rounded-xl px-3 py-2 focus:outline-none focus:border-red-400"
        >

        @error('confirm_name')
            <p class="text-red-600 font-bold">{{ $message }}</p>
        @enderror

        <div class="flex gap-2">
            <button
                type="submit"
                class="px-5 py-2.5 rounded-xl bg-red-500 text-white font-extrabold hover:bg-red-600 transition"
            >
                ลบถาวร
            </button>

            <a
                href="{{ $cancelUrl }}"
                class="px-5 py-2.5 rounded-xl bg-gray-100 text-gray-600 font-extrabold hover:bg-gray-200 transition"
            >
                ยกเลิก
            </a>
        </div>
    </form>

</div>
@endsection
