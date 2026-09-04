<?php

use Illuminate\Support\Facades\Route;
use Modules\MyToolsMain\DcatAdmin\Controllers\HomeController;

/**
 * MyToolsMain 模块后台路由
 *
 * 路由前缀: /admin
 * 命名空间: MyToolsMain
 */

// 后台首页
Route::get('/', [HomeController::class, 'index'])->name('mytoolsmain.home');
