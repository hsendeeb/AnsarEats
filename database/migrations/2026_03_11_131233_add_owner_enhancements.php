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
        Schema::table('orders', function (Blueprint $table) {
            $table->text('rejection_reason')->nullable();
            $table->integer('estimated_prep_time')->nullable(); 
        });

        Schema::table('menu_items', function (Blueprint $table) {
            $table->boolean('is_featured')->default(false);
        });

        Schema::table('menu_categories', function (Blueprint $table) {
            $table->boolean('is_visible')->default(true);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['rejection_reason', 'estimated_prep_time']);
        });

        Schema::table('menu_items', function (Blueprint $table) {
            $table->dropColumn('is_featured');
        });

        Schema::table('menu_categories', function (Blueprint $table) {
            $table->dropColumn('is_visible');
        });
    }
};
