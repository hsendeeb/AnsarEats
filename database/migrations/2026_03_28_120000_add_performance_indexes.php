<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->index(['restaurant_id', 'status', 'id'], 'orders_restaurant_status_id_idx');
            $table->index(['restaurant_id', 'status', 'created_at'], 'orders_restaurant_status_created_idx');
            $table->index(['user_id', 'created_at'], 'orders_user_created_idx');
        });

        Schema::table('menu_categories', function (Blueprint $table) {
            $table->index(['restaurant_id', 'is_visible', 'sort_order'], 'menu_categories_restaurant_visible_sort_idx');
        });

        Schema::table('menu_items', function (Blueprint $table) {
            $table->index(['menu_category_id', 'is_available'], 'menu_items_category_available_idx');
            $table->index(['is_available', 'is_featured'], 'menu_items_available_featured_idx');
        });

        Schema::table('restaurants', function (Blueprint $table) {
            $table->index(['is_open', 'created_at'], 'restaurants_open_created_idx');
        });

        Schema::table('ratings', function (Blueprint $table) {
            $table->index(['restaurant_id', 'created_at'], 'ratings_restaurant_created_idx');
        });

        Schema::table('promotions', function (Blueprint $table) {
            $table->index(['restaurant_id', 'is_active', 'valid_until'], 'promotions_restaurant_active_valid_idx');
        });
    }

    public function down(): void
    {
        Schema::table('promotions', function (Blueprint $table) {
            $table->dropIndex('promotions_restaurant_active_valid_idx');
        });

        Schema::table('ratings', function (Blueprint $table) {
            $table->dropIndex('ratings_restaurant_created_idx');
        });

        Schema::table('restaurants', function (Blueprint $table) {
            $table->dropIndex('restaurants_open_created_idx');
        });

        Schema::table('menu_items', function (Blueprint $table) {
            $table->dropIndex('menu_items_category_available_idx');
            $table->dropIndex('menu_items_available_featured_idx');
        });

        Schema::table('menu_categories', function (Blueprint $table) {
            $table->dropIndex('menu_categories_restaurant_visible_sort_idx');
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->dropIndex('orders_restaurant_status_id_idx');
            $table->dropIndex('orders_restaurant_status_created_idx');
            $table->dropIndex('orders_user_created_idx');
        });
    }
};
