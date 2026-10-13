<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Teacher Course Marketplace (owner: Nakin)
 * - teacher_lessons: lessons a teacher writes inside their own course (text, document, video).
 * - review columns: a course must be approved by an admin before it is published.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('teacher_courses', function (Blueprint $table) {
            $table->timestamp('submitted_at')->nullable()->after('published_at');
            $table->timestamp('approved_at')->nullable()->after('submitted_at');
            $table->timestamp('reviewed_at')->nullable()->after('approved_at');
            $table->foreignId('reviewed_by')->nullable()->after('reviewed_at')->constrained('users')->nullOnDelete();
            $table->text('review_note')->nullable()->after('reviewed_by');
        });

        // Courses that were already live before the review step existed keep their approval.
        DB::table('teacher_courses')
            ->whereIn('status', ['published', 'closed'])
            ->update(['approved_at' => DB::raw('COALESCE(published_at, updated_at, created_at)')]);

        Schema::create('teacher_lessons', function (Blueprint $table) {
            $table->id();
            $table->foreignId('teacher_course_id')->constrained('teacher_courses')->cascadeOnDelete();
            $table->string('title', 150);
            $table->text('body')->nullable();
            $table->string('video_url', 500)->nullable();
            $table->string('video_path')->nullable();
            $table->string('video_name')->nullable();
            $table->unsignedBigInteger('video_size')->nullable();
            $table->string('document_path')->nullable();
            $table->string('document_name')->nullable();
            $table->unsignedBigInteger('document_size')->nullable();
            $table->unsignedInteger('position')->default(1);
            $table->timestamps();

            $table->index(['teacher_course_id', 'position']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('teacher_lessons');

        Schema::table('teacher_courses', function (Blueprint $table) {
            $table->dropConstrainedForeignId('reviewed_by');
            $table->dropColumn(['submitted_at', 'approved_at', 'reviewed_at', 'review_note']);
        });
    }
};
