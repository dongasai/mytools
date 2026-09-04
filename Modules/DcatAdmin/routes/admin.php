<?php

use Illuminate\Support\Facades\Route;
use Modules\DcatAdmin\DcatAdmin\Controllers\DashboardController;
use Modules\DcatAdmin\DcatAdmin\Controllers\JobController;
use Modules\DcatAdmin\DcatAdmin\Controllers\JobJobrunController;
use Modules\DcatAdmin\DcatAdmin\Controllers\LogsController;
use Modules\DcatAdmin\DcatAdmin\Controllers\MenuSyncController;
use Modules\DcatAdmin\DcatAdmin\Controllers\MetricsController;
use Modules\DcatAdmin\DcatAdmin\Controllers\ReadmeController;
use Modules\DcatAdmin\DcatAdmin\Controllers\RouteViewerController;
use Modules\DcatAdmin\DcatAdmin\Controllers\SessionController;
use Modules\DcatAdmin\DcatAdmin\Controllers\SystemInfoController;
use Modules\DcatAdmin\DcatAdmin\Controllers\TraceController;

// 仪表板
Route::get('module_dcatadmin/dashboard', [DashboardController::class, 'index'])->name('module_dcatadmin.dashboard');
Route::get('module_dcatadmin/dashboard/system-status', [DashboardController::class, 'getSystemStatus'])->name('module_dcatadmin.dashboard.system-status');
Route::post('module_dcatadmin/dashboard/refresh', [DashboardController::class, 'refreshDashboard'])->name('module_dcatadmin.dashboard.refresh');

// 任务管理
Route::get('module_dcatadmin/jobs', [JobController::class, 'index'])->name('module_dcatadmin.jobs');
Route::get('module_dcatadmin/jobs/jobrun', [JobJobrunController::class, 'index'])->name('module_dcatadmin.jobs.jobrun');

// 日志查看
Route::get('module_dcatadmin/logs', [LogsController::class, 'index'])->name('module_dcatadmin.logs');
Route::get('module_dcatadmin/logs/cli', [LogsController::class, 'index2'])->name('module_dcatadmin.logs.cli');
Route::get('module_dcatadmin/logs/cron', [LogsController::class, 'indexCron'])->name('module_dcatadmin.logs.cron');

// Readme 查看
Route::get('module_dcatadmin/readme/{path}', [ReadmeController::class, 'index'])->name('module_dcatadmin.readme');

// Session 管理
Route::get('module_dcatadmin/sessions', [SessionController::class, 'index'])->name('module_dcatadmin.sessions');

// Trace 追踪
Route::get('module_dcatadmin/traces', [TraceController::class, 'index'])->name('module_dcatadmin.traces');

// 系统信息
Route::get('module_dcatadmin/system-info', [SystemInfoController::class, 'index'])->name('module_dcatadmin.system-info');

// 图表演示路由
Route::get('module_dcatadmin/metrics', [MetricsController::class, 'index'])->name('module_dcatadmin.metrics');
Route::get('module_dcatadmin/metrics2', [MetricsController::class, 'metrics2'])->name('module_dcatadmin.metrics2');

// 路由查看功能
Route::resource('module_dcatadmin/routers', RouteViewerController::class);
# Config查看
Route::resource('module_dcatadmin/config', \Modules\DcatAdmin\DcatAdmin\Controllers\DevController::class);

# 菜单同步管理
Route::get('module_dcatadmin/menu-sync', [MenuSyncController::class, 'index'])->name('dcat.admin.menu-sync.index');
Route::match(['get', 'post'], 'module_dcatadmin/menu-sync/sync', [MenuSyncController::class, 'sync'])->name('dcat.admin.menu-sync.sync');
Route::get('module_dcatadmin/menu-sync/result', [MenuSyncController::class, 'showResult'])->name('dcat.admin.menu-sync.result');

// Vue 仪表盘路由
use Modules\DcatAdmin\DcatAdmin\Controllers\VueDashboardController;
use Modules\DcatAdmin\DcatAdmin\Controllers\DashboardApiController;

// Vue 仪表盘
Route::get('module_dcatadmin/vue-dashboard', [VueDashboardController::class, 'index'])
    ->name('module_dcatadmin.vue-dashboard');

// Element Plus 组件演示
Route::get('module_dcatadmin/elements-demo', [VueDashboardController::class, 'elementsDemo'])
    ->name('module_dcatadmin.elements-demo');

// 基础组件演示
Route::get('module_dcatadmin/basic-demo', [VueDashboardController::class, 'basicDemo'])
    ->name('module_dcatadmin.basic-demo');

// 表单组件演示
Route::get('module_dcatadmin/form-demo', [VueDashboardController::class, 'formDemo'])
    ->name('module_dcatadmin.form-demo');

// 数据展示演示
Route::get('module_dcatadmin/data-demo', [VueDashboardController::class, 'dataDemo'])
    ->name('module_dcatadmin.data-demo');

// Vue 仪表盘数据接口
Route::get('module_dcatadmin/vue-dashboard/stats', [DashboardApiController::class, 'getStats']);
Route::get('module_dcatadmin/vue-dashboard/chart', [DashboardApiController::class, 'getChartData']);
