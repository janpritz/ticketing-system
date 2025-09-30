<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('faq_revisions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('faq_id')->constrained('faqs')->onDelete('cascade');
            $table->string('topic')->nullable();
            $table->longText('response')->nullable();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('action', 32); // create | update | delete | revert | reverted_to
            $table->json('meta')->nullable();
            $table->timestamps();

            $table->index(['faq_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('faq_revisions');
    }
};