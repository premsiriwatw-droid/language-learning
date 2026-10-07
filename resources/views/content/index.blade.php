@extends('layouts.app')

@section('title', 'Content Management')

@section('content')
@php
    $inputClass = 'w-full rounded-lg border-2 border-gray-200 bg-white px-3 py-2';
    $saveClass = 'rounded-lg bg-emerald-500 px-4 py-2 font-bold text-white hover:bg-emerald-600';
    $deleteClass = 'rounded-lg bg-red-50 px-3 py-2 font-bold text-red-600';

    $vocabularyFields = [
        'word' => 'คำศัพท์',
        'pinyin' => 'Pinyin',
        'meaning' => 'ความหมาย',
        'example_sentence' => 'ประโยคตัวอย่าง',
        'example_pinyin' => 'Pinyin ของประโยค',
        'example_meaning' => 'ความหมายของประโยค',
    ];

    $exerciseTypes = [
        'multiple_choice' => 'Multiple Choice',
        'fill_blank' => 'Fill Blank',
        'listening' => 'Listening',
        'image_choice' => 'Image Choice',
        'translation' => 'Translation',
        'arrange_words' => 'Arrange Words',
        'custom' => 'Custom',
    ];

    $allQuestions = $lesson->exercises->flatMap(
        fn ($exercise) => $exercise->questions
    );

    $pendingCount = $allQuestions->filter(
        fn ($question) => !in_array(
            $question->vocabulary_mode,
            ['after_vocabulary', 'lesson_end'],
            true
        ) || (
            $question->vocabulary_mode === 'after_vocabulary'
            && $question->vocabularies->isEmpty()
        )
    )->count();
@endphp

<div class="space-y-8">
    <div class="flex flex-wrap items-center justify-between gap-4">
        <div>
            <p class="font-bold text-emerald-600">Content Management</p>
            <h1 class="mt-1 text-3xl font-extrabold text-gray-800">
                {{ $lesson->title }}
            </h1>
            <p class="mt-2 text-gray-500">
                จัดการคำศัพท์ แบบฝึกหัด คำถาม และคำตอบ
            </p>
        </div>

        <a
            href="{{ route('lessons.show', $lesson) }}"
            class="rounded-xl border-2 border-gray-200 px-4 py-2 font-bold"
        >
            กลับหน้าบทเรียน
        </a>
    </div>

    @if (session('success'))
        <div class="rounded-xl bg-emerald-50 p-4 font-bold text-emerald-700">
            {{ session('success') }}
        </div>
    @endif

    @if ($errors->any())
        <div role="alert" class="rounded-xl bg-red-50 p-4 text-red-700">
            <p class="mb-2 font-bold">กรุณาตรวจสอบข้อมูล</p>
            <ul class="list-disc pl-5">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @if ($pendingCount > 0)
        <div class="rounded-xl border-2 border-amber-200 bg-amber-50 p-4 text-amber-800">
            <p class="font-bold">
                มี {{ $pendingCount }} คำถามที่ยังรอจัดความสัมพันธ์
            </p>
            <p class="mt-1 text-sm">
                เปิดแก้ไขคำถาม แล้วเลือกคำศัพท์ที่ต้องเรียนก่อน
                หรือกำหนดให้เป็นทบทวนท้ายบท
            </p>
        </div>
    @endif

    {{-- Vocabulary --}}
    <section class="rounded-2xl border-2 border-gray-200 bg-white p-5 space-y-5">
        <h2 class="text-2xl font-extrabold text-gray-800">
            คำศัพท์ {{ $lesson->vocabularies->count() }} คำ
        </h2>

        <details class="rounded-xl bg-gray-50 p-4">
            <summary class="cursor-pointer font-bold text-emerald-700">
                + เพิ่มคำศัพท์
            </summary>

            <form
                action="{{ route('content.vocabularies.store', $lesson) }}"
                method="POST"
                class="mt-4 space-y-3"
            >
                @csrf

                <div class="grid gap-3 md:grid-cols-2">
                    @foreach ($vocabularyFields as $field => $label)
                        <label class="block text-sm font-bold text-gray-600">
                            {{ $label }}
                            @if (in_array($field, ['word', 'meaning'], true))
                                *
                            @endif
                            <input
                                type="text"
                                name="{{ $field }}"
                                @required(in_array($field, ['word', 'meaning'], true))
                                class="{{ $inputClass }}"
                            >
                        </label>
                    @endforeach
                </div>

                <button type="submit" class="{{ $saveClass }}">
                    เพิ่มคำศัพท์
                </button>
            </form>
        </details>

        @forelse ($lesson->vocabularies->sortBy('id') as $vocabulary)
            <div class="rounded-xl border-2 border-gray-100 p-4 space-y-3">
                <div>
                    <p class="text-xl font-bold text-gray-800">
                        {{ $vocabulary->word }}
                        <span class="text-sm text-emerald-600">
                            {{ $vocabulary->pinyin }}
                        </span>
                    </p>
                    <p class="text-gray-600">{{ $vocabulary->meaning }}</p>

                    @if ($vocabulary->example_sentence)
                        <div class="mt-2 rounded-lg bg-gray-50 p-3">
                            <p>{{ $vocabulary->example_sentence }}</p>
                            <p class="text-sm text-emerald-600">
                                {{ $vocabulary->example_pinyin }}
                            </p>
                            <p class="text-sm text-gray-500">
                                {{ $vocabulary->example_meaning }}
                            </p>
                        </div>
                    @endif
                </div>

                <details>
                    <summary class="cursor-pointer font-bold text-blue-600">
                        แก้ไขคำศัพท์
                    </summary>

                    <form
                        action="{{ route('content.vocabularies.update', $vocabulary) }}"
                        method="POST"
                        class="mt-3 space-y-3"
                    >
                        @csrf
                        @method('PUT')

                        <div class="grid gap-3 md:grid-cols-2">
                            @foreach ($vocabularyFields as $field => $label)
                                <label class="block text-sm font-bold text-gray-600">
                                    {{ $label }}
                                    <input
                                        type="text"
                                        name="{{ $field }}"
                                        value="{{ $vocabulary->{$field} }}"
                                        @required(in_array($field, ['word', 'meaning'], true))
                                        class="{{ $inputClass }}"
                                    >
                                </label>
                            @endforeach
                        </div>

                        <button type="submit" class="{{ $saveClass }}">
                            บันทึกคำศัพท์
                        </button>
                    </form>
                </details>

                <form
                    action="{{ route('content.vocabularies.destroy', $vocabulary) }}"
                    method="POST"
                    onsubmit="return confirm('ยืนยันลบคำศัพท์นี้?')"
                >
                    @csrf
                    @method('DELETE')

                    <button type="submit" class="{{ $deleteClass }}">
                        ลบคำศัพท์
                    </button>
                </form>
            </div>
        @empty
            <p class="py-4 text-center text-gray-400">ยังไม่มีคำศัพท์</p>
        @endforelse
    </section>

    {{-- Exercises --}}
    <section class="rounded-2xl border-2 border-gray-200 bg-white p-5 space-y-5">
        <h2 class="text-2xl font-extrabold text-gray-800">
            แบบฝึกหัด {{ $lesson->exercises->count() }} รายการ
        </h2>

        <form
            action="{{ route('content.exercises.store', $lesson) }}"
            method="POST"
            class="rounded-xl bg-gray-50 p-4 space-y-3"
        >
            @csrf

            <h3 class="font-bold text-gray-700">เพิ่มแบบฝึกหัด</h3>

            <label class="block text-sm font-bold text-gray-600">
                ประเภท
                <select name="type" required class="{{ $inputClass }}">
                    @foreach ($exerciseTypes as $value => $label)
                        <option value="{{ $value }}">{{ $label }}</option>
                    @endforeach
                </select>
            </label>

            <label class="block text-sm font-bold text-gray-600">
                ชื่อแบบฝึกหัด
                <input type="text" name="title" required class="{{ $inputClass }}">
            </label>

            <button type="submit" class="{{ $saveClass }}">
                เพิ่มแบบฝึกหัด
            </button>
        </form>

        @forelse ($lesson->exercises as $exercise)
            <div class="rounded-2xl border-2 border-gray-200 p-4 space-y-5">
                <div>
                    <p class="text-sm font-bold text-blue-600">
                        {{ $exercise->type }}
                    </p>
                    <h3 class="text-xl font-extrabold text-gray-800">
                        {{ $exercise->title }}
                    </h3>
                </div>

                <details>
                    <summary class="cursor-pointer font-bold text-blue-600">
                        แก้ไขแบบฝึกหัด
                    </summary>

                    <form
                        action="{{ route('content.exercises.update', $exercise) }}"
                        method="POST"
                        class="mt-3 space-y-3"
                    >
                        @csrf
                        @method('PUT')

                        <select name="type" required class="{{ $inputClass }}">
                            @foreach ($exerciseTypes as $value => $label)
                                <option value="{{ $value }}" @selected($exercise->type === $value)>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>

                        <input
                            type="text"
                            name="title"
                            value="{{ $exercise->title }}"
                            required
                            aria-label="ชื่อแบบฝึกหัด"
                            class="{{ $inputClass }}"
                        >

                        <button type="submit" class="{{ $saveClass }}">
                            บันทึกแบบฝึกหัด
                        </button>
                    </form>
                </details>

                <form
                    action="{{ route('content.exercises.destroy', $exercise) }}"
                    method="POST"
                    onsubmit="return confirm('ลบแบบฝึกหัด รวมถึงคำถามและคำตอบทั้งหมด?')"
                >
                    @csrf
                    @method('DELETE')

                    <button type="submit" class="{{ $deleteClass }}">
                        ลบแบบฝึกหัด
                    </button>
                </form>

                {{-- Add Question --}}
                @php
                    $addFormKey = 'add-question-' . $exercise->id;
                    $restoreAdd = old('question_form') === $addFormKey;
                @endphp

                <details
                    @if ($restoreAdd) open @endif
                    class="rounded-xl bg-gray-50 p-4"
                >
                    <summary class="cursor-pointer font-bold text-blue-600">
                        + เพิ่มคำถาม
                    </summary>

                    <form
                        action="{{ route('content.questions.store', $exercise) }}"
                        method="POST"
                        class="mt-4 space-y-3"
                    >
                        @csrf

                        <label class="block text-sm font-bold text-gray-600">
                            คำถาม *
                            <textarea
                                name="question"
                                rows="2"
                                required
                                class="{{ $inputClass }}"
                            >{{ $restoreAdd ? old('question') : '' }}</textarea>
                        </label>

                        <label class="block text-sm font-bold text-gray-600">
                            คำอธิบาย
                            <textarea
                                name="explanation"
                                rows="2"
                                class="{{ $inputClass }}"
                            >{{ $restoreAdd ? old('explanation') : '' }}</textarea>
                        </label>

                        @foreach (['audio_path' => 'Audio path', 'image_path' => 'Image path'] as $field => $label)
                            <label class="block text-sm font-bold text-gray-600">
                                {{ $label }}
                                <input
                                    type="text"
                                    name="{{ $field }}"
                                    value="{{ $restoreAdd ? old($field) : '' }}"
                                    class="{{ $inputClass }}"
                                >
                            </label>
                        @endforeach

                        @include('content.question-vocabulary-fields', [
                            'lesson' => $lesson,
                            'question' => null,
                            'formKey' => $addFormKey,
                        ])

                        <button type="submit" class="{{ $saveClass }}">
                            เพิ่มคำถาม
                        </button>
                    </form>
                </details>

                {{-- Questions --}}
                @forelse ($exercise->questions as $question)
                    @php
                        $editFormKey = 'edit-question-' . $question->id;
                        $restoreEdit = old('question_form') === $editFormKey;
                    @endphp

                    <div class="rounded-xl border border-gray-200 p-4 space-y-4">
                        <div>
                            <p class="font-extrabold text-gray-800">
                                {{ $question->question }}
                            </p>

                            @if ($question->explanation)
                                <p class="mt-1 text-sm text-gray-500">
                                    {{ $question->explanation }}
                                </p>
                            @endif

                            @if ($question->audio_path)
                                <p class="mt-2 break-all text-xs text-blue-600">
                                    Audio: {{ $question->audio_path }}
                                </p>
                            @endif

                            @if ($question->image_path)
                                <p class="mt-1 break-all text-xs text-purple-600">
                                    Image: {{ $question->image_path }}
                                </p>
                            @endif
                        </div>

                        @if (
                            $question->vocabulary_mode === 'after_vocabulary'
                            && $question->vocabularies->isNotEmpty()
                        )
                            <div class="rounded-lg bg-emerald-50 p-3 text-sm text-emerald-800">
                                <p class="font-bold">ออกหลังเรียนคำศัพท์เหล่านี้ครบ</p>
                                <p class="mt-1">
                                    {{ $question->vocabularies->pluck('word')->implode(' · ') }}
                                </p>
                            </div>
                        @elseif ($question->vocabulary_mode === 'lesson_end')
                            <p class="rounded-lg bg-blue-50 p-3 text-sm font-bold text-blue-700">
                                ทบทวนท้ายบท
                            </p>
                        @else
                            <p class="rounded-lg bg-amber-50 p-3 text-sm font-bold text-amber-800">
                                ยังรอจัดความสัมพันธ์ กรุณาแก้ไขคำถามนี้
                            </p>
                        @endif

                        <details @if ($restoreEdit) open @endif>
                            <summary class="cursor-pointer font-bold text-blue-600">
                                แก้ไขคำถาม
                            </summary>

                            <form
                                action="{{ route('content.questions.update', $question) }}"
                                method="POST"
                                class="mt-3 space-y-3"
                            >
                                @csrf
                                @method('PUT')

                                <label class="block text-sm font-bold text-gray-600">
                                    คำถาม *
                                    <textarea
                                        name="question"
                                        rows="2"
                                        required
                                        class="{{ $inputClass }}"
                                    >{{ $restoreEdit ? old('question') : $question->question }}</textarea>
                                </label>

                                <label class="block text-sm font-bold text-gray-600">
                                    คำอธิบาย
                                    <textarea
                                        name="explanation"
                                        rows="2"
                                        class="{{ $inputClass }}"
                                    >{{ $restoreEdit ? old('explanation') : $question->explanation }}</textarea>
                                </label>

                                @foreach (['audio_path' => 'Audio path', 'image_path' => 'Image path'] as $field => $label)
                                    <label class="block text-sm font-bold text-gray-600">
                                        {{ $label }}
                                        <input
                                            type="text"
                                            name="{{ $field }}"
                                            value="{{ $restoreEdit ? old($field) : $question->{$field} }}"
                                            class="{{ $inputClass }}"
                                        >
                                    </label>
                                @endforeach

                                @include('content.question-vocabulary-fields', [
                                    'lesson' => $lesson,
                                    'question' => $question,
                                    'formKey' => $editFormKey,
                                ])

                                <button type="submit" class="{{ $saveClass }}">
                                    บันทึกคำถาม
                                </button>
                            </form>
                        </details>

                        <form
                            action="{{ route('content.questions.destroy', $question) }}"
                            method="POST"
                            onsubmit="return confirm('ลบคำถามและคำตอบของคำถามนี้?')"
                        >
                            @csrf
                            @method('DELETE')

                            <button type="submit" class="{{ $deleteClass }}">
                                ลบคำถาม
                            </button>
                        </form>

                        {{-- Answers --}}
                        <div class="border-l-4 border-emerald-100 pl-4 space-y-3">
                            <h4 class="font-bold text-gray-700">คำตอบ</h4>

                            @forelse ($question->answers as $answer)
                                <div class="rounded-lg bg-gray-50 p-3 space-y-3">
                                    <p class="{{ $answer->is_correct ? 'font-bold text-emerald-700' : 'text-gray-600' }}">
                                        {{ $answer->is_correct ? '✓' : '○' }}
                                        {{ $answer->answer }}
                                    </p>

                                    <details>
                                        <summary class="cursor-pointer text-sm font-bold text-blue-600">
                                            แก้ไขคำตอบ
                                        </summary>

                                        <form
                                            action="{{ route('content.answers.update', $answer) }}"
                                            method="POST"
                                            class="mt-3 space-y-3"
                                        >
                                            @csrf
                                            @method('PUT')

                                            <input
                                                type="text"
                                                name="answer"
                                                value="{{ $answer->answer }}"
                                                required
                                                aria-label="คำตอบ"
                                                class="{{ $inputClass }}"
                                            >

                                            <label class="flex items-center gap-2">
                                                <input
                                                    type="checkbox"
                                                    name="is_correct"
                                                    value="1"
                                                    @checked($answer->is_correct)
                                                >
                                                เป็นคำตอบที่ถูก
                                            </label>

                                            <button type="submit" class="{{ $saveClass }}">
                                                บันทึกคำตอบ
                                            </button>
                                        </form>
                                    </details>

                                    <form
                                        action="{{ route('content.answers.destroy', $answer) }}"
                                        method="POST"
                                        onsubmit="return confirm('ลบคำตอบนี้?')"
                                    >
                                        @csrf
                                        @method('DELETE')

                                        <button type="submit" class="{{ $deleteClass }}">
                                            ลบคำตอบ
                                        </button>
                                    </form>
                                </div>
                            @empty
                                <p class="text-sm text-amber-700">
                                    ยังไม่มีคำตอบ กรุณาเพิ่มคำตอบ
                                    และกำหนดคำตอบที่ถูกก่อนให้ผู้เรียนใช้งาน
                                </p>
                            @endforelse

                            <form
                                action="{{ route('content.answers.store', $question) }}"
                                method="POST"
                                class="space-y-3"
                            >
                                @csrf

                                <input
                                    type="text"
                                    name="answer"
                                    required
                                    placeholder="เพิ่มคำตอบ"
                                    aria-label="คำตอบใหม่"
                                    class="{{ $inputClass }}"
                                >

                                <label class="flex items-center gap-2">
                                    <input type="checkbox" name="is_correct" value="1">
                                    เป็นคำตอบที่ถูก
                                </label>

                                <button type="submit" class="{{ $saveClass }}">
                                    เพิ่มคำตอบ
                                </button>
                            </form>
                        </div>
                    </div>
                @empty
                    <p class="text-center text-gray-400">ยังไม่มีคำถาม</p>
                @endforelse
            </div>
        @empty
            <p class="text-center text-gray-400">ยังไม่มีแบบฝึกหัด</p>
        @endforelse
    </section>
</div>
@endsection