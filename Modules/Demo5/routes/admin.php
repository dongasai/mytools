<?php

use Illuminate\Support\Facades\Route;
use Modules\Demo5\DcatAdmin\Controllers\CommentController;
use Modules\Demo5\DcatAdmin\Controllers\DashboardController;
use Modules\Demo5\DcatAdmin\Controllers\PostController;
use Modules\Demo5\DcatAdmin\Controllers\VueDashboardController;
use Modules\Demo5\DcatAdmin\Controllers\VuePostController;
use Modules\DcatAdmin\DcatAdmin\Controllers\VueIframeController;

/**
 * Demo5模块后台路由
 * 基于Laravel最佳实践，使用资源路由
 */
Route::group([
    'prefix' => 'module_demo5', // 框架自动处理前缀
], function () {

    // 仪表盘
    Route::get('dashboard', [DashboardController::class, 'index'])->name('demo5.dashboard');

    // Vue 仪表盘
    Route::get('vue-dashboard', [VueDashboardController::class, 'index'])
        ->name('module_demo5.vue-dashboard');
    Route::get('vue-dashboard/stats', [VueDashboardController::class, 'getStats']);
    Route::get('vue-dashboard/chart', [VueDashboardController::class, 'getChartData']);
    Route::get('vue-dashboard/posts', [VueDashboardController::class, 'getRecentPosts']);

    // Vue 文章管理
    Route::get('vue-posts', [VuePostController::class, 'index'])
        ->name('module_demo5.vue-posts');
    Route::get('vue-posts/list', [VuePostController::class, 'list']);
    Route::post('vue-posts', [VuePostController::class, 'store']);
    Route::put('vue-posts/{id}', [VuePostController::class, 'update']);
    Route::delete('vue-posts/{id}', [VuePostController::class, 'destroy']);

    // 文章管理 - Laravel资源路由
    Route::resource('posts', PostController::class)->names('demo5.posts');

    // 评论管理 - Laravel资源路由
    Route::resource('comments', CommentController::class)->names('demo5.comments');

    // 文章评论关联管理（子资源路由）
    Route::get('posts/{post}/comments', [PostController::class, 'comments'])->name('demo5.posts.comments');
});