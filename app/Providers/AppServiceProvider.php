<?php

namespace App\Providers;

use App\Models\Advertisement;
use App\Models\Category;
use App\Models\Comment;
use App\Models\MetalRate;
use App\Models\Page;
use App\Models\PatroEvent;
use App\Models\Post;
use App\Models\Rashifal;
use App\Models\SiteSetting;
use App\Models\Tag;
use App\Support\PageCache;
use App\Support\Thumb;
use App\Support\Visitor;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;
use Spatie\Permission\Middleware\PermissionMiddleware;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Per visitor. Stored pages never count, so a reader only meets these when something is
     * hammering the site. They are generous because many Nepali mobile users share one address.
     */
    private const BUILT_PAGES_PER_MINUTE = 300;
    private const SEARCHES_PER_MINUTE = 60;
    private const COMMENT_POSTS_PER_MINUTE = 30;

    /**
     * Everything shown on the public pages, so saving any of it refreshes them.
     */
    private const PUBLIC_CONTENT = [
        Post::class,
        Page::class,
        Category::class,
        Tag::class,
        Advertisement::class,
        MetalRate::class,
        PatroEvent::class,
        Rashifal::class,
        SiteSetting::class,
    ];

    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Super-admin can always open everything, so nobody can be locked out of the Roles page.
        Gate::before(fn ($user) => $user->hasRole('super-admin') ? true : null);

        // Re-check the page's permission on every Livewire action, not just the first page load.
        Livewire::addPersistentMiddleware([PermissionMiddleware::class]);

        // Cloudflare may reach this server over plain http, and the links must still be https.
        if (str_starts_with((string) config('app.url'), 'https://') && strcasecmp($_SERVER['HTTP_HOST'] ?? '', (string) parse_url((string) config('app.url'), PHP_URL_HOST)) === 0) {
            URL::forceScheme('https');
        }

        RateLimiter::for('public-pages', fn (Request $request) => Limit::perMinute(self::BUILT_PAGES_PER_MINUTE)->by(Visitor::limitKey($request)));
        RateLimiter::for('search', fn (Request $request) => Limit::perMinute(self::SEARCHES_PER_MINUTE)->by(Visitor::limitKey($request)));
        RateLimiter::for('comments', fn (Request $request) => Limit::perMinute(self::COMMENT_POSTS_PER_MINUTE)->by(Visitor::limitKey($request)));

        $this->keepStoredPagesCurrent();
    }

    private function keepStoredPagesCurrent(): void
    {
        foreach (self::PUBLIC_CONTENT as $model) {
            $model::saved(fn () => PageCache::flush());
            $model::deleted(fn () => PageCache::flush());
        }

        // Only comments readers can see change a page. New ones wait for approval.
        Comment::saved(fn (Comment $comment) => $comment->is_approved || $comment->wasChanged('is_approved') ? PageCache::flush() : null);
        Comment::deleted(fn () => PageCache::flush());

        // A post taken down must leave the stored pages at once, not when its turn comes.
        Post::saved(function (Post $post) {
            if (! $post->isPublished()) {
                PageCache::forget('/details/'.$post->slug);
            }

            Thumb::makeAfterResponse($post->cover_image, $post->content);
        });

        Post::deleted(fn (Post $post) => PageCache::forget('/details/'.$post->slug));

        Page::saved(fn (Page $page) => Thumb::makeAfterResponse($page->cover_image, $page->content));
    }
}
