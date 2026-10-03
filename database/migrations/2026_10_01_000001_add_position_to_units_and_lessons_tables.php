<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('units', function (Blueprint $table) {
            $table->unsignedInteger('position')->default(0)->after('course_id');
        });

        Schema::table('lessons', function (Blueprint $table) {
            $table->unsignedInteger('position')->default(0)->after('unit_id');
        });

        /*
         * Backfill existing rows so every Unit/Lesson gets a stable,
         * sequential position (1, 2, 3, ...) within its parent, based on
         * the order the rows were originally created in (id ascending).
         * This keeps today's lesson ordering exactly the same as before,
         * it just makes that order explicit instead of implicit.
         */
        $this->backfillPositions('units', 'course_id');
        $this->backfillPositions('lessons', 'unit_id');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('units', function (Blueprint $table) {
            $table->dropColumn('position');
        });

        Schema::table('lessons', function (Blueprint $table) {
            $table->dropColumn('position');
        });
    }

    /**
     * Assign 1-based, sequential positions to every row of $table,
     * scoped per $parentColumn, ordered by id (creation order).
     */
    private function backfillPositions(string $table, string $parentColumn): void
    {
        $rows = DB::table($table)
            ->select('id', $parentColumn)
            ->orderBy($parentColumn)
            ->orderBy('id')
            ->get();

        $position = [];

        foreach ($rows as $row) {
            $parentId = $row->{$parentColumn};
            $position[$parentId] = ($position[$parentId] ?? 0) + 1;

            DB::table($table)
                ->where('id', $row->id)
                ->update(['position' => $position[$parentId]]);
        }
    }
};
