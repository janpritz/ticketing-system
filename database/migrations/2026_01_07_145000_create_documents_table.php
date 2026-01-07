<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDocumentsTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        // Guard against the table already existing (safe to re-run in environments where table was created manually)
        if (Schema::hasTable('documents')) {
            return;
        }

        Schema::create('documents', function (Blueprint $table) {
            $table->id();
            $table->string('file_name')->index();
            $table->unsignedBigInteger('role_id')->nullable()->index();
            $table->unsignedBigInteger('created_by')->nullable()->index();
            $table->text('content');
            $table->string('rasa_doc_id')->nullable();
            $table->bigInteger('file_size')->nullable();
            $table->string('file_type')->nullable();
            $table->timestamps();

            // Unique constraint to avoid duplicate file names per creator could be added later.
            $table->unique(['file_name', 'created_by']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::dropIfExists('documents');
    }
}
