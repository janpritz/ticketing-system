<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('tickets', function (Blueprint $table) {
            $table->id();
            $table->string('category'); // for categories like "Enrollment Issues", "Finance", etc.
            $table->text('question');  // for the user's question or issue
            $table->string('recepient_id');
            $table->string('email'); // to store the email of the user who created the ticket
            $table->enum('status', ['Open', 'In-Progress', 'Closed'])->default('Open'); // status of the ticket
            $table->foreignId('staff_id')->nullable()->constrained('users'); // assuming staff is also a user
            $table->timestamp('date_created')->useCurrent(); // automatically set the creation date
            $table->timestamp('date_closed')->nullable(); // to track when the ticket is closed, if applicable
            $table->timestamps(); // created_at and updated_at
        });
    }



    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tickets');
    }
};
