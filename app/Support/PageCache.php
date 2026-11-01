<?php

namespace App\Support;

/**
 * Stored copies of public pages, answered before Laravel starts. A stored page costs the
 * server almost nothing, so a traffic spike never reaches the framework or the database.
 *
 * Only pages built by App\Http\Middleware\ServePublicPage land here. Those routes run without
 * sessions, so a stored page can never hold anything belonging to one person.
 *
 * public/index.php loads this file by hand, so nothing here may depend on Laravel.
 */
class PageCache
{
    /**
     * How long a stored page counts as fresh. Saving anything in the admin refreshes pages sooner.
     */
    public const FRESH_SECONDS = 300;

    /**
     * For browsers and Cloudflare. If this server is ever down, Cloudflare keeps showing its last copy.
     */
    public const CACHE_CONTROL = 'public, max-age=30, s-maxage=60, stale-while-revalidate=60, stale-if-error=86400';

    public const SECURITY_HEADERS = [
        'X-Content-Type-Options' => 'nosniff',
        'X-Frame-Options' => 'SAMEORIGIN',
        'Referrer-Policy' => 'strict-origin-when-cross-origin',
        'Permissions-Policy' => 'camera=(), microphone=(), geolocation=(), payment=()',
    ];

    public const STRICT_TRANSPORT = 'max-age=31536000';

    /**
     * Public pages only. Script advertisements and embeds in posts load from outside hosts,
     * so those stay open to https, and everything else is shut.
     */
    public const CONTENT_POLICY = "default-src 'self'; script-src 'self' 'unsafe-inline' https:; style-src 'self' 'unsafe-inline' https:; "
        ."font-src 'self' https: data:; img-src 'self' https: data:; media-src 'self' https:; frame-src 'self' https:; connect-src 'self' https:; "
        ."object-src 'none'; base-uri 'self'; form-action 'self'; frame-ancestors 'self'";

    /**
     * These depend on what the reader typed, or on the moment they ask, so they are always built.
     */
    private const NEVER_STORED = ['/search', '/tools/date-converter', '/up'];

    /**
     * Addresses with numbered pages: categories, tags and authors.
     */
    private const LISTING_PATH = '~^/(?:author/|tag/)?(?!tools$|saved-posts$)[^/.]+$~D';

    private const LONGEST_PATH = 200;
    private const REBUILD_LOCK_SECONDS = 30;
    private const TASK_SECONDS = 60;
    private const KEEP_SECONDS = 86400;

    /**
     * Sends the stored page and stops, or returns so that Laravel can build it.
     */
    public static function answer(): void
    {
        $method = $_SERVER['REQUEST_METHOD'] ?? '';
        $uri = $_SERVER['REQUEST_URI'] ?? '/';
        $file = $method === 'GET' || $method === 'HEAD' ? self::fileFor($uri) : null;
        $storedAt = $file ? @filemtime($file) : false;

        if ($storedAt === false) {
            return;
        }

        $isFresh = time() - $storedAt < self::FRESH_SECONDS && $storedAt > (int) @filemtime(self::flushMarker());

        // One request rebuilds a stale page while everyone else gets the stored copy straight away.
        if (! $isFresh && self::claimRebuild($file)) {
            return;
        }

        if (self::tasksAreDue()) {
            return;
        }

        self::send($file, $storedAt, $uri, $method === 'HEAD');
    }

    /**
     * Whether a page built for this address may be stored. /%6Eews opens the same page as
     * /news, but the page would carry that spelling, so only the plain address counts.
     */
    public static function accepts(string $uri): bool
    {
        $path = (string) parse_url($uri, PHP_URL_PATH);
        $plain = implode('/', array_map('rawurlencode', explode('/', rawurldecode($path))));

        return $path === $plain && self::fileFor($uri) !== null;
    }

    public static function store(string $uri, string $body, int $builtAt): void
    {
        $file = self::fileFor($uri);

        if ($file === null) {
            return;
        }

        @mkdir(dirname($file), 0775, true);

        // Written beside the page and moved into place, so nobody ever reads half a page.
        $temporary = $file.'.'.getmypid().'.tmp';

        if (@file_put_contents($temporary, $body) === false) {
            return;
        }

        // Dated from when the build began, so a save during the build still counts as newer.
        @touch($temporary, $builtAt);

        if (! @rename($temporary, $file)) {
            @unlink($temporary);
        }
    }

    public static function copyOf(string $uri): ?string
    {
        $file = self::fileFor($uri);
        $body = $file ? @file_get_contents($file) : false;

        return $body === false ? null : $body;
    }

    public static function contentType(string $uri): string
    {
        return str_ends_with((string) parse_url($uri, PHP_URL_PATH), '.xml')
            ? 'application/xml; charset=UTF-8'
            : 'text/html; charset=UTF-8';
    }

    public static function forget(string $uri): void
    {
        if ($file = self::fileFor($uri)) {
            @unlink($file);
        }
    }

    public static function release(string $uri): void
    {
        if ($file = self::fileFor($uri)) {
            @rmdir($file.'.lock');
        }
    }

    /**
     * Marks every stored page as out of date. Each is rebuilt the next time someone asks for it.
     */
    public static function flush(): void
    {
        @mkdir(self::folder(), 0775, true);
        @touch(self::flushMarker());
    }

    /**
     * Removes pages nobody has asked for in a day, and anything left by a build that died.
     */
    public static function prune(): int
    {
        $removed = 0;

        foreach (glob(self::folder().'/*', GLOB_ONLYDIR) ?: [] as $folder) {
            foreach (glob($folder.'/*') ?: [] as $entry) {
                if (time() - (int) @filemtime($entry) < self::KEEP_SECONDS) {
                    continue;
                }

                $removed += is_dir($entry) ? (int) @rmdir($entry) : (int) @unlink($entry);
            }
        }

        return $removed;
    }

    private static function folder(): string
    {
        return dirname(__DIR__, 2).'/storage/framework/page-cache';
    }

    private static function flushMarker(): string
    {
        return self::folder().'/.flushed';
    }

    /**
     * The stored file for an address, or null when that address is never stored.
     * The name is a hash, so nothing a visitor types reaches the file system, and it
     * includes the host, so a page is only ever served to the host it was built for.
     */
    private static function fileFor(string $uri): ?string
    {
        $parts = parse_url($uri);
        $path = isset($parts['path']) ? rawurldecode($parts['path']) : null;

        if ($path === null || strlen($path) > self::LONGEST_PATH || in_array($path, self::NEVER_STORED, true)) {
            return null;
        }

        parse_str($parts['query'] ?? '', $query);

        $host = strtolower((string) ($_SERVER['HTTP_HOST'] ?? ''));
        $name = md5($host.$path.'?'.http_build_query(self::meaningfulQuery($path, $query)));

        return self::folder().'/'.substr($name, 0, 2).'/'.$name;
    }

    /**
     * The query values that really change this page. Everything else, such as a Facebook click
     * id or /?page=123, is left out, so it gets the same stored page and cannot force a rebuild.
     * These read numbers with filter_var, as the pager does, so the two can never disagree
     * about which page an address means.
     */
    private static function meaningfulQuery(string $path, array $query): array
    {
        if ($path === '/tools/patro') {
            return array_filter([
                'year' => filter_var($query['year'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 2000, 'max_range' => 2099]]),
                'month' => filter_var($query['month'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1, 'max_range' => 12]]),
            ]);
        }

        if (preg_match(self::LISTING_PATH, $path) === 1) {
            return array_filter([
                'page' => filter_var($query['page'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 2]]),
            ]);
        }

        return [];
    }

    private static function claimRebuild(string $file): bool
    {
        $lock = $file.'.lock';

        if (@mkdir($lock)) {
            return true;
        }

        // The request holding this lock died, so the next one takes over.
        if (time() - (int) @filemtime($lock) > self::REBUILD_LOCK_SECONDS) {
            @rmdir($lock);

            return @mkdir($lock);
        }

        return false;
    }

    /**
     * About once a minute one visitor goes through Laravel, which runs the site's tasks
     * after answering them. The site then needs no cron job, even when every page is fresh.
     */
    private static function tasksAreDue(): bool
    {
        $marker = self::folder().'/.tasks';

        return time() - (int) @filemtime($marker) >= self::TASK_SECONDS && @touch($marker);
    }

    private static function send(string $file, int $storedAt, string $uri, bool $headersOnly): never
    {
        header_remove('X-Powered-By');

        foreach (self::SECURITY_HEADERS as $name => $value) {
            header($name.': '.$value);
        }

        if (($_SERVER['HTTPS'] ?? '') === 'on' || ($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https') {
            header('Strict-Transport-Security: '.self::STRICT_TRANSPORT);
        }

        $modified = gmdate('D, d M Y H:i:s', $storedAt).' GMT';

        header('Content-Security-Policy: '.self::CONTENT_POLICY);
        header('Cache-Control: '.self::CACHE_CONTROL);
        header('Last-Modified: '.$modified);
        header('X-Page-Cache: hit');

        if (($_SERVER['HTTP_IF_MODIFIED_SINCE'] ?? '') === $modified) {
            http_response_code(304);
            exit;
        }

        header('Content-Type: '.self::contentType($uri));
        header('Content-Length: '.filesize($file));

        if (! $headersOnly) {
            readfile($file);
        }

        exit;
    }
}
