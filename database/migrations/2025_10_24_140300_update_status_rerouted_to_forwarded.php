<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $driver = Schema::getConnection()->getDriverName();

        // For MySQL/MariaDB we must add the new enum value first, then update rows,
        // then remove the old enum value. Doing it in this order prevents "data truncated"
        // errors when trying to write a value that's not part of the enum.
        if ($driver === 'mysql') {
            // 1) Add Forwarded to the enum definition (keep Re-routed for now)
            DB::statement("ALTER TABLE `tickets` MODIFY `status` ENUM('Open','Re-routed','Forwarded','Closed') NOT NULL DEFAULT 'Open'");
            DB::statement("ALTER TABLE `ticket_routing_histories` MODIFY `status` ENUM('Open','Re-routed','Forwarded','Closed') NOT NULL");

            // 2) Update rows that used the old value
            DB::table('tickets')->where('status', 'Re-routed')->update(['status' => 'Forwarded']);
            DB::table('ticket_routing_histories')->where('status', 'Re-routed')->update(['status' => 'Forwarded']);

            // 3) Clean up enum to remove the old value (final enum: Open, Forwarded, Closed)
            DB::statement("ALTER TABLE `tickets` MODIFY `status` ENUM('Open','Forwarded','Closed') NOT NULL DEFAULT 'Open'");
            DB::statement("ALTER TABLE `ticket_routing_histories` MODIFY `status` ENUM('Open','Forwarded','Closed') NOT NULL");
        } else {
            // Non-MySQL drivers (Postgres, SQLite) may not allow easy enum modification here.
            // We'll attempt a safe data update first; altering the column type may require
            // manual migration steps for those platforms.
            try {
                DB::table('tickets')->where('status', 'Re-routed')->update(['status' => 'Forwarded']);
                DB::table('ticket_routing_histories')->where('status', 'Re-routed')->update(['status' => 'Forwarded']);
            } catch (\Throwable $e) {
                // If this fails on non-MySQL drivers, surface a helpful message in logs.
                // Developers should perform the needed ALTER TYPE steps for Postgres or rebuild the table.
                throw $e;
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $driver = Schema::getConnection()->getDriverName();

        // Revert data first
        DB::table('tickets')->where('status', 'Forwarded')->update(['status' => 'Re-routed']);
        DB::table('ticket_routing_histories')->where('status', 'Forwarded')->update(['status' => 'Re-routed']);

        if ($driver === 'mysql') {
            // Restore enum to original values
            DB::statement("ALTER TABLE `tickets` MODIFY `status` ENUM('Open','Re-routed','Closed') NOT NULL DEFAULT 'Open'");
            DB::statement("ALTER TABLE `ticket_routing_histories` MODIFY `status` ENUM('Open','Re-routed','Closed') NOT NULL");
        } else {
            // For other DBs, manual steps may be required to restore the enum/type.
        }
    }
};