<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * This migration updates the staged_faqs status enum from 'pending','approved','rejected' to 'pending','publish','unpublish'
     * and migrates existing data.
     */
    public function up(): void
    {
        // First, change the column to a VARCHAR to avoid enum constraints during data migration
        DB::statement("ALTER TABLE staged_faqs MODIFY COLUMN status VARCHAR(50) DEFAULT 'pending'");

        // Now update the data: 'approved' -> 'publish', 'rejected' -> 'unpublish'
        DB::table('staged_faqs')
            ->where('status', 'approved')
            ->update(['status' => 'publish']);

        DB::table('staged_faqs')
            ->where('status', 'rejected')
            ->update(['status' => 'unpublish']);

        // Now change back to the new enum
        DB::statement("ALTER TABLE staged_faqs MODIFY COLUMN status ENUM('pending', 'publish', 'unpublish') DEFAULT 'pending'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // First, change to VARCHAR
        DB::statement("ALTER TABLE staged_faqs MODIFY COLUMN status VARCHAR(50) DEFAULT 'pending'");

        // Reverse the data migration: 'publish' -> 'approved', 'unpublish' -> 'rejected'
        DB::table('staged_faqs')
            ->where('status', 'publish')
            ->update(['status' => 'approved']);

        DB::table('staged_faqs')
            ->where('status', 'unpublish')
            ->update(['status' => 'rejected']);

        // Revert to the old enum
        DB::statement("ALTER TABLE staged_faqs MODIFY COLUMN status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending'");
    }
};
