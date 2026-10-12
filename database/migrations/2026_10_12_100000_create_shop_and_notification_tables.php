<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shop_items', function (Blueprint $table) {
            $table->id();
            $table->string('sku')->unique();
            $table->string('name');
            $table->string('type', 30);
            $table->string('style_key', 50);
            $table->unsignedInteger('price_stars');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
        Schema::create('user_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('shop_item_id')->constrained()->restrictOnDelete();
            $table->timestamps();
            $table->unique(['user_id', 'shop_item_id']);
        });
        Schema::create('purchases', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('shop_item_id')->constrained()->restrictOnDelete();
            $table->unsignedInteger('price_stars');
            $table->timestamps();
            $table->unique(['user_id', 'shop_item_id']);
        });
        Schema::create('user_equipped_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_item_id')->constrained()->cascadeOnDelete();
            $table->string('type', 30);
            $table->timestamps();
            $table->unique(['user_id', 'type']);
        });
        Schema::create('notifications', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('type');
            $table->morphs('notifiable');
            $table->text('data');
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
            $table->index(['notifiable_type', 'notifiable_id', 'read_at'], 'notifications_unread_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notifications');
        Schema::dropIfExists('user_equipped_items');
        Schema::dropIfExists('purchases');
        Schema::dropIfExists('user_items');
        Schema::dropIfExists('shop_items');
    }
};
