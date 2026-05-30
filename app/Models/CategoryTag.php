<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class CategoryTag extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'emoji',
        'image',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    protected static function booted(): void
    {
        static::saving(function (CategoryTag $tag): void {
            $baseSlug = Str::slug($tag->slug ?: $tag->name);
            $slug = $baseSlug;
            $counter = 2;

            while (static::query()
                ->where('slug', $slug)
                ->when($tag->exists, fn ($query) => $query->whereKeyNot($tag->getKey()))
                ->exists()) {
                $slug = "{$baseSlug}-{$counter}";
                $counter++;
            }

            $tag->slug = $slug;
        });

        static::deleted(function (CategoryTag $tag): void {
            MenuItem::query()
                ->whereJsonContains('tags', $tag->slug)
                ->get()
                ->each(function (MenuItem $item) use ($tag): void {
                    $item->update([
                        'tags' => collect($item->tags)
                            ->reject(fn (string $slug): bool => $slug === $tag->slug)
                            ->values()
                            ->all(),
                    ]);
                });
        });
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
