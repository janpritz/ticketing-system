<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

return new class extends Migration {
    public function up(): void
    {
        // Prefer direct ALTER for MySQL enums (safer & faster). If not MySQL, attempt a string change fallback.
        $driver = config('database.default');

        if ($driver === 'mysql') {
            // Add 'deleted' to the enum type for the status column.
            DB::statement("ALTER TABLE `faqs` MODIFY `status` ENUM('pending','trained','deleted') NOT NULL DEFAULT 'pending'");
        } else {
            // Fallback: convert to string column to accept all statuses (requires doctrine/dbal for change())
            Schema::table('faqs', function (Blueprint $table) {
                // If doctrine/dbal is not installed this may fail; using DB statement as safe fallback.
                $table->string('status', 50)->default('pending')->change();
            });
        }
    }

    public function down(): void
    {
        // Before reverting the enum, convert any 'deleted' statuses back to 'pending' to avoid invalid value errors.
        DB::table('faqs')->where('status', 'deleted')->update(['status' => 'pending']);

        $driver = config('database.default');

        if ($driver === 'mysql') {
            DB::statement("ALTER TABLE `faqs` MODIFY `status` ENUM('pending','trained') NOT NULL DEFAULT 'pending'");
        } else {
            Schema::table('faqs', function (Blueprint $table) {
                // revert to string (best-effort). Note: original migration used enum; if you rely on enum, run raw SQL per your DB.
                $table->string('status', 50)->default('pending')->change();
            });
        }
    }
};