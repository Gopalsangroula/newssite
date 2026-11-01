<?php

namespace App\Http\Middleware;

use App\Support\PageCache;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AddSecurityHeaders
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        header_remove('X-Powered-By');
        $response->headers->add(PageCache::SECURITY_HEADERS);

        if ($request->isSecure()) {
            $response->headers->set('Strict-Transport-Security', PageCache::STRICT_TRANSPORT);
        }

        return $response;
    }
}
