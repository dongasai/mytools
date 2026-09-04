<?php

use Illuminate\Support\Facades\Route;
use Modules\Demo5\Api\Controllers\PostController;
use Modules\Demo5\Api\Controllers\UserController;

/**
 * Demo5模块API路由
 * 基于Laravel RESTful API规范
 */

// 文章 API 资源路由
Route::apiResource('demo5/posts', PostController::class);

// 额外的API端点
Route::get('demo5/posts/status/{status}', [PostController::class, 'byStatus'])->name('demo5.posts.byStatus');
Route::get('demo5/posts/search', [PostController::class, 'search'])->name('demo5.posts.search');

// 用户 API 资源路由
Route::apiResource('demo5/users', UserController::class);