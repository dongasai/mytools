<?php

/**
 * FeatureSms API Routes
 *
 * API路由提供短信发送、验证等功能
 */

use Illuminate\Support\Facades\Route;

// API路由组
Route::prefix('api')
    ->middleware('api')
    ->group(function () {
        // API路由将在Controllers目录中定义
        // 这里暂时留空，提供路由文件存在性检查
    });
