<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('documents', function (Blueprint $table) {
            $table->id();
            $table->string('file_name');
            $table->foreignId('role_id')->nullable()->constrained('roles')->nullOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('content');
            $table->string('rasa_doc_id')->nullable();
            $table->bigInteger('file_size')->nullable();
            $table->string('file_type')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['file_name', 'created_by']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('documents');
    }
};
