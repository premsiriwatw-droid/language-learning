@extends('layouts.app')

@section('title', 'เรียนรู้คำศัพท์')

@section('content')
<div x-data="lessonApp({{ json_encode($vocabularies) }}, {{ $id }})" class="max-w-xl mx-auto">

    <!-- Progress Bar ของ Flashcards -->
    <div class="flex items-center gap-4 mb-8">
        <a href="/dev/lessons" class="text-gray-400 hover:text-gray-600 font-bold text-xl">✕</a>
        <div class="flex-1 h-3 bg-gray-200 rounded-full overflow-hidden">
            <div class="h-full bg-emerald-500 transition-all duration-300" 
                 :style="`width: ${((currentIndex + 1) / vocabularies.length) * 100}%`"></div>
        </div>
        <span class="text-xs font-bold text-gray-400" x-text="`${currentIndex + 1}/${vocabularies.length}`"></span>
    </div>

    <!-- Flashcard แสดงคำศัพท์ทีละคำ -->
    <div class="bg-white border-2 border-gray-200 border-b-4 rounded-3xl p-8 text-center min-h-[300px] flex flex-col justify-between shadow-sm relative overflow-hidden">
        
        <!-- นับถอยหลังวงกลมเล็กๆ -->
        <div class="text-right">
            <template x-if="!canNext">
                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-amber-100 text-amber-700 animate-pulse">
                    ⏳ อ่านคำศัพท์ (<span x-text="timer"></span>s)
                </span>
            </template>
            <template x-if="canNext">
                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-emerald-100 text-emerald-700">
                    ✨ พร้อมไปต่อแล้ว!
                </span>
            </template>
        </div>

        <!-- เนื้อหาคำศัพท์ -->
        <div class="my-auto">
            <h1 class="text-4xl font-black text-gray-800 mb-2" x-text="currentVocab.word"></h1>
            <p class="text-lg font-bold text-emerald-600 mb-4" x-text="`[${currentVocab.pronunciation}]`"></p>
            <p class="text-2xl font-bold text-gray-600 mb-6" x-text="currentVocab.meaning"></p>
            
            <div class="bg-gray-50 border border-gray-200 rounded-xl p-3 inline-block max-w-full">
                <p class="text-sm text-gray-500 italic" x-text="`"${currentVocab.example}"`"></p>
            </div>
        </div>

        <!-- ปุ่มกดไปคำถัดไป / ปุ่มไปหน้า Quiz -->
        <div class="mt-6">
            <!-- ปุ่มกรณีคำถัดไป -->
            <template x-if="currentIndex < vocabularies.length - 1">
                <button 
                    @click="nextWord()" 
                    :disabled="!canNext"
                    :class="canNext ? 'bg-emerald-500 hover:bg-emerald-600 text-white border-emerald-600 cursor-pointer active:border-b-0 active:translate-y-1' : 'bg-gray-200 text-gray-400 border-gray-300 cursor-not-allowed'"
                    class="w-full py-4 rounded-2xl font-extrabold text-lg transition-all border-b-4">
                    คำต่อไป →
                </button>
            </template>

            <!-- ปุ่มกรณีเรียนครบทุกคำแล้ว ให้ไปหน้า Quiz -->
            <template x-if="currentIndex === vocabularies.length - 1">
                <a 
                    :href="canNext ? `/dev/quiz/${lessonId}` : '#'"
                    :class="canNext ? 'bg-blue-500 hover:bg-blue-600 text-white border-blue-600 active:border-b-0 active:translate-y-1' : 'bg-gray-200 text-gray-400 border-gray-300 cursor-not-allowed pointer-events-none'"
                    class="block w-full py-4 rounded-2xl font-extrabold text-lg transition-all border-b-4 text-center">
                    เริ่มทำแบบทดสอบ (Quiz) 🎉
                </a>
            </template>
        </div>

    </div>
</div>

<script>
function lessonApp(vocabularies, lessonId) {
    return {
        vocabularies: vocabularies,
        lessonId: lessonId,
        currentIndex: 0,
        canNext: false,
        timer: 3,
        timerInterval: null,

        get currentVocab() {
            return this.vocabularies[this.currentIndex];
        },

        init() {
            this.startTimer();
        },

        startTimer() {
            this.canNext = false;
            this.timer = 3; // รอนับถอยหลัง 3 วินาที
            if (this.timerInterval) clearInterval(this.timerInterval);

            this.timerInterval = setInterval(() => {
                if (this.timer > 1) {
                    this.timer--;
                } else {
                    this.canNext = true;
                    clearInterval(this.timerInterval);
                }
            }, 1000);
        },

        nextWord() {
            if (this.canNext && this.currentIndex < this.vocabularies.length - 1) {
                this.currentIndex++;
                this.startTimer();
            }
        }
    }
}
</script>
@endsection