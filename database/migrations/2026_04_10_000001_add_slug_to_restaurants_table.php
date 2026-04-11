<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('restaurants', function (Blueprint $table) {
            $table->string('slug')->nullable()->after('name');
        });

        $usedSlugs = [];

        DB::table('restaurants')
            ->select(['id', 'name'])
            ->orderBy('id')
            ->get()
            ->each(function ($restaurant) use (&$usedSlugs) {
                $baseSlug = Str::slug((string) $restaurant->name);
                $baseSlug = $baseSlug !== '' ? $baseSlug : 'restaurant';
                $slug = $baseSlug;
                $suffix = 2;

                while (in_array($slug, $usedSlugs, true)) {
                    $slug = $baseSlug.'-'.$suffix;
                    $suffix++;
                }

                DB::table('restaurants')
                    ->where('id', $restaurant->id)
                    ->update(['slug' => $slug]);

                $usedSlugs[] = $slug;
            });

        Schema::table('restaurants', function (Blueprint $table) {
            $table->unique('slug');
        });
    }

    public function down(): void
    {
        Schema::table('restaurants', function (Blueprint $table) {
            $table->dropUnique(['slug']);
            $table->dropColumn('slug');
        });
    }
};
