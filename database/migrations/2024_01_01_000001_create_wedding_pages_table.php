<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('wedding_pages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('slug')->unique();
            $table->json('content_json');
            $table->json('design_settings');
            $table->json('ai_settings')->nullable();
            $table->enum('status', ['draft', 'preview', 'purchased', 'live', 'expired'])->default('draft');
            $table->decimal('price', 10, 2)->default(300.00);
            $table->date('available_from')->nullable();
            $table->date('available_until')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wedding_pages');
    }
};

