<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

/**
 * Adds one to a post's view count when a reader opens it. Nothing about the reader is stored.
 *
 * The count goes up in terminate(), after the response has been sent, so it cannot
 * slow a page down, and any failure is logged, never shown.
 */
class CountPostView
{
    private const SHOULD_COUNT = 'count_post_view';

    /**
     * Crawlers, link previews, monitors and scripts, matched against the user agent.
     * "bot", "spider" and "preview" already cover most named crawlers.
     */
    private const BOT_PATTERN = '/bot|crawl|spider|slurp|scrap|headless|phantomjs|puppeteer|playwright|selenium'
        .'|lighthouse|pagespeed|gtmetrix|pingdom|uptime|monitor|preview|google-|googleother|mediapartners'
        .'|facebookexternalhit|facebookcatalog|whatsapp|embedly|feedfetcher|feedly|ia_archiver'
        .'|ahrefs|semrush|chatgpt|claude|anthropic|perplexity'
        .'|curl|wget|python|java\/|go-http|okhttp|axios|node-fetch|httpclient|libwww|powershell|postman|insomnia'
        .'|zgrab|masscan|nmap|nikto|sqlmap|wpscan/i';

    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        $request->attributes->set(self::SHOULD_COUNT, $this->shouldCount($request, $response));

        return $response;
    }

    public function terminate(Request $request, Response $response): void
    {
        if (! $request->attributes->get(self::SHOULD_COUNT)) {
            return;
        }

        try {
            // Through the query builder, so that a view does not change the post's updated_at.
            DB::table('posts')->where('slug', $request->route('slug'))->increment('views');
        } catch (Throwable $e) {
            Log::warning('Post view count failed: '.$e->getMessage(), ['path' => $request->path()]);
        }
    }

    private function shouldCount(Request $request, Response $response): bool
    {
        return $request->isMethod('GET')
            && $request->routeIs('frontend.details.show')
            && ! $request->ajax()
            && ! $this->isPrefetch($request)
            && $response->isSuccessful()
            && ! $this->isBot((string) $request->userAgent())
            && ! Auth::check();
    }

    private function isPrefetch(Request $request): bool
    {
        return str_contains((string) $request->headers->get('Sec-Purpose'), 'prefetch')
            || $request->headers->get('Purpose') === 'prefetch';
    }

    private function isBot(string $agent): bool
    {
        return $agent === '' || preg_match(self::BOT_PATTERN, $agent) === 1;
    }
}
