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
        // Add a singular 'subscription' JSON column to store a single subscription payload.
        Schema::table('push_notifications', function (Blueprint $table) {
            if (! Schema::hasColumn('push_notifications', 'subscription')) {
                $table->json('subscription')->nullable()->after('subscriptions');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('push_notifications', function (Blueprint $table) {
            if (Schema::hasColumn('push_notifications', 'subscription')) {
                $table->dropColumn('subscription');
            }
        });
    }
};