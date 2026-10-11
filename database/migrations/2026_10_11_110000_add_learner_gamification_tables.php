<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->unsignedInteger('current_streak')->default(0);
            $table->unsignedInteger('longest_streak')->default(0);
            $table->date('last_active_date')->nullable();
        });

        Schema::create('star_rewards', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('source_key', 191);
            $table->unsignedInteger('stars');
            $table->timestamps();
            $table->unique(['user_id', 'source_key']);
        });

        Schema::create('daily_quests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->date('quest_date');
            $table->string('type', 40);
            $table->unsignedInteger('target');
            $table->unsignedInteger('progress')->default(0);
            $table->unsignedInteger('reward_stars');
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('claimed_at')->nullable();
            $table->timestamps();
            $table->unique(['user_id', 'quest_date', 'type']);
        });

        Schema::create('daily_quest_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->date('quest_date');
            $table->string('type', 40);
            $table->string('event_key', 64);
            $table->timestamps();
            $table->unique(['user_id', 'quest_date', 'type', 'event_key'], 'daily_quest_event_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('daily_quest_events');
        Schema::dropIfExists('daily_quests');
        Schema::dropIfExists('star_rewards');

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['current_streak', 'longest_streak', 'last_active_date']);
        });
    }
};
