<?php

namespace App\Http\Middleware;

use App\Models\Post;
use App\Models\Visit;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

/**
 * Records public HTML page views in the visits table.
 *
 * Whether a request counts is decided in handle(), while the session is still
 * open. The row is written in terminate(), after the response has been sent, so
 * tracking cannot slow a page down, and any failure is logged, never shown.
 */
class TrackVisit
{
    private const SHOULD_RECORD = 'track_visit';

    /**
     * Paths that are not public pages, matched with Request::is(). The register,
     * forgot-password and reset-password pages all live under admin/.
     */
    private const EXCLUDED_PATHS = [
        'admin',
        'admin/*',
        'dashboard',
        'settings',
        'settings/*',
        'livewire/*',
        'login',
        'logout',
        'register',
        'verify-email',
        'verify-email/*',
        'confirm-password',
        '_boost/*',
        'storage/*',
        'up',
    ];

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

    /**
     * Browser names and how to spot them. Order matters: Edge and Opera also
     * identify as Chrome, and Chrome also identifies as Safari.
     */
    private const BROWSERS = [
        'Edge' => '/\bEdg(e|A|iOS)?\//',
        'Opera' => '/\b(OPR|OPiOS|Opera)\b/',
        'Samsung Internet' => '/\bSamsungBrowser\//',
        'UC Browser' => '/\bUCBrowser\//',
        'Firefox' => '/\b(Firefox|FxiOS)\//',
        'Chrome' => '/\b(Chrome|CriOS|Chromium)\//',
        'Safari' => '/\bSafari\//',
        'Internet Explorer' => '/\b(MSIE |Trident\/)/',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        try {
            $request->attributes->set(self::SHOULD_RECORD, $this->shouldRecord($request, $response));
        } catch (Throwable $e) {
            $this->logFailure($request, $e);
        }

        return $response;
    }

    public function terminate(Request $request, Response $response): void
    {
        if (! $request->attributes->get(self::SHOULD_RECORD)) {
            return;
        }

        try {
            $this->record($request);
        } catch (Throwable $e) {
            $this->logFailure($request, $e);
        }
    }

    private function shouldRecord(Request $request, Response $response): bool
    {
        return $request->isMethod('GET')
            && ! $request->ajax()
            && ! $this->isPrefetch($request)
            && $response->isSuccessful()
            && str_contains((string) $response->headers->get('Content-Type'), 'text/html')
            && ! $request->is(...self::EXCLUDED_PATHS)
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

    private function record(Request $request): void
    {
        $agent = mb_scrub((string) $request->userAgent(), 'UTF-8');
        $ip = $request->ip();

        Visit::create([
            'post_id' => $this->postId($request),
            'path' => mb_substr('/'.ltrim(mb_scrub($request->decodedPath(), 'UTF-8'), '/'), 0, 1024),
            'ip_address' => $ip,
            'user_agent' => mb_substr($agent, 0, 512),
            'device' => $this->device($agent),
            'browser' => $this->browser($agent),
            'referrer' => $this->referrer($request),
            // A keyed hash of IP and browser, so that uniques can be counted without a cookie.
            'visitor_key' => substr(hash_hmac('sha256', $ip.'|'.$agent, (string) config('app.key')), 0, 40),
        ]);
    }

    private function postId(Request $request): ?int
    {
        if (! $request->routeIs('frontend.details.show')) {
            return null;
        }

        return Post::where('slug', $request->route('slug'))->value('id');
    }

    private function device(string $agent): string
    {
        if (preg_match('/ipad|tablet|kindle|silk|playbook/i', $agent)) {
            return 'tablet';
        }

        if (stripos($agent, 'android') !== false && stripos($agent, 'mobile') === false) {
            return 'tablet';
        }

        if (preg_match('/mobi|iphone|ipod|android|blackberry|opera mini|windows phone/i', $agent)) {
            return 'mobile';
        }

        return 'desktop';
    }

    private function browser(string $agent): string
    {
        foreach (self::BROWSERS as $name => $pattern) {
            if (preg_match($pattern, $agent)) {
                return $name;
            }
        }

        return 'Other';
    }

    /**
     * The referring page without its query string, which can hold personal data.
     */
    private function referrer(Request $request): ?string
    {
        $referrer = (string) $request->headers->get('referer');

        if ($referrer === '') {
            return null;
        }

        return mb_substr(mb_scrub(preg_replace('/[?#].*$/s', '', $referrer), 'UTF-8'), 0, 1024);
    }

    private function logFailure(Request $request, Throwable $e): void
    {
        Log::warning('Visit tracking failed: '.$e->getMessage(), ['path' => $request->path()]);
    }
}
