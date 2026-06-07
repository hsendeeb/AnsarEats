<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('category_tags', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('emoji', 20)->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        $now = now();

        DB::table('category_tags')->insert([
            ['slug' => 'sandwich', 'name' => 'Sandwiches', 'emoji' => '🥪', 'sort_order' => 1, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['slug' => 'burger', 'name' => 'Burgers', 'emoji' => '🍔', 'sort_order' => 2, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['slug' => 'pizza', 'name' => 'Pizza', 'emoji' => '🍕', 'sort_order' => 3, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['slug' => 'dessert', 'name' => 'Desserts', 'emoji' => '🍰', 'sort_order' => 4, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['slug' => 'drink', 'name' => 'Drinks', 'emoji' => '🥤', 'sort_order' => 5, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['slug' => 'salad', 'name' => 'Salads', 'emoji' => '🥗', 'sort_order' => 6, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['slug' => 'breakfast', 'name' => 'Breakfast', 'emoji' => '🍳', 'sort_order' => 7, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['slug' => 'pasta', 'name' => 'Pasta', 'emoji' => '🍝', 'sort_order' => 8, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['slug' => 'seafood', 'name' => 'Seafood', 'emoji' => '🦐', 'sort_order' => 9, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['slug' => 'chicken', 'name' => 'Chicken', 'emoji' => '🍗', 'sort_order' => 10, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('category_tags');
    }
};
