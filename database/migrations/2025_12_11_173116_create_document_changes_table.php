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
        Schema::create('document_changes', function (Blueprint $table) {
            $table->id();
            $table->string('file_name', 255);
            $table->enum('action', ['created', 'updated', 'deleted']);
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('user_name', 255)->nullable();
            $table->string('old_content_hash', 64)->nullable();
            $table->string('new_content_hash', 64)->nullable();
            $table->timestamp('change_timestamp')->useCurrent();
            $table->boolean('training_required')->default(true);
            $table->boolean('training_completed')->default(false);
            $table->timestamp('training_timestamp')->nullable();

            $table->index('file_name');
            $table->index('training_required');
            $table->index('change_timestamp');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('document_changes');
    }
};
