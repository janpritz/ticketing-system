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
        Schema::table('faqs', function (Blueprint $table) {
            $table->timestamp('last_synced_at')->nullable()->after('response_disabled');
            $table->string('sync_hash', 64)->nullable()->after('last_synced_at');
            
            // Index for sync status queries
            $table->index('last_synced_at', 'idx_last_synced');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('faqs', function (Blueprint $table) {
            $table->dropIndex('idx_last_synced');
            $table->dropColumn(['last_synced_at', 'sync_hash']);
        });
    }
};