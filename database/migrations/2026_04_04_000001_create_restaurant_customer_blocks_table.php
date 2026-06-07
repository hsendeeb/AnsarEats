<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('restaurant_customer_blocks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('restaurant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('blocked_by_user_id')->constrained('users')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['restaurant_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('restaurant_customer_blocks');
    }
};
