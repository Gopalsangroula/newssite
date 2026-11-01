<?php

namespace App\Http\Middleware;

use App\Support\PageCache;
use App\Support\SiteTasks;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * The public routes' outer middleware. It stores each finished page for App\Support\PageCache
 * to answer next time, and falls back to the stored page if a build fails.
 */
class ServePublicPage
{
    public function handle(Request $request, Closure $next): Response
    {
        $uri = $request->getRequestUri();

        // The method actually sent. A POST can claim to be a GET, and must never touch a stored page.
        $isGet = $request->getRealMethod() === 'GET';

        $response = $next($request);

        // A database outage shows readers the last good page, not an error. So does the
        // limit on built pages, because sending a stored copy costs the server almost nothing.
        $failed = $response->isServerError() || $response->getStatusCode() === Response::HTTP_TOO_MANY_REQUESTS;

        if ($isGet && $failed && ($copy = PageCache::copyOf($uri)) !== null) {
            PageCache::release($uri);

            return response($copy, 200, [
                'Content-Type' => PageCache::contentType($uri),
                'Content-Security-Policy' => PageCache::CONTENT_POLICY,
                'Cache-Control' => 'no-store',
                'X-Page-Cache' => 'stale',
            ]);
        }

        $response->headers->set('Content-Security-Policy', PageCache::CONTENT_POLICY);

        if ($isGet && $this->canStore($request, $response)) {
            $response->headers->set('Cache-Control', PageCache::CACHE_CONTROL);
            PageCache::store($uri, (string) $response->getContent(), (int) $request->server('REQUEST_TIME', time()));
        } elseif ($isGet && $response->getStatusCode() === 404) {
            PageCache::forget($uri);
        }

        PageCache::release($uri);
        SiteTasks::runAfterResponse();

        return $response;
    }

    /**
     * Only a page whose links point at the site's real address is stored, so neither
     * a forged Host header nor a plain http request can plant wrong links for everyone.
     */
    private function canStore(Request $request, Response $response): bool
    {
        return $response->getStatusCode() === 200
            && PageCache::accepts($request->getRequestUri())
            && ! $request->attributes->get('keep_page_live', false)
            && $response->headers->getCookies() === []
            && preg_match('~^(text/html|application/xml)~', (string) $response->headers->get('Content-Type')) === 1
            && url('/') === rtrim((string) config('app.url'), '/');
    }
}
