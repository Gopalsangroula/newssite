<?php

namespace App\Http\Middleware;

use App\Models\Page;
use App\Models\Post;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

/**
 * Publishes posts and pages once their scheduled time has passed.
 *
 * The site has no cron job, so the first request after that time does the work,
 * before the page is built, which means that visitor already sees the new post.
 * It runs at most once a minute, and any failure is logged, never shown.
 */
class PublishScheduledPosts
{
    private const LOCK = 'publish_scheduled_posts';
    private const LOCK_SECONDS = 60;

    public function handle(Request $request, Closure $next): Response
    {
        try {
            if (Cache::add(self::LOCK, true, self::LOCK_SECONDS)) {
                $this->publishDue();
            }
        } catch (Throwable $e) {
            Log::warning('Publishing scheduled posts failed: '.$e->getMessage());
        }

        return $next($request);
    }

    private function publishDue(): void
    {
        foreach ([Post::class, Page::class] as $model) {
            $model::where('status', 'scheduled')
                ->where('published_at', '<=', now())
                ->update(['status' => 'published']);
        }
    }
}
