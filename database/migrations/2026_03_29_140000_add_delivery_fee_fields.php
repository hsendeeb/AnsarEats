<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('restaurants', function (Blueprint $table) {
            $table->decimal('delivery_fee', 8, 2)->default(0)->after('phone');
        });

        Schema::table('restaurant_registration_requests', function (Blueprint $table) {
            $table->decimal('delivery_fee', 8, 2)->default(0)->after('phone');
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->decimal('delivery_fee', 8, 2)->default(0)->after('total');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn('delivery_fee');
        });

        Schema::table('restaurant_registration_requests', function (Blueprint $table) {
            $table->dropColumn('delivery_fee');
        });

        Schema::table('restaurants', function (Blueprint $table) {
            $table->dropColumn('delivery_fee');
        });
    }
};
