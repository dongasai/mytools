<?php

use Illuminate\Support\Facades\Route;
use Modules\AFile\DcatAdmin\Controllers\FileController;
use Modules\AFile\DcatAdmin\Controllers\ImageController;
use Modules\AFile\DcatAdmin\Controllers\StorageConfigController;
use Modules\AFile\DcatAdmin\Controllers\FileTemplateController;

/**
 * AFile模块后台路由
 * 文件管理后台
 */

Route::group(['prefix' => 'module_afile'], function () {
    // 文件管理
    Route::resource('files', FileController::class)->names('afile.files');

    // 图片管理
    Route::resource('images', ImageController::class)->names('afile.images');

    // 存储配置管理
    Route::resource('storage-configs', StorageConfigController::class)->names('afile.storage-configs');

    // 文件模板管理
    Route::resource('file-templates', FileTemplateController::class)->names('afile.templates');
});