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
        Schema::table('queued_documents', function (Blueprint $table) {
            // Remove file_content column since we now store files in filesystem
            $table->dropColumn('file_content');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('queued_documents', function (Blueprint $table) {
            // Add file_content column back for rollback
            $table->longText('file_content')->nullable()->after('file_path');
        });
    }
};
