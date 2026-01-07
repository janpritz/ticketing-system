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
        Schema::create('announcement_roles', function (Blueprint $table) {
            $table->id();

            // Announcement IDs are generated/stored in Rasa's Announcements.txt.
            // They are integers but not foreign keys in our DB.
            $table->unsignedInteger('announcement_id');

            $table->unsignedBigInteger('role_id');
            $table->timestamps();

            $table->unique(['announcement_id', 'role_id']);
            $table->index('role_id');

            $table->foreign('role_id')
                ->references('id')
                ->on('roles')
                ->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('announcement_roles');
    }
};

