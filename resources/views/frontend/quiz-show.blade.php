@extends('layouts.app')

@section('title', 'แบบทดสอบ')

@section('content')
<div x-data="quizApp({{ json_encode($questions) }})" class="max-w-xl mx-auto">

    <!-- Screen 1: คำถาม Quiz -->
    <template x-if="!isFinished">
        <div>
            <!-- Progress Bar ของ Quiz -->
            <div class="flex items-center gap-4 mb-8">
                <a href="/dev/lessons" class="text-gray-400 hover:text-gray-600 font-bold text-xl">✕</a>
                <div class="flex-1 h-3 bg-gray-200 rounded-full overflow-hidden">
                    <div class="h-full bg-blue-500 transition-all duration-300" 
                         :style="`width: ${((currentIndex + 1) / questions.length) * 100}%`"></div>
                </div>
                <span class="text-xs font-bold text-gray-400" x-text="`${currentIndex + 1}/${questions.length}`"></span>
            </div>

            <!-- กล่องโจทย์คำถาม -->
            <div class="bg-white border-2 border-gray-200 border-b-4 rounded-3xl p-6 shadow-sm mb-6">
                <span class="text-xs font-extrabold text-blue-500 tracking-wider uppercase mb-1 block">คำถามที่ <span x-text="currentIndex + 1"></span></span>
                <h2 class="text-2xl font-extrabold text-gray-800" x-text="currentQuestion.question"></h2>
            </div>

            <!-- ตัวเลือกคำตอบ -->
            <div class="grid grid-cols-1 gap-3 mb-8">
                <template x-for="(option, index) in currentQuestion.options" :key="index">
                    <button 
                        @click="selectOption(index)"
                        :class="selectedOption === index ? 'border-blue-500 bg-blue-50 text-blue-700' : 'border-gray-200 bg-white hover:bg-gray-50 text-gray-700'"
                        class="p-4 rounded-2xl border-2 border-b-4 font-bold text-left text-lg transition-all active:border-b-2">
                        <span class="inline-block w-8 h-8 rounded-xl bg-gray-100 text-center leading-8 text-sm mr-2" x-text="String.fromCharCode(65 + index)"></span>
                        <span x-text="option"></span>
                    </button>
                </template>
            </div>

            <!-- ปุ่มไปข้อถัดไป -->
            <button 
                @click="nextQuestion()"
                :disabled="selectedOption === null"
                :class="selectedOption !== null ? 'bg-blue-500 hover:bg-blue-600 text-white border-blue-600 cursor-pointer active:border-b-0 active:translate-y-1' : 'bg-gray-200 text-gray-400 border-gray-300 cursor-not-allowed'"
                class="w-full py-4 rounded-2xl font-extrabold text-lg transition-all border-b-4">
                <span x-text="currentIndex < questions.length - 1 ? 'ข้อถัดไป →' : 'ส่งคำตอบ 🎉'"></span>
            </button>
        </div>
    </template>

    <!-- Screen 2: สรุปผลหลังทำ Quiz เสร็จ -->
    <template x-if="isFinished">
        <div class="bg-white border-2 border-gray-200 border-b-4 rounded-3xl p-8 text-center shadow-sm">
            <div class="text-6xl mb-4">🎉</div>
            <h1 class="text-3xl font-black text-gray-800 mb-2">เก่งมาก! บทเรียนเสร็จสิ้น</h1>
            <p class="text-gray-500 mb-6 font-medium">คุณผ่านบทเรียนนี้เรียบร้อยแล้ว</p>

            <div class="flex justify-center gap-4 mb-8">
                <div class="bg-amber-50 border-2 border-amber-200 rounded-2xl p-4 w-1/2">
                    <div class="text-amber-500 font-bold text-sm">ได้รับ XP</div>
                    <div class="text-3xl font-black text-amber-600">+20 XP</div>
                </div>
                <div class="bg-emerald-50 border-2 border-emerald-200 rounded-2xl p-4 w-1/2">
                    <div class="text-emerald-500 font-bold text-sm">ความถูกต้อง</div>
                    <div class="text-3xl font-black text-emerald-600">100%</div>
                </div>
            </div>

            <a href="/dev/lessons" class="block w-full bg-emerald-500 hover:bg-emerald-600 text-white border-b-4 border-emerald-600 py-4 rounded-2xl font-extrabold text-lg transition-all active:border-b-0 active:translate-y-1">
                กลับหน้าหลัก
            </a>
        </div>
    </template>

</div>

<script>
function quizApp(questions) {
    return {
        questions: questions,
        currentIndex: 0,
        selectedOption: null,
        isFinished: false,

        get currentQuestion() {
            return this.questions[this.currentIndex];
        },

        selectOption(index) {
            this.selectedOption = index;
        },

        nextQuestion() {
            if (this.selectedOption !== null) {
                if (this.currentIndex < this.questions.length - 1) {
                    this.currentIndex++;
                    this.selectedOption = null;
                } else {
                    this.isFinished = true;
                }
            }
        }
    }
}
</script>
@endsection