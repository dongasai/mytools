<?php

use Illuminate\Support\Facades\Route;
use Modules\AClean\DcatAdmin\Controllers\CleanupConfigController;
use Modules\AClean\DcatAdmin\Controllers\CleanupLogController;
use Modules\AClean\DcatAdmin\Controllers\CleanupPlanController;
use Modules\AClean\DcatAdmin\Controllers\CleanupStatsController;
use Modules\AClean\DcatAdmin\Controllers\CleanupTaskController;

/**
 * AClean 模块后台路由
 * 基于Laravel最佳实践，使用资源路由
 */
Route::group([
    'prefix' => 'module_aclean',
    'middleware' => ['admin'],
], function () {

    // 清理配置管理 - Laravel资源路由
    Route::resource('configs', CleanupConfigController::class)->names('aclean-admin.configs');

    // 清理计划管理 - Laravel资源路由
    Route::resource('plans', CleanupPlanController::class)->names('aclean-admin.plans');

    // 清理任务管理 - Laravel资源路由
    Route::resource('tasks', CleanupTaskController::class)->names('aclean-admin.tasks');

    // 清理日志 - Laravel资源路由
    Route::resource('logs', CleanupLogController::class)->names('aclean-admin.logs');

    // 统计信息
    Route::get('stats', [CleanupStatsController::class, 'dashboard'])->name('aclean-admin.stats.dashboard');

    // API路由组
    Route::group(['prefix' => 'api'], function () {

        // 配置相关API
        Route::post('configs/scan-tables', [CleanupConfigController::class, 'scanTables'])->name('aclean-admin.configs.scan-tables');
        Route::post('configs/batch-enable', [CleanupConfigController::class, 'batchEnable'])->name('aclean-admin.configs.batch-enable');
        Route::post('configs/batch-disable', [CleanupConfigController::class, 'batchDisable'])->name('aclean-admin.configs.batch-disable');
        Route::post('configs/{id}/test-cleanup', [CleanupConfigController::class, 'testCleanup'])->name('aclean-admin.configs.test-cleanup');

        // 计划相关API
        Route::post('plans/create-from-template', [CleanupPlanController::class, 'createFromTemplate'])->name('aclean-admin.plans.create-from-template');
        Route::get('plans/{id}/contents', [CleanupPlanController::class, 'viewContents'])->name('aclean-admin.plans.view-contents');
        Route::post('plans/{id}/create-task', [CleanupPlanController::class, 'createTask'])->name('aclean-admin.plans.create-task');
        Route::get('plans/{id}/preview', [CleanupPlanController::class, 'preview'])->name('aclean-admin.plans.preview');
        Route::post('plans/batch-enable', [CleanupPlanController::class, 'batchEnable'])->name('aclean-admin.plans.batch-enable');
        Route::post('plans/batch-disable', [CleanupPlanController::class, 'batchDisable'])->name('aclean-admin.plans.batch-disable');

        // 任务相关API
        Route::post('tasks/create', [CleanupTaskController::class, 'createTask'])->name('aclean-admin.tasks.create');
        Route::post('tasks/{id}/start', [CleanupTaskController::class, 'startTask'])->name('aclean-admin.tasks.start');
        Route::post('tasks/{id}/pause', [CleanupTaskController::class, 'pauseTask'])->name('aclean-admin.tasks.pause');
        Route::post('tasks/{id}/resume', [CleanupTaskController::class, 'resumeTask'])->name('aclean-admin.tasks.resume');
        Route::post('tasks/{id}/cancel', [CleanupTaskController::class, 'cancelTask'])->name('aclean-admin.tasks.cancel');
        Route::get('tasks/{id}/logs', [CleanupTaskController::class, 'viewLogs'])->name('aclean-admin.tasks.view-logs');
        Route::post('tasks/batch-cancel', [CleanupTaskController::class, 'batchCancel'])->name('aclean-admin.tasks.batch-cancel');

        // 日志相关API
        Route::get('logs/export', [CleanupLogController::class, 'export'])->name('aclean-admin.logs.export');
        Route::post('logs/clean-old', [CleanupLogController::class, 'cleanOld'])->name('aclean-admin.logs.clean-old');

    });
});