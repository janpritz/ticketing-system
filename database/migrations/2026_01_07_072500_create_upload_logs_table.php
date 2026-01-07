<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('upload_logs', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('staff_id')->nullable();
            $table->string('file_name');
            $table->unsignedBigInteger('file_size')->nullable();
            $table->timestamp('upload_date')->nullable();
            $table->timestamp('server_recieved_date')->nullable();
            $table->timestamps();

            $table->foreign('staff_id')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('upload_logs');
    }
};
