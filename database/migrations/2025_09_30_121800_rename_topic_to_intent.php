<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Prefer direct ALTER for MySQL
        $driver = config('database.default');

        if ($driver === 'mysql') {
            DB::statement("ALTER TABLE `faqs` CHANGE `topic` `intent` VARCHAR(255) NOT NULL");
            DB::statement("ALTER TABLE `faq_revisions` CHANGE `topic` `intent` VARCHAR(255) NULL");
        } else {
            // Fallback: attempt to rename columns using schema builder (requires doctrine/dbal for change())
            if (Schema::hasColumn('faqs', 'topic') && !Schema::hasColumn('faqs', 'intent')) {
                Schema::table('faqs', function ($table) {
                    $table->string('intent')->after('id');
                });
                DB::statement("UPDATE `faqs` SET `intent` = `topic`");
                Schema::table('faqs', function ($table) {
                    $table->dropColumn('topic');
                });
            }

            if (Schema::hasColumn('faq_revisions', 'topic') && !Schema::hasColumn('faq_revisions', 'intent')) {
                Schema::table('faq_revisions', function ($table) {
                    $table->string('intent')->nullable()->after('faq_id');
                });
                DB::statement("UPDATE `faq_revisions` SET `intent` = `topic`");
                Schema::table('faq_revisions', function ($table) {
                    $table->dropColumn('topic');
                });
            }
        }
    }

    public function down(): void
    {
        $driver = config('database.default');

        if ($driver === 'mysql') {
            DB::statement("ALTER TABLE `faqs` CHANGE `intent` `topic` VARCHAR(255) NOT NULL");
            DB::statement("ALTER TABLE `faq_revisions` CHANGE `intent` `topic` VARCHAR(255) NULL");
        } else {
            if (Schema::hasColumn('faqs', 'intent') && !Schema::hasColumn('faqs', 'topic')) {
                Schema::table('faqs', function ($table) {
                    $table->string('topic')->after('id');
                });
                DB::statement("UPDATE `faqs` SET `topic` = `intent`");
                Schema::table('faqs', function ($table) {
                    $table->dropColumn('intent');
                });
            }

            if (Schema::hasColumn('faq_revisions', 'intent') && !Schema::hasColumn('faq_revisions', 'topic')) {
                Schema::table('faq_revisions', function ($table) {
                    $table->string('topic')->nullable()->after('faq_id');
                });
                DB::statement("UPDATE `faq_revisions` SET `topic` = `intent`");
                Schema::table('faq_revisions', function ($table) {
                    $table->dropColumn('intent');
                });
            }
        }
    }
};