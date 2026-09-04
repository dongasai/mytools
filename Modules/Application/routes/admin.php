<?php

use Illuminate\Support\Facades\Route;
use Modules\Application\DcatAdmin\Controllers\ActionLogController;
use Modules\Application\DcatAdmin\Controllers\AdminGridViewController;
use Modules\Application\DcatAdmin\Controllers\ConfigAdminController;
use Modules\Application\DcatAdmin\Controllers\ConfigController;
use Modules\Application\DcatAdmin\Controllers\DemoController;
use Modules\Application\DcatAdmin\Controllers\FailedJobController;
use Modules\Application\DcatAdmin\Controllers\DictController;
use Modules\Application\DcatAdmin\Controllers\FeatureBlacklistController;
use Modules\Application\DcatAdmin\Controllers\FeatureController;
use Modules\Application\DcatAdmin\Controllers\FeatureWhitelistController;
use Modules\Application\DcatAdmin\Controllers\JobBatchController;
use Modules\Application\DcatAdmin\Controllers\JobController;
use Modules\Application\DcatAdmin\Controllers\JobRunController;
use Modules\Application\DcatAdmin\Controllers\HomeController;
use Modules\Application\DcatAdmin\Controllers\SystemLogController;
use Modules\Application\DcatAdmin\Controllers\ToolController;
use Modules\Application\DcatAdmin\Controllers\RequestLogController;

/**
 * Application模块后台路由
 * Admin后台,超管后台
 */
Route::group([
    'prefix' => 'module_application', // 这里不需要`/admin`的前缀, 框架自动处理
    //
], function () {

    // 系统全局状态首页
    Route::get('home', [HomeController::class, 'index'])->name('application.home');

    // 系统配置管理
    Route::get('config', [ConfigController::class, 'index'])->name('application.config');
    Route::get('config/create', [ConfigController::class, 'create'])->name('application.config.create');
    Route::post('config', [ConfigController::class, 'store'])->name('application.config.store');
    Route::get('config/{id}/edit', [ConfigController::class, 'edit'])->name('application.config.edit');
    Route::put('config/{id}', [ConfigController::class, 'update'])->name('application.config.update');
    Route::delete('config/{id}', [ConfigController::class, 'destroy'])->name('application.config.destroy');

  
    // 高级配置
    Route::resource('config-admins', ConfigAdminController::class)->names('application.config_admins');

    // 系统日志
    Route::get('system-log', [SystemLogController::class, 'index'])->name('application.system_log');
    Route::get('system-log/{id}', [SystemLogController::class, 'show'])->name('application.system_log.show');
    Route::delete('system-log/{id}', [SystemLogController::class, 'destroy'])->name('application.system_log.destroy');

    // 操作日志
    Route::get('action-log', [ActionLogController::class, 'index'])->name('application.action_log');
    Route::get('action-log/{id}', [ActionLogController::class, 'show'])->name('application.action_log.show');

    // 请求日志
    Route::resource('request-logs', RequestLogController::class)->names('application.request-logs');

    // 队列任务
    Route::get('jobs', [JobController::class, 'index'])->name('application.jobs');

    // 失败任务
    Route::get('failed-jobs', [FailedJobController::class, 'index'])->name('application.failed_jobs');

    // 任务批次
    Route::get('job-batches', [JobBatchController::class, 'index'])->name('application.job_batches');

    // 任务运行
    Route::get('job-runs', [JobRunController::class, 'index'])->name('application.job_runs');

    // 系统工具
    Route::get('tools', [ToolController::class, 'index'])->name('application.tools');

    // Debug调试
    Route::get('demo/debug', [DemoController::class, 'debug'])->name('application.debug');

    // 管理视图
    Route::get('admin-views', [AdminGridViewController::class, 'index'])->name('application.admin_views');

    // 功能开关管理
    Route::get('features', [FeatureController::class, 'index'])->name('application.features');
    Route::get('features/create', [FeatureController::class, 'create'])->name('application.features.create');
    Route::post('features', [FeatureController::class, 'store'])->name('application.features.store');
    Route::get('features/{id}/edit', [FeatureController::class, 'edit'])->name('application.features.edit');
    Route::put('features/{id}', [FeatureController::class, 'update'])->name('application.features.update');
    Route::delete('features/{id}', [FeatureController::class, 'destroy'])->name('application.features.destroy');
    Route::get('features/{id}', [FeatureController::class, 'show'])->name('application.features.show');

    // 功能白名单管理
    Route::get('features/{feature_id}/whitelist', [FeatureWhitelistController::class, 'index'])->name('application.features.whitelist');
    Route::post('features/{feature_id}/whitelist', [FeatureWhitelistController::class, 'store'])->name('application.features.whitelist.store');
    Route::get('features/{feature_id}/whitelist/{id}/edit', [FeatureWhitelistController::class, 'edit'])->name('application.features.whitelist.edit');
    Route::put('features/{feature_id}/whitelist/{id}', [FeatureWhitelistController::class, 'update'])->name('application.features.whitelist.update');
    Route::delete('features/{feature_id}/whitelist/{id}', [FeatureWhitelistController::class, 'destroy'])->name('application.features.whitelist.destroy');

    // 功能黑名单管理
    Route::get('features/{feature_id}/blacklist', [FeatureBlacklistController::class, 'index'])->name('application.features.blacklist');
    Route::post('features/{feature_id}/blacklist', [FeatureBlacklistController::class, 'store'])->name('application.features.blacklist.store');
    Route::get('features/{feature_id}/blacklist/{id}/edit', [FeatureBlacklistController::class, 'edit'])->name('application.features.blacklist.edit');
    Route::put('features/{feature_id}/blacklist/{id}', [FeatureBlacklistController::class, 'update'])->name('application.features.blacklist.update');
    Route::delete('features/{feature_id}/blacklist/{id}', [FeatureBlacklistController::class, 'destroy'])->name('application.features.blacklist.destroy');

    // 字典管理
    Route::get('dict', [DictController::class, 'index'])->name('application.dict');
    Route::get('dict/create', [DictController::class, 'create'])->name('application.dict.create');
    Route::post('dict', [DictController::class, 'store'])->name('application.dict.store');
    Route::get('dict/{id}/edit', [DictController::class, 'edit'])->name('application.dict.edit');
    Route::put('dict/{id}', [DictController::class, 'update'])->name('application.dict.update');
    Route::delete('dict/{id}', [DictController::class, 'destroy'])->name('application.dict.destroy');
    Route::get('dict/{id}', [DictController::class, 'show'])->name('application.dict.show');
});