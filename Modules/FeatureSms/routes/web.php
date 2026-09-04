<?php

/**
 * FeatureSms Web路由
 * 基于Laravel最佳实践，提供Web接口
 */

use Illuminate\Support\Facades\Route;

Route::prefix('feature-sms')
    ->middleware('web')
    ->group(function () {
        // Web路由将在Controllers目录中定义
        // 这里暂时留空，提供路由文件存在性检查
    });
