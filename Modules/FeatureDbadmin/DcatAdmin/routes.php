<?php

use Illuminate\Support\Facades\Route;
use Modules\FeatureDbadmin\DcatAdmin\Controllers;

Route::group([
    'prefix' => 'featuredbadmin',
    'middleware' => ['admin', 'admin.permission:check']
], function () {

    // ===== 仪表盘 =====
    Route::get('dashboard', [Controllers\DashboardController::class, 'index']); // HTML 页面
    Route::get('dashboard/stats', [Controllers\DashboardController::class, 'stats']); // JSON 接口

    // ===== 连接管理 =====
    Route::get('connections', [Controllers\ConnectionController::class, 'index']); // HTML 页面
    Route::get('connections/list', [Controllers\ConnectionController::class, 'list']); // JSON 接口
    Route::post('connections', [Controllers\ConnectionController::class, 'store']); // JSON 接口
    Route::put('connections/{id}', [Controllers\ConnectionController::class, 'update']); // JSON 接口
    Route::delete('connections/{id}', [Controllers\ConnectionController::class, 'destroy']); // JSON 接口
    Route::post('connections/{id}/test', [Controllers\ConnectionController::class, 'test']); // JSON 接口

    // ===== 表管理 =====
    Route::get('tables', [Controllers\TableController::class, 'index']); // HTML 页面（Vue）
    Route::get('tables/list', [Controllers\TableController::class, 'list']); // JSON 接口
    Route::get('tables/{name}/structure', [Controllers\TableController::class, 'structure']); // JSON 接口
    Route::get('tables/{name}/export', [Controllers\TableController::class, 'export']); // JSON 接口

    // ===== 数据浏览 =====
    Route::get('data/{table}', [Controllers\DataBrowserController::class, 'index']); // HTML 页面（Vue）
    Route::get('data/{table}/list', [Controllers\DataBrowserController::class, 'list']); // JSON 接口
    Route::get('data/{table}/row/{id}', [Controllers\DataBrowserController::class, 'row']); // JSON 接口
    Route::post('data/{table}/row', [Controllers\DataBrowserController::class, 'create']); // JSON 接口
    Route::put('data/{table}/row/{id}', [Controllers\DataBrowserController::class, 'update']); // JSON 接口
    Route::delete('data/{table}/row/{id}', [Controllers\DataBrowserController::class, 'delete']); // JSON 接口
    Route::get('data/{table}/export', [Controllers\DataBrowserController::class, 'export']); // JSON 接口

    // ===== SQL 查询工具 =====
    Route::get('query', [Controllers\QueryToolController::class, 'index']); // HTML 页面（Vue）
    Route::post('query/execute', [Controllers\QueryToolController::class, 'execute']); // JSON 接口
    Route::get('query/history', [Controllers\QueryToolController::class, 'history']); // JSON 接口
    Route::get('query/saved', [Controllers\QueryToolController::class, 'saved']); // JSON 接口
    Route::post('query/save', [Controllers\QueryToolController::class, 'save']); // JSON 接口
});
