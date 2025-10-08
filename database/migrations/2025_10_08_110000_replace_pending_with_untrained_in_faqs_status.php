<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

return new class extends Migration {
    public function up(): void
    {
        $driver = config('database.default');

        if ($driver === 'mysql') {
            // Add 'untrained' to enum so the subsequent UPDATE succeeds
            DB::statement("ALTER TABLE `faqs` MODIFY `status` ENUM('pending','untrained','trained','deleted') NOT NULL DEFAULT 'pending'");
        } else {
            // Fallback: convert to string column with the intended default to allow updates.
            Schema::table('faqs', function (Blueprint $table) {
                $table->string('status', 50)->default('untrained')->change();
            });
        }

        // Now safely convert existing rows from 'pending' -> 'untrained'
        DB::table('faqs')->where('status', 'pending')->update(['status' => 'untrained']);

        // Finally, make 'untrained' the default and (optionally) remove 'pending' from the enum on MySQL.
        if ($driver === 'mysql') {
            DB::statement("ALTER TABLE `faqs` MODIFY `status` ENUM('untrained','trained','deleted') NOT NULL DEFAULT 'untrained'");
        } else {
            Schema::table('faqs', function (Blueprint $table) {
                $table->string('status', 50)->default('untrained')->change();
            });
        }
    }

    public function down(): void
    {
        // Revert rows back to 'pending' if they were 'untrained'
        DB::table('faqs')->where('status', 'untrained')->update(['status' => 'pending']);

        $driver = config('database.default');

        if ($driver === 'mysql') {
            DB::statement("ALTER TABLE `faqs` MODIFY `status` ENUM('pending','trained','deleted') NOT NULL DEFAULT 'pending'");
        } else {
            Schema::table('faqs', function (Blueprint $table) {
                $table->string('status', 50)->default('pending')->change();
            });
        }
    }
};