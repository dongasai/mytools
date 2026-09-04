<?php

use Illuminate\Support\Facades\Route;
use Modules\AFile\Api\Controllers\FileApiController;
use Modules\AFile\Api\Controllers\ImageApiController;

/*
|--------------------------------------------------------------------------
| AFile API Routes
|--------------------------------------------------------------------------
|
| 文件模块对外 API 路由
| 认证方式：ApiAuth 中间件（Account 模块的自定义 Token 认证）
|
| 注意：RouteServiceProviderTrait 已自动添加 prefix('api')
| 最终路径: /api/file/...
*/

// 需要认证的接口
Route::middleware(['api.auth'])->prefix('file')->group(function () {
    // 文件上传
    Route::post('/upload/public', [FileApiController::class, 'uploadPublic'])
        ->name('files.upload.public');
    Route::post('/upload/private', [FileApiController::class, 'uploadPrivate'])
        ->name('files.upload.private');

    // 文件上传到临时储存
    Route::post('/upload/temp', [FileApiController::class, 'uploadTemp'])
        ->name('files.upload.temp');

    // 图片上传
    Route::post('/image/upload/public', [ImageApiController::class, 'uploadPublic'])
        ->name('images.upload.public');
    Route::post('/image/upload/private', [ImageApiController::class, 'uploadPrivate'])
        ->name('images.upload.private');

    // 私有图片下载（需要检查所有权）
    Route::get('/image/{id}/download/private', [ImageApiController::class, 'downloadPrivate'])
        ->name('images.download.private')
        ->where('id', '[0-9]+');
});

// 公开访问的接口
// 文件下载（RESTful 风格，与 FileLogic::getFileUrl() 生成的 URL 保持一致）
Route::prefix('v1')->group(function () {
    Route::get('/files/{id}/download', [FileApiController::class, 'download'])
        ->name('files.download')
        ->where('id', '[0-9]+');
});

// 图片公开接口
Route::prefix('file')->group(function () {
    // 图片查看
    Route::get('/image/{id}', [ImageApiController::class, 'show'])
        ->name('images.show')
        ->where('id', '[0-9]+');

    // 公共图片下载
    Route::get('/image/{id}/download', [ImageApiController::class, 'download'])
        ->name('images.download')
        ->where('id', '[0-9]+');
});
