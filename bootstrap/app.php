<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

// 修正 HTTPS 误判：宝塔环境 Nginx pathinfo.conf 的 if 语句会导致 $https=1
// 根据 REQUEST_SCHEME 自动判断实际协议
$envHttps = env('IS_HTTPS', null);
if ($envHttps !== null) {
    // 环境变量明确指定
    $isHttps = in_array(strtolower($envHttps), ['true', '1', 'on'], true);
} else {
    // 根据 REQUEST_SCHEME 自动判断
    $requestScheme = $_SERVER['REQUEST_SCHEME'] ?? 'http';
    $isHttps = strtolower($requestScheme) === 'https';
}

// 如果判断为 HTTP，但 $_SERVER['HTTPS'] 被误设为非空，清空它
if (!$isHttps && !empty($_SERVER['HTTPS'])) {
    $_SERVER['HTTPS'] = '';
}

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // HandleCors 必须作为前置中间件，确保所有响应（包括错误）都有 CORS 头
        $middleware->prepend(\Illuminate\Http\Middleware\HandleCors::class);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })
    ->withProviders([

    ])
    ->create();
