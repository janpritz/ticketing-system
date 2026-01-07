<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Creates announcements table to store announcements locally and avoid relying on Rasa file for persistence.
     */
    public function up(): void
    {
        Schema::create('announcements', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('title', 255)->unique();
            $table->text('content');
            // store the role_id the announcement targets (nullable for broadcast to all)
            $table->unsignedBigInteger('role_id')->nullable();
            // user who created the announcement
            $table->unsignedBigInteger('created_by')->nullable();
            $table->boolean('pinned')->default(false);
            $table->timestamps();

            // indexes
            $table->index('role_id');
            $table->index('created_by');
        });

        // Note: announcement_roles pivot table may still be used for legacy behaviour or multi-role mapping.
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('announcements');
    }
};
