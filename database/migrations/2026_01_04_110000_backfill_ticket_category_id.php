<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;

class BackfillTicketCategoryId extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1) Ensure there is an 'Others' category to receive unmatched tickets
        $othersId = DB::table('categories')->where('name', 'Others')->value('id');
        if (!$othersId) {
            // Ensure we have a valid role_id to satisfy the non-null constraint.
            $defaultRoleId = DB::table('roles')->value('id');
            if (!$defaultRoleId) {
                // If no roles exist yet (test/early migration), create a fallback role.
                $defaultRoleId = DB::table('roles')->insertGetId([
                    'name' => 'Unassigned',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            $othersId = DB::table('categories')->insertGetId([
                'name' => 'Others',
                'role_id' => $defaultRoleId,
                'description' => 'Auto-created category for legacy/unknown tickets',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // 2) Backfill category_id by matching tickets.category (string) to categories.name
        DB::statement("UPDATE tickets JOIN categories ON categories.name = tickets.category SET tickets.category_id = categories.id WHERE tickets.category_id IS NULL");

        // 3) For any remaining tickets without a category_id, set category_id to 'Others'
        DB::table('tickets')->whereNull('category_id')->update(['category_id' => $othersId]);

        // 4) Assign unmatched tickets (now set to Others) to the Primary Administrator if they have no staff
        $primaryAdminId = null;
        try {
            // Only attempt lookup if the users table has a 'role' column (may not exist yet during migrations)
            if (\Illuminate\Support\Facades\Schema::hasColumn('users', 'role')) {
                $primaryAdminId = DB::table('users')->whereRaw('LOWER(role) = ?', ['primary administrator'])->value('id');
            }
        } catch (\Throwable $e) {
            // Ignore if users table/column not present during early migration runs
            $primaryAdminId = null;
        }

        if ($primaryAdminId) {
            DB::table('tickets')
                ->where('category_id', $othersId)
                ->whereNull('staff_id')
                ->update(['staff_id' => $primaryAdminId]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert: set category_id to null for tickets whose category name matches a category
        DB::statement("UPDATE tickets JOIN categories ON categories.name = tickets.category SET tickets.category_id = NULL WHERE tickets.category_id IS NOT NULL");
    }
}

