<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('faqs', function (Blueprint $table) {
            // Add status with default 'untrained' and allowed values ['untrained', 'trained']
            // Note: On PostgreSQL, enum becomes a CHECK constraint.
            if (!Schema::hasColumn('faqs', 'status')) {
                $table->enum('status', ['untrained', 'trained'])->default('untrained')->after('response');
            }
        });
    }

    public function down(): void
    {
        Schema::table('faqs', function (Blueprint $table) {
            if (Schema::hasColumn('faqs', 'status')) {
                $table->dropColumn('status');
            }
        });
    }
};