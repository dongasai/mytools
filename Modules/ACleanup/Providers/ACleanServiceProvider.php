<?php

namespace Modules\AClean\Providers;

use Modules\ABase\Support\ServiceProvider as ABaseServiceProvider;
use Modules\AClean\Commands\CleanupDataCommand;
use Modules\AClean\Commands\InsertCleanupAdminMenuCommand;
use Modules\AClean\Commands\ScanModelsCommand;
use Modules\AClean\Commands\TestModelCleanupCommand;
use Modules\AClean\Commands\ValidateModelCleanupCommand;
use Illuminate\Support\Facades\Event;
use Illuminate\Console\Scheduling\Schedule;

/**
 * AClean 模块服务提供者
 *
 * 注册模块的服务、命令、路由等
 */
class ACleanServiceProvider extends ABaseServiceProvider
{
    /**
     * 模块名（小写）
     */
    protected string $nameLower = 'aclean';

    /**
     * 模块名
     */
    protected string $name = 'AClean';

    /**
     * 模块路径
     */
    protected string $modulePath = __DIR__ . '/..';

    /**
     * 注册的命令
     */
    public array $commandList = [
        ScanModelsCommand::class,
        TestModelCleanupCommand::class,
        ValidateModelCleanupCommand::class,
        CleanupDataCommand::class,
        InsertCleanupAdminMenuCommand::class,
    ];

    /**
     * 注册命令调度
     */
    protected function registerCommandSchedules(): void
    {
        // 清理模块暂无定时任务
    }

    /**
     * 启动服务
     */
    public function boot(): void
    {
        parent::boot();

        // 发布配置文件
        $this->publishes([
            __DIR__ . '/../config/aclean.php' => config_path('aclean.php'),
        ], 'aclean-config');

        // 注册事件监听器
        $this->registerEventListeners();

        // 路由通过 route-attributes 自动注册,无需手动注册
    }

    /**
     * 注册事件监听器
     */
    protected function registerEventListeners(): void
    {
        // 清理任务开始事件
        Event::listen(
            \Modules\AClean\Events\TaskStartedEvent::class,
            \Modules\AClean\Listeners\LogTaskStartedListener::class
        );
    }

    /**
     * 获取提供的服务
     */
    public function provides(): array
    {
        return [
            'aclean.service',
        ];
    }
}
