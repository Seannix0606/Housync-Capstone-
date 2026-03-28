<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // 1. Register the Role Middleware Alias (Fixes "Target class [role] does not exist")
        $middleware->alias([
            'role' => \App\Http\Middleware\RoleMiddleware::class,
            'esp32.auth' => \App\Http\Middleware\VerifyEsp32ApiKey::class,
        ]);

        // 2. Trust Proxies
        // Set TRUSTED_PROXIES in .env to the CIDR(s) of your load balancer(s).
        //   Railway example : TRUSTED_PROXIES=100.64.0.0/10
        //   Local Nginx     : TRUSTED_PROXIES=127.0.0.1
        //   Multiple CIDRs  : TRUSTED_PROXIES=10.0.0.0/8,172.16.0.0/12
        //
        // WARNING: Do NOT use '*' in production. Trusting all proxies lets any
        // client forge X-Forwarded-For, bypassing IP-based rate limiting and
        // producing incorrect access logs. See OWASP: Unvalidated Redirects.
        $rawProxies = env('TRUSTED_PROXIES');
        $at = match (true) {
            $rawProxies === null || $rawProxies === '' => null,
            str_contains($rawProxies, ',')            => array_map('trim', explode(',', $rawProxies)),
            default                                   => $rawProxies,
        };
        $middleware->trustProxies(
            at: $at,
            headers: \Illuminate\Http\Request::HEADER_X_FORWARDED_FOR
                   | \Illuminate\Http\Request::HEADER_X_FORWARDED_PROTO
                   | \Illuminate\Http\Request::HEADER_X_FORWARDED_PORT
                   | \Illuminate\Http\Request::HEADER_X_FORWARDED_HOST,
        );
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
