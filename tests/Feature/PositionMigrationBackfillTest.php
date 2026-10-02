<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

/**
 * Proves the add_position migration keeps today's ordering for data that
 * already exists in the database (e.g. the seeded Chinese track) by
 * rolling the migration back, inserting "legacy" rows, and migrating
 * forward again.
 *
 * Uses DatabaseMigrations (not RefreshDatabase) because it runs schema
 * changes, which must not happen inside the per-test transaction.
 */
class PositionMigrationBackfillTest extends TestCase
{
    use DatabaseMigrations;

    public function test_existing_units_and_lessons_get_sequential_positions_per_parent_in_creation_order(): void
    {
        // Undo the two 2026_10_01 migrations (is_admin, then position).
        $this->artisan('migrate:rollback', ['--step' => 2])->assertSuccessful();
        $this->assertFalse(Schema::hasColumn('units', 'position'));

        $now = now();
        $languageId = DB::table('languages')->insertGetId(['name' => 'Chinese', 'created_at' => $now, 'updated_at' => $now]);
        $courseA = DB::table('courses')->insertGetId(['language_id' => $languageId, 'title' => 'A', 'created_at' => $now, 'updated_at' => $now]);
        $courseB = DB::table('courses')->insertGetId(['language_id' => $languageId, 'title' => 'B', 'created_at' => $now, 'updated_at' => $now]);

        // Interleave inserts across courses so ids are not contiguous per parent.
        $a1 = DB::table('units')->insertGetId(['course_id' => $courseA, 'title' => 'A1', 'created_at' => $now, 'updated_at' => $now]);
        $b1 = DB::table('units')->insertGetId(['course_id' => $courseB, 'title' => 'B1', 'created_at' => $now, 'updated_at' => $now]);
        $a2 = DB::table('units')->insertGetId(['course_id' => $courseA, 'title' => 'A2', 'created_at' => $now, 'updated_at' => $now]);

        $l1 = DB::table('lessons')->insertGetId(['unit_id' => $a1, 'title' => 'L1', 'created_at' => $now, 'updated_at' => $now]);
        $l2 = DB::table('lessons')->insertGetId(['unit_id' => $a1, 'title' => 'L2', 'created_at' => $now, 'updated_at' => $now]);
        $l3 = DB::table('lessons')->insertGetId(['unit_id' => $a2, 'title' => 'L3', 'created_at' => $now, 'updated_at' => $now]);

        $this->artisan('migrate')->assertSuccessful();

        $unitPosition = fn ($id) => (int) DB::table('units')->where('id', $id)->value('position');
        $lessonPosition = fn ($id) => (int) DB::table('lessons')->where('id', $id)->value('position');

        $this->assertSame(1, $unitPosition($a1));
        $this->assertSame(2, $unitPosition($a2));
        $this->assertSame(1, $unitPosition($b1));

        $this->assertSame(1, $lessonPosition($l1));
        $this->assertSame(2, $lessonPosition($l2));
        $this->assertSame(1, $lessonPosition($l3));
    }
}
