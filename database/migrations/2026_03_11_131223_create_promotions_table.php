<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('promotions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('restaurant_id')->constrained()->cascadeOnDelete();
            $table->string('code');
            $table->integer('discount_percentage');
            $table->date('valid_until')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            // Allow duplicate codes across different restaurants, but unique within one restaurant
            $table->unique(['restaurant_id', 'code']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('promotions');
    }
};
