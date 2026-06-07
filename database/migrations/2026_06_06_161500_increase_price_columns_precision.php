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
        Schema::table('menu_items', function (Blueprint $table) {
            $table->decimal('price', 16, 2)->change();
            $table->decimal('sale_price', 16, 2)->nullable()->change();
        });

        Schema::table('order_items', function (Blueprint $table) {
            $table->decimal('price', 16, 2)->change();
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->decimal('total', 16, 2)->change();
            $table->decimal('discount_amount', 16, 2)->default(0)->change();
            $table->decimal('delivery_fee', 16, 2)->default(0)->change();
        });

        Schema::table('restaurants', function (Blueprint $table) {
            $table->decimal('delivery_fee', 16, 2)->default(0)->change();
        });

        Schema::table('restaurant_registration_requests', function (Blueprint $table) {
            $table->decimal('delivery_fee', 16, 2)->default(0)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('menu_items', function (Blueprint $table) {
            $table->decimal('price', 8, 2)->change();
            $table->decimal('sale_price', 8, 2)->nullable()->change();
        });

        Schema::table('order_items', function (Blueprint $table) {
            $table->decimal('price', 8, 2)->change();
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->decimal('total', 10, 2)->change();
            $table->decimal('discount_amount', 8, 2)->default(0)->change();
            $table->decimal('delivery_fee', 8, 2)->default(0)->change();
        });

        Schema::table('restaurants', function (Blueprint $table) {
            $table->decimal('delivery_fee', 8, 2)->default(0)->change();
        });

        Schema::table('restaurant_registration_requests', function (Blueprint $table) {
            $table->decimal('delivery_fee', 8, 2)->default(0)->change();
        });
    }
};
