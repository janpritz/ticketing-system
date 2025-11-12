<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Rename 'Re-routed' status to 'Forwarded' in both tickets and routing history tables.
     */
    public function up(): void
    {
        // Update tickets table
        DB::statement("UPDATE tickets SET status = 'Forwarded' WHERE status = 'Re-routed'");

        // Update ticket routing histories table
        DB::statement("UPDATE ticket_routing_histories SET status = 'Forwarded' WHERE status = 'Re-routed'");
    }

    /**
     * Reverse the migrations.
     * Rename 'Forwarded' status back to 'Re-routed' in both tables.
     */
    public function down(): void
    {
        // Revert tickets table
        DB::statement("UPDATE tickets SET status = 'Re-routed' WHERE status = 'Forwarded'");

        // Revert ticket routing histories table
        DB::statement("UPDATE ticket_routing_histories SET status = 'Re-routed' WHERE status = 'Forwarded'");
    }
};
