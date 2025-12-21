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
        Schema::create('queued_documents', function (Blueprint $table) {
            $table->id();
            $table->string('file_name');
            $table->longText('file_content');
            $table->string('file_type', 10)->default('txt');
            $table->enum('status', ['pending', 'processing', 'completed', 'failed'])->default('pending');
            $table->unsignedBigInteger('uploaded_by');
            $table->json('assigned_roles')->nullable();
            $table->enum('operation_type', ['create', 'update', 'delete'])->default('create');
            $table->integer('document_id')->nullable();
            $table->timestamp('uploaded_at')->nullable();
            $table->text('error_message')->nullable();
            $table->integer('retry_count')->default(0);
            $table->timestamp('next_retry_at')->nullable();
            $table->timestamps();

            $table->index('status');
            $table->index('uploaded_by');
            $table->index('next_retry_at');
            $table->foreign('uploaded_by')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('queued_documents');
    }
};
