<?php

use Illuminate\Support\Facades\Route;
use Modules\FeatureDbadmin\DcatAdmin\Controllers;

/**
 * FeatureDbadmin 模块后台路由
 *
 * 单页面应用架构：
 * - 唯一 HTML 入口：/admin/featuredbadmin → HomeController@home (Vue SPA 入口)
 * - JSON API 路由：/admin/featuredbadmin/* → 各 Controller (API 处理)
 */
Route::group([
    'prefix' => 'featuredbadmin',
], function () {

    // ===== 唯一的 Vue SPA 入口路由 =====
    Route::get('/', [Controllers\HomeController::class, 'home'])->name('featuredbadmin.home');

    // ===== 仪表盘 JSON API =====
    Route::get('dashboard/stats', [Controllers\DashboardController::class, 'stats']);
    Route::get('dashboard/query-history', [Controllers\DashboardController::class, 'queryHistory']);

    // ===== 连接管理 JSON API =====
    Route::get('connections', [Controllers\ConnectionController::class, 'list']);
    Route::post('connections', [Controllers\ConnectionController::class, 'save']);
    Route::put('connections/{id}', [Controllers\ConnectionController::class, 'modify']);
    Route::delete('connections/{id}', [Controllers\ConnectionController::class, 'remove']);
    Route::post('connections/{id}/test', [Controllers\ConnectionController::class, 'test']);
    Route::post('connections/test-config', [Controllers\ConnectionController::class, 'testConfig']);

    // ===== 表管理 JSON API =====
    Route::get('databases', [Controllers\TableController::class, 'databases']);
    Route::get('schemas', [Controllers\TableController::class, 'schemas']);
    Route::get('tables', [Controllers\TableController::class, 'list']);
    Route::post('test-table', [Controllers\TableController::class, 'createTestTable']);
    Route::get('tables/{name}/structure', [Controllers\TableController::class, 'structure']);
    Route::get('tables/{name}/export', [Controllers\TableController::class, 'export']);

    // ===== 数据浏览 JSON API =====
    Route::get('data/{table}', [Controllers\DataBrowserController::class, 'list']);
    Route::get('data/{table}/row/{id}', [Controllers\DataBrowserController::class, 'row']);
    Route::post('data/{table}/row', [Controllers\DataBrowserController::class, 'insert']);
    Route::put('data/{table}/row/{id}', [Controllers\DataBrowserController::class, 'modify']);
    Route::delete('data/{table}/row/{id}', [Controllers\DataBrowserController::class, 'delete']);
    Route::get('data/{table}/export', [Controllers\DataBrowserController::class, 'export']);

    // ===== SQL 查询工具 JSON API =====
    Route::post('query/execute', [Controllers\QueryToolController::class, 'execute']);
    Route::get('query/history', [Controllers\QueryToolController::class, 'history']);
    Route::get('query/saved', [Controllers\QueryToolController::class, 'saved']);
    Route::post('query/save', [Controllers\QueryToolController::class, 'save']);
});
