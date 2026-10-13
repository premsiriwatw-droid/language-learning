<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('teacher_recommendations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('teacher_course_id')->constrained()->cascadeOnDelete();
            $table->foreignId('student_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('lesson_id')->nullable()->constrained()->nullOnDelete();
            $table->string('lesson_title');
            $table->text('reason');
            $table->timestamps();
            $table->index(['teacher_course_id', 'student_id']);
        });

        Schema::create('course_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('teacher_course_id')->constrained()->cascadeOnDelete();
            $table->string('title', 160);
            $table->text('instructions')->nullable();
            $table->string('target_kind', 10);
            $table->foreignId('lesson_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('unit_id')->nullable()->constrained()->nullOnDelete();
            $table->string('target_label');
            $table->json('lesson_ids');
            $table->dateTime('due_at');
            $table->timestamps();
        });

        Schema::create('assignment_statuses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_assignment_id')->constrained()->cascadeOnDelete();
            $table->foreignId('student_id')->constrained('users')->cascadeOnDelete();
            $table->string('status', 20)->default('pending');
            $table->unsignedBigInteger('attempt_baseline')->default(0);
            $table->dateTime('completed_at')->nullable();
            $table->timestamps();
            $table->unique(['course_assignment_id', 'student_id']);
        });

        Schema::create('course_conversations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('teacher_course_id')->constrained()->cascadeOnDelete();
            $table->foreignId('student_id')->constrained('users')->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['teacher_course_id', 'student_id']);
        });

        Schema::create('course_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_conversation_id')->constrained()->cascadeOnDelete();
            $table->foreignId('sender_id')->nullable()->constrained('users')->nullOnDelete();
            $table->text('body');
            $table->timestamps();
            $table->index(['course_conversation_id', 'id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('course_messages');
        Schema::dropIfExists('course_conversations');
        Schema::dropIfExists('assignment_statuses');
        Schema::dropIfExists('course_assignments');
        Schema::dropIfExists('teacher_recommendations');
    }
};
