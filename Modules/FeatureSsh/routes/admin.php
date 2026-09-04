<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Modules\FeatureSsh\DcatAdmin\Controllers\AuthenticationController;
use Modules\FeatureSsh\DcatAdmin\Controllers\CommandLogController;
use Modules\FeatureSsh\DcatAdmin\Controllers\DashboardController;
use Modules\FeatureSsh\DcatAdmin\Controllers\KeyPairController;
use Modules\FeatureSsh\DcatAdmin\Controllers\ServerController;

/**
 * FeatureSsh模块后台路由
 *
 * @author AI开发团队
 * @date 2026-09-02
 */

// 仪表盘
Route::get('featuressh/dashboard', [DashboardController::class, 'index'])
    ->name('featuressh.dashboard');

// 服务器管理
Route::resource('featuressh/servers', ServerController::class)
    ->names('featuressh.servers');
Route::get('featuressh/servers/{id}/test', [ServerController::class, 'test'])
    ->name('featuressh.servers.test');

// 服务器分组管理
Route::resource('featuressh/server-groups', ServerGroupController::class)
    ->names('featuressh.server-groups');

// 认证管理
Route::resource('featuressh/authentications', AuthenticationController::class)
    ->names('featuressh.authentications');
Route::get('featuressh/authentications/{id}/test', [AuthenticationController::class, 'test'])
    ->name('featuressh.authentications.test');

// 密钥对管理
Route::resource('featuressh/key-pairs', KeyPairController::class)
    ->names('featuressh.key-pairs');
Route::get('featuressh/key-pairs/import', [KeyPairController::class, 'import'])
    ->name('featuressh.key-pairs.import');
Route::post('featuressh/key-pairs/import', [KeyPairController::class, 'importSave'])
    ->name('featuressh.key-pairs.import-save');
Route::get('featuressh/key-pairs/{id}/public-key', [KeyPairController::class, 'publicKey'])
    ->name('featuressh.key-pairs.public-key');

// 命令执行日志
Route::resource('featuressh/command-logs', CommandLogController::class)
    ->names('featuressh.command-logs');
