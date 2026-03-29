<?php

namespace App\Support;

use Closure;
use Illuminate\Support\Facades\Cache;

class PerformanceCache
{
    private const VERSION_PREFIX = 'perf:version:';

    public static function remember(string $bucket, string $suffix, \DateTimeInterface|\DateInterval|int $ttl, Closure $callback): mixed
    {
        return Cache::remember(self::key($bucket, $suffix), $ttl, $callback);
    }

    public static function bump(array|string $buckets): void
    {
        foreach ((array) $buckets as $bucket) {
            $versionKey = self::versionKey($bucket);

            if (!Cache::add($versionKey, 1, now()->addYear())) {
                Cache::increment($versionKey);
            }
        }
    }

    public static function key(string $bucket, string $suffix): string
    {
        return sprintf('perf:%s:v%s:%s', $bucket, self::version($bucket), sha1($suffix));
    }

    public static function version(string $bucket): int
    {
        return (int) Cache::rememberForever(self::versionKey($bucket), fn () => 1);
    }

    private static function versionKey(string $bucket): string
    {
        return self::VERSION_PREFIX.$bucket;
    }
}
