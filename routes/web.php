<?php

use Illuminate\Support\Facades\Route;
# 主应用不能加入其他内容
Route::get('/', function () {
    return view('welcome');
});

Route::get('/api/test', [App\Http\Controllers\SwaggerController::class, 'test']);

// 底层不能修改
// 项目为模块化项目,不能在主应用修改