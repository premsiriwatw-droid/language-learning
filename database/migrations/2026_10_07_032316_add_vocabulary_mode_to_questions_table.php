<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('questions', function (Blueprint $table) {
            $table->string('vocabulary_mode', 30)
                ->default('pending');
        });

        // คำถามเดิมที่มี mapping อยู่แล้ว ใช้งานต่อได้
        if (Schema::hasTable('question_vocabulary')) {
            DB::table('questions')
                ->whereIn(
                    'id',
                    DB::table('question_vocabulary')
                        ->select('question_id')
                )
                ->update([
                    'vocabulary_mode' => 'after_vocabulary',
                ]);
        }

        // คำถามเดิมที่ยังไม่มี mapping คงสถานะ pending
        // เพื่อให้ผู้ดูแลเลือกคำศัพท์หรือกำหนดเป็นท้ายบทเอง
    }

    public function down(): void
    {
        Schema::table('questions', function (Blueprint $table) {
            $table->dropColumn('vocabulary_mode');
        });
    }
};