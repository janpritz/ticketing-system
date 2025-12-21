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
            // Add file_path column to store the path to the file in storage
            $table->string('file_path')->nullable()->after('file_content');
            
            // Make file_content nullable for backward compatibility during transition
            $table->longText('file_content')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('queued_documents', function (Blueprint $table) {
            // Remove file_path column
            $table->dropColumn('file_path');
            
            // Make file_content non-nullable again
            $table->longText('file_content')->nullable(false)->change();
        });
    }
};
