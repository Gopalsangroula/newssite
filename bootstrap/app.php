<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Middleware\RestrictToAdminOrEditor;
use App\Http\Middleware\RedirectIfAuthenticated;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        then: fn () => Route::middleware('public')->group(base_path('routes/public.php')),
    )
    ->withMiddleware(function (Middleware $middleware) {
        // The live site sits behind Cloudflare, so $request->ip() must read the visitor's
        // address from its headers. Ranges come from cloudflare.com/ips.
        $middleware->trustProxies(
            at: [
                '173.245.48.0/20', '103.21.244.0/22', '103.22.200.0/22', '103.31.4.0/22',
                '141.101.64.0/18', '108.162.192.0/18', '190.93.240.0/20', '188.114.96.0/20',
                '197.234.240.0/22', '198.41.128.0/17', '162.158.0.0/15', '104.16.0.0/13',
                '104.24.0.0/14', '172.64.0.0/13', '131.0.72.0/22',
                '2400:cb00::/32', '2606:4700::/32', '2803:f800::/32', '2405:b500::/32',
                '2405:8100::/32', '2a06:98c0::/29', '2c0f:f248::/32',
            ],
            headers: Request::HEADER_X_FORWARDED_FOR | Request::HEADER_X_FORWARDED_PROTO,
        );

        // A forged Host header would otherwise end up in password reset links and page addresses.
        $middleware->trustHosts(at: ['^(.+\.)?libang\.org\.np$', '^127\.0\.0\.1$', '^localhost$']);

        // Global Middleware
        $middleware->append(\App\Http\Middleware\AddSecurityHeaders::class);

        // Middleware Aliases
        $middleware->alias([
            'auth' => \Illuminate\Auth\Middleware\Authenticate::class,
            'auth.basic' => \Illuminate\Auth\Middleware\AuthenticateWithBasicAuth::class,
            'cache.headers' => \Illuminate\Http\Middleware\SetCacheHeaders::class,
            'can' => \Illuminate\Auth\Middleware\Authorize::class,
            'guest' => RedirectIfAuthenticated::class,
            'password.confirm' => \Illuminate\Auth\Middleware\RequirePassword::class,
            'precognitive' => \Illuminate\Foundation\Http\Middleware\HandlePrecognitiveRequests::class,
            'signed' => \Illuminate\Routing\Middleware\ValidateSignature::class,
            'throttle' => \Illuminate\Routing\Middleware\ThrottleRequests::class,
            'verified' => \Illuminate\Auth\Middleware\EnsureEmailIsVerified::class,
            // Spatie Permission Middleware (corrected namespaces)
            'role' => \Spatie\Permission\Middleware\RoleMiddleware::class,
            'permission' => \Spatie\Permission\Middleware\PermissionMiddleware::class,
            'role_or_permission' => \Spatie\Permission\Middleware\RoleOrPermissionMiddleware::class,
            // Custom Middleware
            'admin.or.editor' => RestrictToAdminOrEditor::class,
        ]);

        // Middleware Groups
        $middleware->group('admin', [
            'auth',
            'verified',
            'role:admin',
        ]);

        // The public pages: no session, no cookies, and each finished page is stored.
        // Stored pages are answered before any of this, so the limit only counts real work.
        $middleware->group('public', [
            \App\Http\Middleware\ServePublicPage::class,
            'throttle:public-pages',
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();