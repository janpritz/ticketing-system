<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Set existing users' email_notifications to false (0)
        if (Schema::hasTable('users')) {
            \Illuminate\Support\Facades\DB::table('users')->update(['email_notifications' => 0]);
        }
    }

    public function down(): void
    {
        // No-op: cannot reliably revert original values; leave as-is
    }
};

