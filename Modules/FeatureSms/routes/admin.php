<?php

use Illuminate\Support\Facades\Route;
use Modules\FeatureSms\DcatAdmin\Controllers\DashboardController;
use Modules\FeatureSms\DcatAdmin\Controllers\SmsConfigController;
use Modules\FeatureSms\DcatAdmin\Controllers\SmsCodeController;
use Modules\FeatureSms\DcatAdmin\Controllers\SmsGatewayController;
use Modules\FeatureSms\DcatAdmin\Controllers\SmscodeLogController;
use Modules\FeatureSms\DcatAdmin\Controllers\MyGatewayController;

/**
 * FeatureSms 模块后台路由
 * 基于Laravel最佳实践，使用资源路由
 */
Route::group([
    'prefix' => 'featuresms', // 使用模块名作为前缀
], function () {
    // 仪表盘
    Route::get('dashboard', [DashboardController::class, 'index'])->name('featuresms.dashboard');

    // 短信配置管理 - Laravel资源路由
    Route::resource('sms-configs', SmsConfigController::class)->names('featuresms.sms-configs');

    // 验证码管理 - Laravel资源路由
    Route::resource('sms-codes', SmsCodeController::class)->names('featuresms.sms-codes');

    // db驱动-短信记录 - Laravel资源路由
    Route::resource('sms-records', SmsGatewayController::class)->names('featuresms.sms-records');

    // 我的网关 - Laravel资源路由
    Route::resource('my-gateways', MyGatewayController::class)->names('featuresms.my-gateways');

    // 短信记录管理 - Laravel资源路由
    Route::resource('sms-logs', SmscodeLogController::class)->names('featuresms.sms-logs');

    // 图表路由
    Route::get('metrics/sms-send-trend', function () {
        return app(\Modules\FeatureSms\DcatAdmin\Metrics\SmsSendTrend::class)->handle(request());
    })->name('featuresms.metrics.sms-send-trend');
});
