<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('document_changes', function (Blueprint $table) {
            $table->id();
            $table->string('file_name');
            $table->enum('action', ['created', 'updated', 'deleted']);
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('user_name')->nullable();
            $table->string('old_content_hash', 64)->nullable();
            $table->string('new_content_hash', 64)->nullable();
            $table->timestamp('change_timestamp')->useCurrent();
            $table->boolean('training_required')->default(true);
            $table->boolean('training_completed')->default(false);
            $table->timestamp('training_timestamp')->nullable();
            $table->string('model_name')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('document_changes');
    }
};
