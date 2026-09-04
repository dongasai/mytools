<?php

declare(strict_types=1);

namespace Modules\Debug\Providers;

use Illuminate\Support\ServiceProvider;

/**
 * Debug模块服务提供者
 *
 * @package Modules\Debug\Providers
 */
class DebugServiceProvider extends ServiceProvider
{
    /**
     * 注册服务
     */
    public function register(): void
    {
        // 注册配置文件
        $this->mergeConfigFrom(
            __DIR__.'/../config/config.php',
            'debug'
        );

        // 注册命令
        $this->commands([
            \Modules\Debug\Commands\ReplayRequestCommand::class,
            \Modules\Debug\Commands\RequestLogCommand::class,
        ]);
    }

    /**
     * 启动服务
     */
    public function boot(): void
    {
        // 加载路由文件
        $this->loadRoutesFrom(__DIR__.'/../routes/web.php');
        $this->loadRoutesFrom(__DIR__.'/../routes/console.php');

        // 加载视图文件
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'debug');

        // 加载翻译文件
        $this->loadTranslationsFrom(__DIR__.'/../resources/lang', 'debug');

        // 发布配置文件
        if ($this->app->runningInConsole()) {
            // 发布配置
            $this->publishes([
                __DIR__.'/../config/config.php' => config_path('debug.php'),
            ], 'debug-config');

            // 发布静态资源到 public/modules/debug/
            $this->publishes([
                __DIR__.'/../resources/assets' => public_path('modules/debug'),
            ], 'debug-assets');
        }
    }
}