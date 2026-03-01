<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tickets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->nullable()->constrained('categories')->nullOnDelete();
            $table->text('question');
            $table->text('response')->nullable();
            $table->string('recepient_id');
            $table->string('email');
            $table->enum('status', ['Open', 'Forwarded', 'Closed'])->default('Open');
            $table->foreignId('staff_id')->nullable()->constrained('users');
            $table->timestamp('date_created')->useCurrent();
            $table->timestamp('date_closed')->nullable();
            $table->timestamp('first_viewed_at')->nullable();
            $table->foreignId('first_viewed_by')->nullable()->constrained('users');
            $table->timestamps();
            $table->json('attachments')->nullable();
            $table->boolean('is_processed')->default(false);
            $table->foreignId('role_id')->nullable()->constrained('roles')->nullOnDelete();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tickets');
    }
};
