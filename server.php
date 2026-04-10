<?php

/**
 * Router for PHP's built-in server (e.g. Railway) so static files under /public
 * are served directly — same behavior as `php artisan serve`.
 *
 * Do not use public/index.php as the router; it never returns false, so /images/*
 * and other assets would always hit Laravel and typically 404.
 */
$publicPath = __DIR__.'/public';

$uri = urldecode(
    parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?? ''
);

if ($uri !== '/' && file_exists($publicPath.$uri)) {
    return false;
}

require_once $publicPath.'/index.php';
