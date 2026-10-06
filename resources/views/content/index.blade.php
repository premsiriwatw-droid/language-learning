@extends('layouts.app')

@section('title', 'Content Management')

@section('content')
<div class="space-y-8">

    {{-- Header --}}
    <div class="flex items-center justify-between gap-4">
        <div>
            <p class="text-sm font-bold text-emerald-500 uppercase tracking-wide">
                Content Management
            </p>

            <h1 class="text-3xl font-extrabold text-gray-800 mt-1">
                {{ $lesson->title }}
            </h1>

            <p class="text-gray-500 mt-1">
                Manage vocabulary, exercises, questions and answers for this lesson.
            </p>
        </div>

        <a
            href="{{ route('lessons.show', $lesson) }}"
            class="shrink-0 px-4 py-2 rounded-xl border-2 border-gray-200 bg-white font-bold text-gray-600 hover:border-emerald-400 hover:text-emerald-600 transition"
        >
            Back to Lesson
        </a>
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
                Please check the information below.
            </p>

            <ul class="list-disc pl-5 space-y-1">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif


    {{-- ========================================================= --}}
    {{-- VOCABULARY --}}
    {{-- ========================================================= --}}

    <section class="bg-white border-2 border-gray-200 rounded-2xl p-6 shadow-sm">

        <div class="flex items-center justify-between mb-6">
            <div>
                <h2 class="text-2xl font-extrabold text-gray-800">
                    Vocabulary
                </h2>

                <p class="text-sm text-gray-500 mt-1">
                    {{ $lesson->vocabularies->count() }} words in this lesson
                </p>
            </div>
        </div>


        {{-- Add Vocabulary --}}
        <div class="bg-gray-50 border border-gray-200 rounded-xl p-5 mb-6">

            <h3 class="font-extrabold text-gray-700 mb-4">
                Add Vocabulary
            </h3>

            <form
                action="{{ route('content.vocabularies.store', $lesson) }}"
                method="POST"
                class="space-y-4"
            >
                @csrf

                <div class="grid md:grid-cols-2 gap-4">

                    <div>
                        <label class="block text-sm font-bold text-gray-600 mb-1">
                            Word *
                        </label>

                        <input
                            type="text"
                            name="word"
                            value="{{ old('word') }}"
                            required
                            class="w-full border-2 border-gray-200 rounded-xl px-3 py-2 focus:outline-none focus:border-emerald-400"
                            placeholder="你好"
                        >
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-gray-600 mb-1">
                            Pinyin
                        </label>

                        <input
                            type="text"
                            name="pinyin"
                            value="{{ old('pinyin') }}"
                            class="w-full border-2 border-gray-200 rounded-xl px-3 py-2 focus:outline-none focus:border-emerald-400"
                            placeholder="nǐ hǎo"
                        >
                    </div>

                </div>


                <div>
                    <label class="block text-sm font-bold text-gray-600 mb-1">
                        Meaning *
                    </label>

                    <input
                        type="text"
                        name="meaning"
                        value="{{ old('meaning') }}"
                        required
                        class="w-full border-2 border-gray-200 rounded-xl px-3 py-2 focus:outline-none focus:border-emerald-400"
                        placeholder="Hello"
                    >
                </div>


                <div class="grid md:grid-cols-3 gap-4">

                    <div>
                        <label class="block text-sm font-bold text-gray-600 mb-1">
                            Example Sentence
                        </label>

                        <input
                            type="text"
                            name="example_sentence"
                            value="{{ old('example_sentence') }}"
                            class="w-full border-2 border-gray-200 rounded-xl px-3 py-2 focus:outline-none focus:border-emerald-400"
                            placeholder="你好！"
                        >
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-gray-600 mb-1">
                            Example Pinyin
                        </label>

                        <input
                            type="text"
                            name="example_pinyin"
                            value="{{ old('example_pinyin') }}"
                            class="w-full border-2 border-gray-200 rounded-xl px-3 py-2 focus:outline-none focus:border-emerald-400"
                            placeholder="nǐ hǎo"
                        >
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-gray-600 mb-1">
                            Example Meaning
                        </label>

                        <input
                            type="text"
                            name="example_meaning"
                            value="{{ old('example_meaning') }}"
                            class="w-full border-2 border-gray-200 rounded-xl px-3 py-2 focus:outline-none focus:border-emerald-400"
                            placeholder="Hello!"
                        >
                    </div>

                </div>


                <button
                    type="submit"
                    class="px-5 py-2.5 rounded-xl bg-emerald-500 text-white font-extrabold hover:bg-emerald-600 transition"
                >
                    + Add Vocabulary
                </button>

            </form>
        </div>


        {{-- Vocabulary List --}}
        <div class="space-y-4">

            @forelse ($lesson->vocabularies as $vocabulary)

                <div
                    x-data="{ editing: false }"
                    class="border-2 border-gray-100 rounded-xl p-4"
                >

                    {{-- Display --}}
                    <div x-show="!editing">

                        <div class="flex justify-between gap-4">

                            <div>
                                <div class="flex flex-wrap items-baseline gap-2">
                                    <h3 class="text-xl font-extrabold text-gray-800">
                                        {{ $vocabulary->word }}
                                    </h3>

                                    @if ($vocabulary->pinyin)
                                        <span class="text-sm font-bold text-emerald-600">
                                            {{ $vocabulary->pinyin }}
                                        </span>
                                    @endif
                                </div>

                                <p class="text-gray-600 mt-1">
                                    {{ $vocabulary->meaning }}
                                </p>

                                @if ($vocabulary->example_sentence)
                                    <div class="mt-3 bg-gray-50 rounded-lg p-3">
                                        <p class="font-bold text-gray-700">
                                            {{ $vocabulary->example_sentence }}
                                        </p>

                                        @if ($vocabulary->example_pinyin)
                                            <p class="text-sm text-emerald-600">
                                                {{ $vocabulary->example_pinyin }}
                                            </p>
                                        @endif

                                        @if ($vocabulary->example_meaning)
                                            <p class="text-sm text-gray-500">
                                                {{ $vocabulary->example_meaning }}
                                            </p>
                                        @endif
                                    </div>
                                @endif
                            </div>


                            <div class="flex items-start gap-2">

                                <button
                                    type="button"
                                    @click="editing = true"
                                    class="px-3 py-1.5 rounded-lg bg-blue-50 text-blue-600 font-bold hover:bg-blue-100"
                                >
                                    Edit
                                </button>

                                <form
                                    action="{{ route('content.vocabularies.destroy', $vocabulary) }}"
                                    method="POST"
                                    onsubmit="return confirm('Delete this vocabulary?')"
                                >
                                    @csrf
                                    @method('DELETE')

                                    <button
                                        type="submit"
                                        class="px-3 py-1.5 rounded-lg bg-red-50 text-red-600 font-bold hover:bg-red-100"
                                    >
                                        Delete
                                    </button>
                                </form>

                            </div>

                        </div>

                    </div>


                    {{-- Edit Vocabulary --}}
                    <form
                        x-show="editing"
                        x-cloak
                        action="{{ route('content.vocabularies.update', $vocabulary) }}"
                        method="POST"
                        class="space-y-3"
                    >
                        @csrf
                        @method('PUT')

                        <div class="grid md:grid-cols-2 gap-3">

                            <input
                                type="text"
                                name="word"
                                value="{{ $vocabulary->word }}"
                                required
                                class="border-2 border-gray-200 rounded-lg px-3 py-2"
                            >

                            <input
                                type="text"
                                name="pinyin"
                                value="{{ $vocabulary->pinyin }}"
                                class="border-2 border-gray-200 rounded-lg px-3 py-2"
                            >

                        </div>

                        <input
                            type="text"
                            name="meaning"
                            value="{{ $vocabulary->meaning }}"
                            required
                            class="w-full border-2 border-gray-200 rounded-lg px-3 py-2"
                        >

                        <div class="grid md:grid-cols-3 gap-3">

                            <input
                                type="text"
                                name="example_sentence"
                                value="{{ $vocabulary->example_sentence }}"
                                placeholder="Example sentence"
                                class="border-2 border-gray-200 rounded-lg px-3 py-2"
                            >

                            <input
                                type="text"
                                name="example_pinyin"
                                value="{{ $vocabulary->example_pinyin }}"
                                placeholder="Example pinyin"
                                class="border-2 border-gray-200 rounded-lg px-3 py-2"
                            >

                            <input
                                type="text"
                                name="example_meaning"
                                value="{{ $vocabulary->example_meaning }}"
                                placeholder="Example meaning"
                                class="border-2 border-gray-200 rounded-lg px-3 py-2"
                            >

                        </div>

                        <div class="flex gap-2">

                            <button
                                type="submit"
                                class="px-4 py-2 bg-emerald-500 text-white rounded-lg font-bold"
                            >
                                Save
                            </button>

                            <button
                                type="button"
                                @click="editing = false"
                                class="px-4 py-2 bg-gray-100 text-gray-600 rounded-lg font-bold"
                            >
                                Cancel
                            </button>

                        </div>

                    </form>

                </div>

            @empty

                <div class="text-center text-gray-400 py-8">
                    No vocabulary yet.
                </div>

            @endforelse

        </div>

    </section>


    {{-- ========================================================= --}}
    {{-- EXERCISES --}}
    {{-- ========================================================= --}}

    <section class="bg-white border-2 border-gray-200 rounded-2xl p-6 shadow-sm">

        <div class="mb-6">
            <h2 class="text-2xl font-extrabold text-gray-800">
                Exercises
            </h2>

            <p class="text-sm text-gray-500 mt-1">
                {{ $lesson->exercises->count() }} exercises in this lesson
            </p>
        </div>


        {{-- Add Exercise --}}
        <form
            action="{{ route('content.exercises.store', $lesson) }}"
            method="POST"
            class="bg-gray-50 border border-gray-200 rounded-xl p-5 mb-6"
        >
            @csrf

            <h3 class="font-extrabold text-gray-700 mb-4">
                Add Exercise
            </h3>

            <div class="grid md:grid-cols-2 gap-4">

                <div>
                    <label class="block text-sm font-bold text-gray-600 mb-1">
                        Type *
                    </label>

                    <select
                        name="type"
                        required
                        class="w-full border-2 border-gray-200 rounded-xl px-3 py-2 bg-white"
                    >
                        <option value="multiple_choice">Multiple Choice</option>
                        <option value="fill_blank">Fill Blank</option>
                        <option value="translation">Translation</option>
                        <option value="arrange_words">Arrange Words</option>
                        <option value="listening">Listening</option>
                        <option value="image_choice">Image Choice</option>
                        <option value="custom">Custom</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-bold text-gray-600 mb-1">
                        Title *
                    </label>

                    <input
                        type="text"
                        name="title"
                        required
                        class="w-full border-2 border-gray-200 rounded-xl px-3 py-2"
                        placeholder="Choose the correct meaning"
                    >
                </div>

            </div>

            <button
                type="submit"
                class="mt-4 px-5 py-2.5 rounded-xl bg-blue-500 text-white font-extrabold hover:bg-blue-600"
            >
                + Add Exercise
            </button>

        </form>


        {{-- Exercise List --}}
        <div class="space-y-6">

            @forelse ($lesson->exercises as $exercise)

                <div class="border-2 border-gray-200 rounded-2xl overflow-hidden">

                    {{-- Exercise Header --}}
                    <div class="bg-gray-50 px-5 py-4 flex items-center justify-between gap-4">

                        <div>
                            <span class="inline-block text-xs font-extrabold uppercase bg-blue-100 text-blue-600 px-2 py-1 rounded-lg">
                                {{ $exercise->type }}
                            </span>

                            <h3 class="font-extrabold text-lg text-gray-800 mt-2">
                                {{ $exercise->title }}
                            </h3>
                        </div>


                        <div
                            x-data="{ editing: false }"
                            class="flex items-center gap-2"
                        >

                            <button
                                type="button"
                                @click="editing = !editing"
                                class="px-3 py-1.5 rounded-lg bg-blue-50 text-blue-600 font-bold"
                            >
                                Edit
                            </button>

                            <form
                                action="{{ route('content.exercises.destroy', $exercise) }}"
                                method="POST"
                                onsubmit="return confirm('Delete this exercise and all of its questions?')"
                            >
                                @csrf
                                @method('DELETE')

                                <button
                                    type="submit"
                                    class="px-3 py-1.5 rounded-lg bg-red-50 text-red-600 font-bold"
                                >
                                    Delete
                                </button>
                            </form>


                            {{-- Edit Exercise Popup --}}
                            <div
                                x-show="editing"
                                x-cloak
                                class="fixed inset-0 bg-black/30 flex items-center justify-center z-50 p-4"
                            >
                                <form
                                    action="{{ route('content.exercises.update', $exercise) }}"
                                    method="POST"
                                    class="bg-white rounded-2xl p-6 w-full max-w-md space-y-4"
                                    @click.outside="editing = false"
                                >
                                    @csrf
                                    @method('PUT')

                                    <h3 class="text-xl font-extrabold">
                                        Edit Exercise
                                    </h3>

                                    <select
                                        name="type"
                                        class="w-full border-2 border-gray-200 rounded-xl px-3 py-2"
                                    >
                                        @foreach ([
                                            'multiple_choice',
                                            'fill_blank',
                                            'translation',
                                            'arrange_words',
                                            'listening',
                                            'image_choice',
                                            'custom'
                                        ] as $type)

                                            <option
                                                value="{{ $type }}"
                                                @selected($exercise->type === $type)
                                            >
                                                {{ $type }}
                                            </option>

                                        @endforeach
                                    </select>

                                    <input
                                        type="text"
                                        name="title"
                                        value="{{ $exercise->title }}"
                                        required
                                        class="w-full border-2 border-gray-200 rounded-xl px-3 py-2"
                                    >

                                    <div class="flex gap-2">

                                        <button
                                            type="submit"
                                            class="px-4 py-2 bg-emerald-500 text-white rounded-lg font-bold"
                                        >
                                            Save
                                        </button>

                                        <button
                                            type="button"
                                            @click="editing = false"
                                            class="px-4 py-2 bg-gray-100 rounded-lg font-bold"
                                        >
                                            Cancel
                                        </button>

                                    </div>

                                </form>
                            </div>

                        </div>

                    </div>


                    <div class="p-5">

                        {{-- Add Question --}}
                        <form
                            action="{{ route('content.questions.store', $exercise) }}"
                            method="POST"
                            class="bg-blue-50/50 rounded-xl p-4 mb-5"
                        >
                            @csrf

                            <h4 class="font-extrabold text-gray-700 mb-3">
                                Add Question
                            </h4>

                            <textarea
                                name="question"
                                required
                                rows="2"
                                placeholder="Question"
                                class="w-full border-2 border-gray-200 rounded-xl px-3 py-2 mb-3"
                            ></textarea>

                            <textarea
                                name="explanation"
                                rows="2"
                                placeholder="Explanation (optional)"
                                class="w-full border-2 border-gray-200 rounded-xl px-3 py-2 mb-3"
                            ></textarea>

                            <div class="grid md:grid-cols-2 gap-3">

                                <input
                                    type="text"
                                    name="audio_path"
                                    placeholder="Audio path (optional)"
                                    class="border-2 border-gray-200 rounded-xl px-3 py-2"
                                >

                                <input
                                    type="text"
                                    name="image_path"
                                    placeholder="Image path (optional)"
                                    class="border-2 border-gray-200 rounded-xl px-3 py-2"
                                >

                            </div>

                            <button
                                type="submit"
                                class="mt-3 px-4 py-2 bg-blue-500 text-white rounded-lg font-bold"
                            >
                                + Add Question
                            </button>

                        </form>


                        {{-- Questions --}}
                        <div class="space-y-5">

                            @forelse ($exercise->questions as $question)

                                <div
                                    x-data="{ editingQuestion: false }"
                                    class="border border-gray-200 rounded-xl p-4"
                                >

                                    <div class="flex justify-between gap-4">

                                        <div>
                                            <p class="font-extrabold text-gray-800">
                                                {{ $question->question }}
                                            </p>

                                            @if ($question->explanation)
                                                <p class="text-sm text-gray-500 mt-1">
                                                    {{ $question->explanation }}
                                                </p>
                                            @endif

                                            @if ($question->audio_path)
                                                <p class="text-xs text-blue-500 mt-2">
                                                    Audio: {{ $question->audio_path }}
                                                </p>
                                            @endif

                                            @if ($question->image_path)
                                                <p class="text-xs text-purple-500 mt-1">
                                                    Image: {{ $question->image_path }}
                                                </p>
                                            @endif
                                        </div>


                                        <div class="flex gap-2">

                                            <button
                                                type="button"
                                                @click="editingQuestion = true"
                                                class="text-blue-600 font-bold text-sm"
                                            >
                                                Edit
                                            </button>

                                            <form
                                                action="{{ route('content.questions.destroy', $question) }}"
                                                method="POST"
                                                onsubmit="return confirm('Delete this question?')"
                                            >
                                                @csrf
                                                @method('DELETE')

                                                <button
                                                    type="submit"
                                                    class="text-red-600 font-bold text-sm"
                                                >
                                                    Delete
                                                </button>

                                            </form>

                                        </div>

                                    </div>


                                    {{-- Edit Question --}}
                                    <form
                                        x-show="editingQuestion"
                                        x-cloak
                                        action="{{ route('content.questions.update', $question) }}"
                                        method="POST"
                                        class="mt-4 space-y-3 bg-gray-50 p-4 rounded-xl"
                                    >
                                        @csrf
                                        @method('PUT')

                                        <textarea
                                            name="question"
                                            required
                                            rows="2"
                                            class="w-full border-2 border-gray-200 rounded-lg px-3 py-2"
                                        >{{ $question->question }}</textarea>

                                        <textarea
                                            name="explanation"
                                            rows="2"
                                            class="w-full border-2 border-gray-200 rounded-lg px-3 py-2"
                                        >{{ $question->explanation }}</textarea>

                                        <div class="grid md:grid-cols-2 gap-3">

                                            <input
                                                type="text"
                                                name="audio_path"
                                                value="{{ $question->audio_path }}"
                                                placeholder="Audio path"
                                                class="border-2 border-gray-200 rounded-lg px-3 py-2"
                                            >

                                            <input
                                                type="text"
                                                name="image_path"
                                                value="{{ $question->image_path }}"
                                                placeholder="Image path"
                                                class="border-2 border-gray-200 rounded-lg px-3 py-2"
                                            >

                                        </div>

                                        <div class="flex gap-2">

                                            <button
                                                type="submit"
                                                class="px-4 py-2 bg-emerald-500 text-white rounded-lg font-bold"
                                            >
                                                Save
                                            </button>

                                            <button
                                                type="button"
                                                @click="editingQuestion = false"
                                                class="px-4 py-2 bg-gray-200 rounded-lg font-bold"
                                            >
                                                Cancel
                                            </button>

                                        </div>

                                    </form>


                                    {{-- Answers --}}
                                    <div class="mt-5 pl-4 border-l-4 border-emerald-100">

                                        <h5 class="font-extrabold text-sm text-gray-600 mb-3">
                                            Answers
                                        </h5>

                                        <div class="space-y-2 mb-4">

                                            @forelse ($question->answers as $answer)

                                                <div
                                                    x-data="{ editingAnswer: false }"
                                                    class="bg-gray-50 rounded-lg p-3"
                                                >

                                                    <div
                                                        x-show="!editingAnswer"
                                                        class="flex items-center justify-between gap-3"
                                                    >

                                                        <div class="flex items-center gap-2">

                                                            @if ($answer->is_correct)
                                                                <span class="text-emerald-500 font-extrabold">
                                                                    ✓
                                                                </span>
                                                            @else
                                                                <span class="text-gray-300">
                                                                    ○
                                                                </span>
                                                            @endif

                                                            <span class="{{ $answer->is_correct ? 'font-bold text-emerald-700' : 'text-gray-700' }}">
                                                                {{ $answer->answer }}
                                                            </span>

                                                        </div>


                                                        <div class="flex gap-2">

                                                            <button
                                                                type="button"
                                                                @click="editingAnswer = true"
                                                                class="text-blue-600 text-sm font-bold"
                                                            >
                                                                Edit
                                                            </button>

                                                            <form
                                                                action="{{ route('content.answers.destroy', $answer) }}"
                                                                method="POST"
                                                                onsubmit="return confirm('Delete this answer?')"
                                                            >
                                                                @csrf
                                                                @method('DELETE')

                                                                <button
                                                                    type="submit"
                                                                    class="text-red-600 text-sm font-bold"
                                                                >
                                                                    Delete
                                                                </button>

                                                            </form>

                                                        </div>

                                                    </div>


                                                    {{-- Edit Answer --}}
                                                    <form
                                                        x-show="editingAnswer"
                                                        x-cloak
                                                        action="{{ route('content.answers.update', $answer) }}"
                                                        method="POST"
                                                        class="space-y-3"
                                                    >
                                                        @csrf
                                                        @method('PUT')

                                                        <input
                                                            type="text"
                                                            name="answer"
                                                            value="{{ $answer->answer }}"
                                                            required
                                                            class="w-full border-2 border-gray-200 rounded-lg px-3 py-2"
                                                        >

                                                        <label class="flex items-center gap-2 text-sm font-bold text-gray-600">

                                                            <input
                                                                type="checkbox"
                                                                name="is_correct"
                                                                value="1"
                                                                @checked($answer->is_correct)
                                                            >

                                                            Correct answer

                                                        </label>

                                                        <div class="flex gap-2">

                                                            <button
                                                                type="submit"
                                                                class="px-3 py-1.5 bg-emerald-500 text-white rounded-lg font-bold"
                                                            >
                                                                Save
                                                            </button>

                                                            <button
                                                                type="button"
                                                                @click="editingAnswer = false"
                                                                class="px-3 py-1.5 bg-gray-200 rounded-lg font-bold"
                                                            >
                                                                Cancel
                                                            </button>

                                                        </div>

                                                    </form>

                                                </div>

                                            @empty

                                                <p class="text-sm text-gray-400">
                                                    No answers yet.
                                                </p>

                                            @endforelse

                                        </div>


                                        {{-- Add Answer --}}
                                        <form
                                            action="{{ route('content.answers.store', $question) }}"
                                            method="POST"
                                            class="flex flex-col md:flex-row md:items-center gap-3"
                                        >
                                            @csrf

                                            <input
                                                type="text"
                                                name="answer"
                                                required
                                                placeholder="New answer"
                                                class="flex-1 border-2 border-gray-200 rounded-lg px-3 py-2"
                                            >

                                            <label class="flex items-center gap-2 text-sm font-bold text-gray-600 whitespace-nowrap">

                                                <input
                                                    type="checkbox"
                                                    name="is_correct"
                                                    value="1"
                                                >

                                                Correct

                                            </label>

                                            <button
                                                type="submit"
                                                class="px-4 py-2 bg-emerald-500 text-white rounded-lg font-bold whitespace-nowrap"
                                            >
                                                + Answer
                                            </button>

                                        </form>

                                    </div>

                                </div>

                            @empty

                                <div class="text-center text-gray-400 py-5">
                                    No questions in this exercise yet.
                                </div>

                            @endforelse

                        </div>

                    </div>

                </div>

            @empty

                <div class="text-center text-gray-400 py-8">
                    No exercises yet.
                </div>

            @endforelse

        </div>

    </section>

</div>
@endsection