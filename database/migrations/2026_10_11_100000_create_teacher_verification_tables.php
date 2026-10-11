<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('teacher_applications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('full_name');
            $table->text('bio');
            $table->text('teaching_experience');
            $table->json('teaching_languages');
            $table->string('status', 30)->default('pending')->index();
            $table->timestamp('submitted_at');
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->text('review_note')->nullable();
            $table->timestamps();
        });

        Schema::create('teacher_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('teacher_application_id')->constrained()->cascadeOnDelete();
            $table->string('document_type', 50);
            $table->string('file_path');
            $table->string('original_name');
            $table->string('mime_type', 100);
            $table->unsignedBigInteger('file_size');
            $table->timestamp('uploaded_at');
            $table->timestamps();
        });

        Schema::create('teacher_application_reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('teacher_application_id')->constrained()->cascadeOnDelete();
            $table->foreignId('reviewer_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('from_status', 30);
            $table->string('to_status', 30);
            $table->text('note')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('teacher_application_reviews');
        Schema::dropIfExists('teacher_documents');
        Schema::dropIfExists('teacher_applications');
    }
};
