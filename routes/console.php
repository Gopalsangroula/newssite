<?php

use App\Support\PageCache;
use App\Support\SiteTasks;
use App\Support\Thumb;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schedule;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('site:tasks', function () {
    SiteTasks::run();
})->purpose('Publish scheduled posts, add up reads and tidy stored pages');

Artisan::command('site:refresh', function () {
    PageCache::flush();
    $this->info('Every stored page will be rebuilt the next time someone opens it.');
})->purpose('Refresh the stored public pages, for example after uploading new files');

Artisan::command('images:thumbs', function () {
    if (! function_exists('imagewebp')) {
        $this->error('This PHP has no GD with WebP, so no copies can be made. Pages keep using the originals.');

        return 1;
    }

    // Run by hand for pictures already on the site, so the largest photos need room.
    ini_set('memory_limit', '1024M');

    $written = 0;
    $pictures = collect(Storage::disk('public')->allFiles())->reject(fn ($file) => str_starts_with($file, 'thumbs/'));

    $this->withProgressBar($pictures, function ($file) use (&$written) {
        $written += Thumb::make($file);
    });

    $this->newLine();
    $this->info($written.' copies written for '.$pictures->count().' files. Nothing already made was redone.');
})->purpose('Make smaller copies of every uploaded picture');

Artisan::command('site:check', function () {
    $url = (string) config('app.url');
    $logs = (array) config('logging.channels.stack.channels');

    $checks = [
        'Debug mode is off (APP_DEBUG=false)' => ! config('app.debug'),
        'The site address is right (APP_URL=https://libang.org.np)' => $url === 'https://libang.org.np',
        'Cache stays out of the database (CACHE_STORE=file)' => config('cache.default') !== 'database',
        'Logs rotate instead of growing (LOG_STACK=daily)' => in_array('daily', $logs, true),
        'Logs skip routine detail (LOG_LEVEL=warning)' => in_array(config('logging.channels.daily.level'), ['warning', 'error'], true),
        'Settings are cached (php artisan config:cache)' => app()->configurationIsCached(),
        'Routes are cached (php artisan route:cache)' => app()->routesAreCached(),
        'Pages can be stored (storage/framework is writable)' => is_writable(storage_path('framework')),
        'Uploads are reachable (php artisan storage:link)' => is_dir(public_path('storage')),
        'Picture copies can be made (PHP GD with WebP)' => function_exists('imagewebp'),
        'Picture copies exist (php artisan images:thumbs)' => Storage::disk('public')->exists('thumbs'),
        'The database answers' => rescue(fn () => (bool) DB::connection()->getPdo(), false, false),
        'The search index is there (php artisan migrate --force)' => rescue(fn () => Schema::hasIndex('posts', 'posts_search'), false, false),
        'The old genealogy and visitor tables are gone (see docs/vps-setup.md)' => rescue(fn () => ! Schema::hasTable('visits') && ! Schema::hasTable('genealogies'), false, false),
    ];

    foreach ($checks as $check => $passed) {
        $this->line(($passed ? '<info>  ok   </info>' : '<error> FIX  </error>').' '.$check);
    }

    return in_array(false, $checks, true) ? 1 : 0;
})->purpose('Check that this server is set up for a busy site');

// Optional, because the site also runs these itself. See docs/vps-setup.md.
Schedule::command('site:tasks')->everyMinute()->withoutOverlapping();
