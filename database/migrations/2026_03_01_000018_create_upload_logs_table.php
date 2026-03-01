<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('upload_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('staff_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('file_name');
            $table->bigInteger('file_size')->nullable();
            $table->timestamp('upload_date')->nullable();
            $table->timestamp('server_recieved_date')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('upload_logs');
    }
};
