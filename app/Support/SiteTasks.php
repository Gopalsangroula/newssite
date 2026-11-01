<?php

namespace App\Support;

use App\Models\Page;
use App\Models\Post;
use FilesystemIterator;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use Throwable;

/**
 * The site's regular jobs. They run at most once a minute, after a page has gone out,
 * so no reader waits for them, and a cron job is a bonus rather than a need.
 * Any failure is logged, never shown.
 */
class SiteTasks
{
    private const LOCK = 'site-tasks';
    private const LOCK_SECONDS = 55;
    private const HOURLY_LOCK = 'site-tasks-hourly';
    private const CACHE_EXPIRY_DIGITS = 10;

    public static function runAfterResponse(): void
    {
        app()->terminating(fn () => static::run());
    }

    public static function run(): void
    {
        try {
            if (! Cache::add(self::LOCK, true, self::LOCK_SECONDS)) {
                return;
            }

            static::publishDue();
            ReadCounter::flush();

            if (Cache::add(self::HOURLY_LOCK, true, now()->addHour())) {
                PageCache::prune();
                static::clearExpiredSessions();
                static::clearExpiredCacheFiles();
            }
        } catch (Throwable $e) {
            Log::warning('Site tasks failed: '.$e->getMessage());
        }
    }

    /**
     * Laravel clears these while people use the session pages. Readers no longer do, so the
     * rows, each with an address and browser in it, would otherwise wait a long time.
     */
    private static function clearExpiredSessions(): void
    {
        if (config('session.driver') === 'database') {
            DB::table((string) config('session.table'))
                ->where('last_activity', '<', now()->subMinutes((int) config('session.lifetime'))->timestamp)
                ->delete();
        }
    }

    /**
     * The file cache only removes an expired entry when someone asks for it again. Rate limit
     * entries are never asked for again, so on a busy site they would slowly fill the disk.
     * Each file starts with its expiry time.
     */
    private static function clearExpiredCacheFiles(): void
    {
        if (config('cache.default') !== 'file') {
            return;
        }

        $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator(
            (string) config('cache.stores.file.path'),
            FilesystemIterator::SKIP_DOTS
        ));

        foreach ($files as $file) {
            $expires = (int) @file_get_contents($file->getPathname(), false, null, 0, self::CACHE_EXPIRY_DIGITS);

            if ($expires > 0 && $expires < time()) {
                @unlink($file->getPathname());
            }
        }
    }

    /**
     * Publishes posts and pages once their scheduled time has passed.
     */
    private static function publishDue(): void
    {
        $published = 0;

        foreach ([Post::class, Page::class] as $model) {
            $published += $model::where('status', 'scheduled')
                ->where('published_at', '<=', now())
                ->update(['status' => 'published']);
        }

        if ($published > 0) {
            PageCache::flush();
        }
    }
}
