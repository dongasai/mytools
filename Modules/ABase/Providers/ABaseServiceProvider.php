<?php

namespace Modules\ABase\Providers;

use Nwidart\Modules\Traits\PathNamespace;
use Illuminate\Routing\Router;
use Modules\ABase\Http\Middleware\RequireLog;

class ABaseServiceProvider extends \Modules\ABase\Support\ServiceProvider
{
    use PathNamespace;

    protected string $name = 'ABase';

    protected string $nameLower = 'module_abase';

    /**
     * Boot the application events.
     */
    public function boot(): void
    {
        $this->modulePath = dirname(__DIR__);
        parent::boot();

        // 注册请求日志中间件（必须在 boot 阶段，配置加载之后）
        $this->registerRequestLogMiddleware();
    }

    /**
     * Register the service provider.
     */
    public function register(): void
    {
        // 所有ServiceProvider由module.json注册，不应在此手动注册
        // ABase是核心模块，不提供路由功能
    }

    /**
     * Register commands in the format of Command::class
     */
    protected function registerCommands(): void
    {
        $this->commands([
            \Modules\ABase\Commands\GenerateAppTreeCommand::class,
            \Modules\ABase\Commands\GenerateConfigDbCommand::class,
            \Modules\ABase\Commands\GenerateModelAnnotation::class,
            // Hook管理命令
            \Modules\ABase\Commands\HookStatusCommand::class,
            \Modules\ABase\Commands\HookListCommand::class,
            \Modules\ABase\Commands\HookTestCommand::class,
            // OpenAPI文档生成命令
            \Modules\ABase\Commands\OpenapiApiGenerateCommand::class,
            // 版本信息命令
            \Modules\ABase\Commands\VersionCommand::class,
        ]);
    }

    /**
     * Register command Schedules.
     */
    protected function registerCommandSchedules(): void
    {
        // $this->app->booted(function () {
        //     $schedule = $this->app->make(Schedule::class);
        //     $schedule->command('inspire')->hourly();
        // });
    }

    /**
     * Get the services provided by the provider.
     */
    public function provides(): array
    {
        return [];
    }

    /**
     * 注册请求日志中间件
     *
     * 根据配置决定是否启用以及应用范围
     */
    protected function registerRequestLogMiddleware(): void
    {
        // 检查是否启用
        if (!config('module_abase::abase.enabled', false)) {
            return;
        }

        /** @var \Illuminate\Routing\Router $router */
        $router = $this->app->make(Router::class);
        // 默认全局应用，配置文件中可添加 'scope' => 'global' 进行控制
        $scope = config('module_abase::abase.scope', 'global');

        // Laravel 没有 global 中间件组，需要 push 到实际使用的组
        switch ($scope) {
            case 'global':
                // 全局应用：添加到 web 和 api 两个组
                $router->pushMiddlewareToGroup('web', RequireLog::class);
                $router->pushMiddlewareToGroup('api', RequireLog::class);
                break;

            case 'api':
                // 仅应用于 API 路由组
                $router->pushMiddlewareToGroup('api', RequireLog::class);
                break;

            case 'web':
                // 仅应用于 Web 路由组
                $router->pushMiddlewareToGroup('web', RequireLog::class);
                break;

            default:
                // 默认全局应用
                $router->pushMiddlewareToGroup('web', RequireLog::class);
                $router->pushMiddlewareToGroup('api', RequireLog::class);
                break;
        }
    }
}
