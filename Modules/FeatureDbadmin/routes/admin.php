<?php

use Illuminate\Support\Facades\Route;
use Modules\FeatureDbadmin\DcatAdmin\Controllers\DashboardController;

/**
 * FeatureDbadmin模块后台路由
 */
Route::group([
    'prefix' => 'featuredbadmin',
], function () {

    // 仪表盘
    Route::get('dashboard', [DashboardController::class, 'index'])->name('featuredbadmin.dashboard');

    // TODO: 添加其他路由
    // Route::resource('resources', ResourceController::class)->names('featuredbadmin.resources');

});