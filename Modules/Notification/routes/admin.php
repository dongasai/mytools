<?php

declare(strict_types=1);

use Dcat\Admin\Admin;
use Illuminate\Support\Facades\Route;
use Modules\Notification\DcatAdmin\Controllers\NotificationLogController;
use Modules\Notification\DcatAdmin\Controllers\NotificationTemplateController;

/**
 * DcatAdmin 路由配置.
 *
 * 定义通知模块的后台管理路由，包括通知日志和通知模板的资源路由。
 * 所有路由均使用 DcatAdmin 的路由配置和中间件。
 *
 * @package Modules\Notification\routes
 */

// Admin::routes() 只应在 app/Admin/routes.php 中调用一次，模块路由文件中不需要

// 定义通知模块后台路由组（仅设置模块前缀）
Route::group([
    'prefix' => 'module_notification',
], function () {
    // 通知日志路由（只读）
    Route::resource('notification-logs', NotificationLogController::class)
        ->only(['index', 'show'])
        ->names('module_notification.notification-logs');

    // 通知模板路由（完整 CRUD）
    Route::resource('notification-templates', NotificationTemplateController::class)
        ->names('module_notification.notification-templates');
});