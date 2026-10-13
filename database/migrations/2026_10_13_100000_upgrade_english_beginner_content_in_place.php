<?php

use App\Services\Content\EnglishBeginnerContentUpgrade;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        app(EnglishBeginnerContentUpgrade::class)->apply();
    }

    /** This audited content revision is not automatically reversed. */
    public function down(): void {}
};
