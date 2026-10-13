<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Teacher Course Marketplace (owner: Nakin)
 * content_revision goes up on every content change and every submission, so an admin
 * can only approve/reject exactly the version that was on screen.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('teacher_courses', function (Blueprint $table) {
            $table->unsignedInteger('content_revision')->default(0)->after('review_note');
        });
    }

    public function down(): void
    {
        Schema::table('teacher_courses', function (Blueprint $table) {
            $table->dropColumn('content_revision');
        });
    }
};
