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
        Schema::create('analytics_ticket_snapshot', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ticket_id')->constrained('tickets')->onDelete('cascade');
            $table->enum('status', ['Open', 'Forwarded', 'Closed']);
            $table->foreignId('assigned_agent_id')->nullable()->constrained('users')->onDelete('set null');
            $table->string('category');
            $table->date('snapshot_date');
            $table->timestamps();

            $table->index(['snapshot_date', 'status']);
            $table->index('ticket_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('analytics_ticket_snapshot');
    }
};
