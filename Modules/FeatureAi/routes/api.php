<?php

use Illuminate\Support\Facades\Route;
use Modules\FeatureAi\Http\Controllers\AIController;

Route::middleware(['auth:sanctum'])->prefix('v1')->group(function () {
    Route::apiResource('ais', AIController::class)->names('ai');
});
