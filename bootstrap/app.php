<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Middleware\TrustProxies;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // PHP-FPM is never reachable from outside Docker's internal network (only the
        // host nginx reverse proxy can reach it), and thanks to Docker's NAT the
        // container sees the proxy's connection from an internal bridge IP rather
        // than 127.0.0.1, so we trust all connections for X-Forwarded-* headers.
        $middleware->trustProxies(at: '*');

        $middleware->validateCsrfTokens(except: [
            'telegram/webhook/*',
            'payments/liqpay/callback',
        ]);

        $middleware->alias([
            'admin' => \App\Http\Middleware\EnsureUserIsAdmin::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*'),
        );
    })->create();
