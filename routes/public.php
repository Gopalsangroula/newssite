<?php

use App\Http\Controllers\CommentController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\SiteController;
use App\Http\Controllers\SitemapController;
use App\Http\Controllers\ToolsController;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;

/*
 * The public site, loaded from bootstrap/app.php outside the "web" group. No session starts and
 * no cookie is set here, so finished pages can be stored and Cloudflare can cache them.
 * Anything that needs to know who is asking belongs in routes/web.php.
 */

Route::get('/sitemap.xml', SitemapController::class)->name('sitemap');
Route::redirect('/home', '/', 301)->name('home');

// For an uptime monitor: 200 while the site can reach its database.
Route::get('/up', function () {
    DB::connection()->getPdo();

    return 'OK';
});

Route::name('frontend.')->group(function () {
    // Home page
    Route::get('/', [HomeController::class, 'index'])->name('welcome');

    // Single Post page (by slug)
    Route::get('/details/{slug}', [SiteController::class, 'post'])->name('details.show');

    // Static pages
    Route::get('/pages/{slug}', [SiteController::class, 'page'])->name('frontend.pages.show');

    // Author Archive
    Route::get('/author/{slug}', [SiteController::class, 'author'])->name('author.show');

    // Tag Archive
    Route::get('/tag/{slug}', [SiteController::class, 'tag'])->name('tag.show');

    // Search and the reader's saved posts
    Route::get('/search', [SiteController::class, 'search'])->middleware('throttle:search')->name('search');
    Route::view('/saved-posts', 'saved-posts')->name('saved');

    // Tools
    Route::prefix('tools')->name('tools.')->controller(ToolsController::class)->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('/forex', 'forex')->name('forex');
        Route::get('/gold-silver', 'goldSilver')->name('gold');
        Route::get('/weather', 'weather')->name('weather');
        Route::get('/air-quality', 'airQuality')->name('air');
        Route::get('/patro', 'patro')->name('patro');
        Route::get('/rashifal', 'rashifal')->name('rashifal');
        Route::get('/date-converter', 'dateConverter')->name('converter');
    });

    // Category Archive (catch-all slug) — KEEP THIS LAST so it doesn't override other specific routes.
    // No dots, so probes for files such as wp-login.php are turned away without a database lookup.
    Route::get('/{slug}', [SiteController::class, 'category'])->where('slug', '[^./]+')->name('category.show');
});

Route::post('/comments/{post}', [CommentController::class, 'store'])
    ->whereNumber('post')
    ->middleware('throttle:comments')
    ->name('comments.store');
