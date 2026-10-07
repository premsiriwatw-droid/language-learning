<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('question_vocabulary', function (Blueprint $table) {
            $table->foreignId('question_id')
                ->constrained('questions')
                ->cascadeOnDelete();

            $table->foreignId('vocabulary_id')
                ->constrained('vocabularies')
                ->cascadeOnDelete();

            $table->primary(['question_id', 'vocabulary_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('question_vocabulary');
    }
};