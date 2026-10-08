<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('learning_attempts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('lesson_id')->nullable()->constrained()->nullOnDelete();
            $table->string('run_key', 64);
            $table->string('lesson_title');
            $table->string('language_name')->nullable();
            $table->unsignedInteger('vocabulary_count');
            $table->unsignedInteger('question_count');
            $table->unsignedInteger('correct_count');
            $table->unsignedInteger('wrong_count');
            $table->unsignedInteger('wrong_attempts');
            $table->decimal('score_percent', 5, 2)->nullable();
            $table->unsignedInteger('elapsed_seconds');
            $table->timestamp('started_at');
            $table->timestamp('completed_at');
            $table->json('results');
            $table->timestamps();
            $table->unique(['user_id', 'run_key']);
            $table->index(['user_id', 'completed_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('learning_attempts');
    }
};
