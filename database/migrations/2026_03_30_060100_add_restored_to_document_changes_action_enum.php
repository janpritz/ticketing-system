<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE document_changes MODIFY action ENUM('created', 'updated', 'deleted', 'restored') NOT NULL");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE document_changes MODIFY action ENUM('created', 'updated', 'deleted') NOT NULL");
    }
};
