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
            $othersId = DB::table('categories')->insertGetId([
                'name' => 'Others',
                'role_id' => null,
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
        $primaryAdminId = DB::table('users')->whereRaw('LOWER(role) = ?', ['primary administrator'])->value('id');
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

