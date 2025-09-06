<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('ticket_routing_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ticket_id')->constrained('tickets')->onDelete('cascade'); // Link to the ticket
            $table->foreignId('staff_id')->nullable()->constrained('users')->onDelete('set null'); // Staff who was assigned to this ticket
            $table->enum('status', ['Open', 'In-Progress', 'Closed']); // Status when the routing occurred
            $table->timestamp('routed_at')->useCurrent(); // Time when the ticket was routed
            $table->text('notes')->nullable(); // Optional notes to explain why the ticket was routed
            $table->timestamps(); // created_at and updated_at
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ticket_routing_histories');
    }
};
