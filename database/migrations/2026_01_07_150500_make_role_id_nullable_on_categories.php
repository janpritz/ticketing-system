<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class MakeRoleIdNullableOnCategories extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        // Try the Laravel fluent change (may require doctrine/dbal). Fall back to raw SQL if necessary.
        try {
            Schema::table('categories', function (Blueprint $table) {
                $table->unsignedBigInteger('role_id')->nullable()->change();
            });
        } catch (\Throwable $e) {
            // Fallback for MySQL: alter the column to allow NULL
            try {
                DB::statement('ALTER TABLE `categories` MODIFY `role_id` BIGINT UNSIGNED NULL');
            } catch (\Throwable $ex) {
                // Log and continue; migration should not crash the deploy in some environments
                echo "Warning: Could not alter categories.role_id to nullable: {$ex->getMessage()}\n";
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        try {
            Schema::table('categories', function (Blueprint $table) {
                $table->unsignedBigInteger('role_id')->nullable(false)->change();
            });
        } catch (\Throwable $e) {
            try {
                DB::statement('ALTER TABLE `categories` MODIFY `role_id` BIGINT UNSIGNED NOT NULL');
            } catch (\Throwable $ex) {
                echo "Warning: Could not revert categories.role_id to NOT NULL: {$ex->getMessage()}\n";
            }
        }
    }
}
