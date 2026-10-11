<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/*
| Teacher Course Marketplace & Enrollment (owner: Nakin)
|
| Teacher Courses are kept completely separate from the System Courses that
| Admin manages (languages -> courses -> units -> lessons). A Teacher Course
| only *references* existing system lessons as its curriculum; it never owns
| or edits them.
*/
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('teacher_courses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('teacher_id')->constrained('users')->cascadeOnDelete();
            // Admin deleting a language must not silently wipe teachers' courses.
            $table->foreignId('language_id')->nullable()->constrained()->nullOnDelete();
            $table->string('title');
            $table->text('description');
            $table->string('level', 20)->index();            // beginner / intermediate / advanced
            $table->string('price_type', 10)->index();       // free / paid
            $table->decimal('price', 10, 2)->nullable();     // only for paid
            $table->string('status', 20)->default('draft')->index(); // draft / published / closed
            $table->string('enrollment_mode', 30)->default('instant'); // instant / approval_required
            $table->timestamp('published_at')->nullable();
            $table->timestamps();

            $table->index(['teacher_id', 'status']);
        });

        Schema::create('teacher_course_lessons', function (Blueprint $table) {
            $table->id();
            $table->foreignId('teacher_course_id')->constrained()->cascadeOnDelete();
            $table->foreignId('lesson_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('position')->default(0);
            $table->timestamps();

            $table->unique(['teacher_course_id', 'lesson_id']);
        });

        Schema::create('course_enrollments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('teacher_course_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('status', 20)->index();            // pending / approved / rejected / active / completed
            $table->string('payment_status', 20);             // not_required / unpaid / submitted / confirmed
            $table->decimal('price_at_request', 10, 2)->nullable();
            $table->text('teacher_note')->nullable();
            $table->timestamp('requested_at');
            $table->timestamp('decided_at')->nullable();
            $table->timestamp('payment_submitted_at')->nullable();
            $table->timestamp('payment_confirmed_at')->nullable();
            $table->timestamp('activated_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            // One enrollment row per learner per course: blocks duplicates at DB level.
            $table->unique(['teacher_course_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('course_enrollments');
        Schema::dropIfExists('teacher_course_lessons');
        Schema::dropIfExists('teacher_courses');
    }
};
