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
        Schema::create('faq_sync_queue', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('faq_id');
            $table->enum('sync_status', ['pending', 'syncing', 'synced', 'failed'])->default('pending');
            $table->enum('sync_type', ['create', 'update', 'delete', 'enable', 'disable']);
            $table->integer('attempts')->default(0);
            $table->timestamp('last_attempt_at')->nullable();
            $table->text('last_error')->nullable();
            $table->timestamp('synced_at')->nullable();
            $table->timestamps();

            // Indexes for performance
            $table->index(['faq_id', 'sync_status'], 'idx_faq_status');
            $table->index(['sync_status', 'attempts'], 'idx_status_attempts');
            $table->index('created_at', 'idx_created');
            
            // Composite index for common queries
            $table->index(['sync_status', 'attempts', 'created_at'], 'idx_sync_queue_lookup');

            // Foreign key with cascade delete
            $table->foreign('faq_id')
                ->references('id')
                ->on('faqs')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('faq_sync_queue');
    }
};