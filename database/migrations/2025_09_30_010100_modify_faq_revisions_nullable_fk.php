<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class ModifyFaqRevisionsNullableFk extends Migration
{
    /**
     * Make faq_revisions.faq_id nullable and use nullOnDelete without requiring doctrine/dbal.
     *
     * Strategy:
     *  - Drop existing FK
     *  - Add a temporary nullable column faq_id_tmp
     *  - Copy values from faq_id -> faq_id_tmp
     *  - Drop the original faq_id column
     *  - Add a new nullable faq_id column
     *  - Copy values back from faq_id_tmp -> faq_id
     *  - Drop faq_id_tmp
     *  - Add FK with nullOnDelete
     */
    public function up()
    {
        // 1) Drop existing foreign key if present
        Schema::table('faq_revisions', function (Blueprint $table) {
            // dropForeign will throw if the constraint doesn't exist; guard with try/catch
            try {
                $table->dropForeign(['faq_id']);
            } catch (\Throwable $e) {
                // ignore if it doesn't exist
            }
        });

        // 2) Add temporary column
        Schema::table('faq_revisions', function (Blueprint $table) {
            $table->unsignedBigInteger('faq_id_tmp')->nullable();
        });

        // 3) Copy data into temporary column
        DB::statement('UPDATE faq_revisions SET faq_id_tmp = faq_id');

        // 4) Drop original column (and any dangling index)
        Schema::table('faq_revisions', function (Blueprint $table) {
            // If an index exists on faq_id it will be dropped with the column.
            $table->dropColumn('faq_id');
        });

        // 5) Recreate faq_id as nullable
        Schema::table('faq_revisions', function (Blueprint $table) {
            $table->unsignedBigInteger('faq_id')->nullable();
        });

        // 6) Copy data back
        DB::statement('UPDATE faq_revisions SET faq_id = faq_id_tmp');

        // 7) Drop temporary column
        Schema::table('faq_revisions', function (Blueprint $table) {
            $table->dropColumn('faq_id_tmp');
        });

        // 8) Add new foreign key with nullOnDelete
        Schema::table('faq_revisions', function (Blueprint $table) {
            $table->foreign('faq_id')->references('id')->on('faqs')->nullOnDelete();
        });
    }

    /**
     * Reverse the migration: restore NOT NULL + cascade behavior.
     * This uses the same temporary-column strategy to avoid ->change().
     */
    public function down()
    {
        // 1) Drop FK if present
        Schema::table('faq_revisions', function (Blueprint $table) {
            try {
                $table->dropForeign(['faq_id']);
            } catch (\Throwable $e) {
                // ignore
            }
        });

        // 2) Add temporary column
        Schema::table('faq_revisions', function (Blueprint $table) {
            $table->unsignedBigInteger('faq_id_tmp')->nullable();
        });

        // 3) Copy data
        DB::statement('UPDATE faq_revisions SET faq_id_tmp = faq_id');

        // 4) Drop current faq_id
        Schema::table('faq_revisions', function (Blueprint $table) {
            $table->dropColumn('faq_id');
        });

        // 5) Recreate faq_id as non-nullable (note: if there are nulls this will fail; that's expected)
        Schema::table('faq_revisions', function (Blueprint $table) {
            $table->unsignedBigInteger('faq_id')->nullable(false);
        });

        // 6) Copy data back
        DB::statement('UPDATE faq_revisions SET faq_id = faq_id_tmp');

        // 7) Drop temp
        Schema::table('faq_revisions', function (Blueprint $table) {
            $table->dropColumn('faq_id_tmp');
        });

        // 8) Recreate FK cascade
        Schema::table('faq_revisions', function (Blueprint $table) {
            $table->foreign('faq_id')->references('id')->on('faqs')->onDelete('cascade');
        });
    }
}