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
        // Add is_processed column to tickets table
        Schema::table('tickets', function (Blueprint $table) {
            $table->boolean('is_processed')->default(false)->after('attachments');
        });

        // Migrate existing data from processed_tickets to tickets
        // Check if processed_tickets table exists and has data
        if (Schema::hasTable('processed_tickets')) {
            $processedTickets = DB::table('processed_tickets')->get();
            
            foreach ($processedTickets as $processed) {
                DB::table('tickets')
                    ->where('id', $processed->ticket_id)
                    ->update(['is_processed' => true]);
            }

            // Drop the processed_tickets table
            Schema::dropIfExists('processed_tickets');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Recreate processed_tickets table
        Schema::create('processed_tickets', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('ticket_id');
            $table->timestamps();
        });

        // Migrate data back from tickets.is_processed to processed_tickets
        $processedTickets = DB::table('tickets')
            ->where('is_processed', true)
            ->get();

        foreach ($processedTickets as $ticket) {
            DB::table('processed_tickets')->insert([
                'ticket_id' => $ticket->id,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // Drop is_processed column
        Schema::table('tickets', function (Blueprint $table) {
            $table->dropColumn('is_processed');
        });
    }
};
