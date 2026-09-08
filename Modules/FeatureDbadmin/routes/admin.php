<?php

use Illuminate\Support\Facades\Route;
use Modules\FeatureDbadmin\DcatAdmin\Controllers;

/**
 * FeatureDbadmin 模块后台路由
 */
Route::group([
    'prefix' => 'featuredbadmin',
], function () {

    // ===== 唯一的 HTML 入口（Vue 应用） =====
    Route::get('dashboard', [Controllers\DashboardController::class, 'index']);

    // ===== 仪表盘 JSON API =====
    Route::get('dashboard/stats', [Controllers\DashboardController::class, 'stats']);
    Route::get('dashboard/query-history', [Controllers\DashboardController::class, 'queryHistory']);

    // ===== 连接管理 JSON API =====
    Route::get('connections', [Controllers\ConnectionController::class, 'list']);
    Route::post('connections', [Controllers\ConnectionController::class, 'store']);
    Route::put('connections/{id}', [Controllers\ConnectionController::class, 'update']);
    Route::delete('connections/{id}', [Controllers\ConnectionController::class, 'destroy']);
    Route::post('connections/{id}/test', [Controllers\ConnectionController::class, 'test']);

    // ===== 表管理 JSON API =====
    Route::get('tables', [Controllers\TableController::class, 'list']);
    Route::get('tables/{name}/structure', [Controllers\TableController::class, 'structure']);
    Route::get('tables/{name}/export', [Controllers\TableController::class, 'export']);

    // ===== 数据浏览 JSON API =====
    Route::get('data/{table}', [Controllers\DataBrowserController::class, 'list']);
    Route::get('data/{table}/row/{id}', [Controllers\DataBrowserController::class, 'row']);
    Route::post('data/{table}/row', [Controllers\DataBrowserController::class, 'create']);
    Route::put('data/{table}/row/{id}', [Controllers\DataBrowserController::class, 'update']);
    Route::delete('data/{table}/row/{id}', [Controllers\DataBrowserController::class, 'delete']);
    Route::get('data/{table}/export', [Controllers\DataBrowserController::class, 'export']);

    // ===== SQL 查询工具 JSON API =====
    Route::post('query/execute', [Controllers\QueryToolController::class, 'execute']);
    Route::get('query/history', [Controllers\QueryToolController::class, 'history']);
    Route::get('query/saved', [Controllers\QueryToolController::class, 'saved']);
    Route::post('query/save', [Controllers\QueryToolController::class, 'save']);
});