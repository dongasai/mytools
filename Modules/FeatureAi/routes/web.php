<?php

use Illuminate\Support\Facades\Route;
use Modules\FeatureAi\Http\Controllers\AIController;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::resource('ais', AIController::class)->names('ai');
});
