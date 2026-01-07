<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('revision_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('wedding_page_id')->constrained()->onDelete('cascade');
            $table->text('message');
            $table->text('admin_response')->nullable();
            $table->decimal('quoted_price', 10, 2)->nullable();
            $table->boolean('deposit_paid')->default(false);
            $table->boolean('final_paid')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('revision_requests');
    }
};

