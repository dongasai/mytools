<?php

use Dcat\Admin\Admin;
use Illuminate\Routing\Router;
use Illuminate\Support\Facades\Route;

Admin::routes();

Route::group([
    'prefix' => config('admin.route.prefix'),
    'middleware' => config('admin.route.middleware'),
], function (Router $router) {

//    $router->get('/', [App\Admin\Controllers\HomeController::class, 'index'])->name('admin.home');

    // 所有模块路由现在通过模块系统自动加载
    // 模块路由不允许出现在这里
    // 这是主应用,不能有模块路由
    // 模块的路由应该由模块管理

    // 后台视图管理路由
    $router->resource('admin_view', App\Admin\Controllers\AdminViewController::class)->names('admin_view');
    $router->get('admin_view/add', [App\Admin\Controllers\AdminViewController::class, 'getadd'])->name('admin_view_add');

});
