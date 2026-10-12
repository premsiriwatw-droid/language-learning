@php
    $restoreInput = old('question_form') === $formKey;

    $savedMode = isset($question)
        ? $question->vocabulary_mode
        : '';

    $selectedMode = $restoreInput
        ? old('vocabulary_mode', '')
        : $savedMode;

    if (!in_array($selectedMode, ['after_vocabulary', 'lesson_end'], true)) {
        $selectedMode = '';
    }

    $selectedIds = $restoreInput
        ? old('vocabulary_ids', [])
        : (isset($question) ? $question->vocabularies->modelKeys() : []);

    $selectedIds = is_array($selectedIds)
        ? array_map('strval', $selectedIds)
        : [];
@endphp

<div
    x-data="{ mode: '{{ $selectedMode }}' }"
    class="rounded-xl border-2 border-emerald-100 bg-emerald-50 p-4 space-y-3"
>
    <input type="hidden" name="question_form" value="{{ $formKey }}">

    <label class="block font-bold text-gray-700">
        จัดคำถามไว้ช่วงไหน?
        <select
            name="vocabulary_mode"
            x-model="mode"
            required
            class="mt-2 w-full rounded-lg border-2 border-gray-200 bg-white px-3 py-2"
        >
            <option value="" @selected($selectedMode === '')>
                กรุณาเลือกรูปแบบ
            </option>
            <option
                value="after_vocabulary"
                @selected($selectedMode === 'after_vocabulary')
            >
                หลังเรียนคำศัพท์ที่เลือกครบ
            </option>
            <option
                value="lesson_end"
                @selected($selectedMode === 'lesson_end')
            >
                ทบทวนท้ายบท ไม่ผูกคำศัพท์
            </option>
        </select>
    </label>

    <fieldset
        x-show="mode === 'after_vocabulary'"
        :disabled="mode !== 'after_vocabulary'"
        @disabled($selectedMode !== 'after_vocabulary')
        class="space-y-3"
    >
        <legend class="font-bold text-gray-700">
            คำศัพท์ที่ต้องเรียนก่อน
        </legend>

        <p class="text-sm text-gray-600">
            เลือกอย่างน้อยหนึ่งคำ หากเลือกหลายคำ
            คำถามจะออกหลังผู้เรียนเรียนครบทุกคำที่เลือก
        </p>

        <div class="max-h-64 overflow-y-auto rounded-lg bg-white p-3 space-y-2">
            @forelse ($lesson->vocabularies->sortBy('id') as $word)
                <label class="flex items-start gap-3 rounded-lg p-2 hover:bg-gray-50">
                    <input
                        type="checkbox"
                        name="vocabulary_ids[]"
                        value="{{ $word->id }}"
                        @checked(in_array((string) $word->id, $selectedIds, true))
                        class="mt-1"
                    >
                    <span>
                        <span class="font-bold">{{ $word->word }}</span>
                        @if ($word->pinyin)
                            <span class="text-sm text-emerald-700">
                                {{ $word->pinyin }}
                            </span>
                        @endif
                        <span class="block text-sm text-gray-600">
                            {{ $word->meaning }}
                        </span>
                    </span>
                </label>
            @empty
                <p class="text-sm text-amber-700">
                    บทนี้ยังไม่มีคำศัพท์ กรุณาเพิ่มคำศัพท์ก่อน
                    หรือเลือกเป็นคำถามทบทวนท้ายบท
                </p>
            @endforelse
        </div>
    </fieldset>

    <p
        x-show="mode === 'lesson_end'"
        class="text-sm text-gray-600"
    >
        คำถามนี้จะอยู่หลังเรียนคำศัพท์ครบทั้งบท
        การบันทึกจะล้างการผูกคำศัพท์เดิมของคำถามนี้
    </p>
</div>