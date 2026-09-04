<?php

namespace DLaravel\Providers;

use Illuminate\Support\Facades\Schedule;
use Illuminate\Support\ServiceProvider;

class DLaravelServiceProvider extends ServiceProvider
{
    /**
     * 注册服务
     */
    public function register(): void
    {
        // 注册自定义日志驱动 - 必须在register中注册
        $this->registerCustomLogDriver();
    }

    /**
     * 启动服务
     */
    public function boot(): void
    {
        // 注册中间件
        $this->registerMiddleware();

        // 注册定时任务
        $this->registerSchedules();
    }




    /**
     * 注册自定义日志驱动到 Laravel
     */
    protected function registerCustomLogDriver(): void
    {
        // 注册自定义日志驱动到 Laravel
        $this->app->make('log')->extend('size_rotating_daily', function ($config) {
            return (new \DLaravel\Logging\SizeRotatingDailyLogger())($config);
        });
    }

    /**
     * 注册中间件
     */
    protected function registerMiddleware(): void
    {
        $router = $this->app['router'];

        // 注册全局中间件
        $router->aliasMiddleware('log.request', \DLaravel\Middleware\LogRequestMiddleware::class);
    }

    /**
     * 注册DLaravel相关的定时任务
     *
     * 将原本在 routes/console.php 中的DLaravel调度配置迁移到此处
     */
    protected function registerSchedules(): void
    {
        // 在应用完全启动后注册定时任务
        $this->app->booted(function () {
            // 每天凌晨3点清理 size_rotating_daily 日志文件（保留配置的天数）
            Schedule::command('ucore:clean-size-rotating-logs')
                ->dailyAt('03:00')
                ->description('清理DLaravel轮转日志文件');
        });
    }
}
