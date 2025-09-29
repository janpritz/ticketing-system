<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFaqsTable extends Migration
{
    public function up()
    {
        Schema::create('faqs', function (Blueprint $table) {
            $table->id();
            $table->string('topic');
            $table->text('response');
            $table->timestamps(); // includes created_at and updated_at
        });
    }

    public function down()
    {
        Schema::dropIfExists('faqs');
    }
}