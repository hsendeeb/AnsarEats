<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            $table->string('variant_label')->nullable()->after('name');
        });

        Schema::table('restaurants', function (Blueprint $table) {
            $table->json('operating_hours')->nullable()->after('description');
        });
    }

    public function down(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            $table->dropColumn('variant_label');
        });

        Schema::table('restaurants', function (Blueprint $table) {
            $table->dropColumn('operating_hours');
        });
    }
};
