<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Modules\Debug\Http\Controllers\DebugApiProtoController;
use Modules\Debug\Http\Controllers\DebugFrameController;
use Modules\Debug\Http\Controllers\DebugQuickLoginController;
use Modules\Debug\Http\Controllers\DebugRouteController;

Route::prefix('debug')->group(function () {
    // 主外壳页面（包含导航 + iframe）
    Route::get('/', [DebugFrameController::class, 'frame'])->name('debug');

    // 内容页面（仅用于 iframe 嵌入，直接访问会跳转到主外壳）
    Route::get('/index', [DebugRouteController::class, 'index'])->name('debug.index');
    Route::get('/routes', [DebugRouteController::class, 'routes'])->name('debug.routes');
    Route::get('/server', [DebugRouteController::class, 'server'])->name('debug.server');
    Route::get('/apiproto', [DebugApiProtoController::class, 'index'])->name('debug.apiproto');

    // 快速登录API（排除CSRF验证）
    Route::post('/api/quick-login', [DebugQuickLoginController::class, 'login'])
        ->withoutMiddleware([\Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class])
        ->name('debug.quick-login');
});