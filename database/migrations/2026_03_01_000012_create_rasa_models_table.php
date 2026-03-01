<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rasa_models', function (Blueprint $table) {
            $table->id();
            $table->string('model_name')->unique();
            $table->bigInteger('size')->nullable();
            $table->boolean('is_current')->default(false);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rasa_models');
    }
};
