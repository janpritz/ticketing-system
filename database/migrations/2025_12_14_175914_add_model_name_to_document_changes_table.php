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
        Schema::table('document_changes', function (Blueprint $table) {
            $table->string('model_name', 255)->nullable()->after('training_timestamp');
            $table->index('model_name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('document_changes', function (Blueprint $table) {
            $table->dropIndex(['model_name']);
            $table->dropColumn('model_name');
        });
    }
};
