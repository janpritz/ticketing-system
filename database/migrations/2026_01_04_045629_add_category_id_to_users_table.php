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
        Schema::table('users', function (Blueprint $table) {
            // Add nullable foreign key to categories
            $table->unsignedBigInteger('category_id')->nullable()->after('role_id');
            $table->foreign('category_id')->references('id')->on('categories')->nullOnDelete();

            // Drop the old string 'category' column if it exists
            if (Schema::hasColumn('users', 'category')) {
                $table->dropColumn('category');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Recreate the legacy 'category' string column
            if (! Schema::hasColumn('users', 'category')) {
                $table->string('category')->nullable()->after('role_id');
            }

            // Drop foreign key and column
            if (Schema::hasColumn('users', 'category_id')) {
                $table->dropForeign(['category_id']);
                $table->dropColumn('category_id');
            }
        });
    }
};

