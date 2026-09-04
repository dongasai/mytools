<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Modules\FeatureAi\DcatAdmin\Controllers\AiConversationController;
use Modules\FeatureAi\DcatAdmin\Controllers\AiImageController;
use Modules\FeatureAi\DcatAdmin\Controllers\AiProviderController;
use Modules\FeatureAi\DcatAdmin\Controllers\AiProviderModelController;
use Modules\FeatureAi\DcatAdmin\Controllers\AiServiceMappingController;
use Modules\FeatureAi\DcatAdmin\Controllers\AiTestController;
use Modules\FeatureAi\DcatAdmin\Controllers\LlmApiLogController;

/**
 * FeatureAi 模块后台路由
 *
 * AI服务管理后台路由配置，路由前缀: module_featureai
 */

Route::group(['prefix' => 'module_featureai'], function () {
    Route::resource('ai-providers', AiProviderController::class);
    Route::resource('ai-provider-models', AiProviderModelController::class);
    Route::resource('ai-service-mappings', AiServiceMappingController::class);
    Route::resource('ai-conversations', AiConversationController::class);
    Route::resource('ai-images', AiImageController::class);
    Route::resource('ai-tests', AiTestController::class);
    Route::resource('llm-api-logs', LlmApiLogController::class);
});
