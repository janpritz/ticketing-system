<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class DropAnnouncementRolesTable extends Migration
{
    /**
     * Run the migrations.
     * Drops the announcement_roles pivot table if it exists.
     */
    public function up()
    {
        Schema::dropIfExists('announcement_roles');
    }

    /**
     * Reverse the migrations.
     * Recreates the announcement_roles table with basic columns in case a rollback is needed.
     */
    public function down()
    {
        Schema::create('announcement_roles', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('announcement_id')->index();
            $table->unsignedBigInteger('role_id')->index();
            $table->timestamps();
        });
    }
}
