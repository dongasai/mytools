<?php

declare(strict_types=1);

namespace Modules\Application\Providers;


use Illuminate\Console\Scheduling\Schedule;
use Modules\ABase\Support\ServiceProvider;
use Modules\Application\Console\BubbleSuperiorHookCommand;
use Modules\Application\Console\CleanJobRunsCommand;
use Modules\Application\Console\DbPerformanceTestCommand;
use Modules\Application\Console\DepartmentApproverHookCommand;
use Modules\Application\Console\DepartmentLeaderHookCommand;
use Modules\Application\Console\InsertSystemLogAdminMenu;
use Modules\Application\Console\OrganizationMemberHookCommand;
use Modules\Application\Console\OrganizationTypeHookCommand;
use Modules\Application\Console\SuperiorHookCommand;
use Modules\Application\Console\TestQueueCommand;
use Modules\Application\Console\UserDetailHookCommand;
use Modules\Application\Console\UserListHookCommand;
use Modules\Application\Console\UserTypeHookCommand;
use Modules\Application\Events\ConfigChangedEvent;
use Modules\Application\Events\SystemLogCreatedEvent;
use Modules\Application\Events\ViewConfigChangedEvent;
use Modules\Application\Listeners\SystemEventListener;

/**
 * Application模块主服务提供者
 *
 * 负责注册模块的控制台命令和其他服务
 */
class ApplicationServiceProvider extends ServiceProvider
{
    protected string $name = 'Application';
    protected string $nameLower = 'module_application';
    /**
     * 注册服务
     */
    public function register(): void
    {
        //
    }


    /**
     * 事件到监听器的映射
     *
     * @var array
     */
    protected $listen = [
        ConfigChangedEvent::class => [
            SystemEventListener::class . '@handleConfigChanged',
        ],
        ViewConfigChangedEvent::class => [
            SystemEventListener::class . '@handleViewConfigChanged',
        ],
        SystemLogCreatedEvent::class => [
            SystemEventListener::class . '@handleSystemLogCreated',
        ],
    ];

    /**
     * 需要注册的订阅者
     *
     * @var array
     */
    protected $subscribe = [
        SystemEventListener::class,
    ];




    /**
     * 启动服务
     */
    public function boot(): void
    {
        // 注册事件监听器
        $this->registerEvents();

        // 注册命令
        $this->registerCommands();

        // 注册定时任务
        $this->registerSchedules();
        $this->modulePath = dirname(__DIR__);
        $this->registerConfig();
    }

    /**
     * 注册事件和监听器
     */
    protected function registerEvents(): void
    {
        $events = $this->app->make('events');

        foreach ($this->listen as $event => $listeners) {
            foreach ($listeners as $listener) {
                $events->listen($event, $listener);
            }
        }

        foreach ($this->subscribe as $subscriber) {
            $events->subscribe($subscriber);
        }
    }

    /**
     * 注册命令
     */
    protected function registerCommands(): void
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                CleanJobRunsCommand::class,
                DbPerformanceTestCommand::class,
                TestQueueCommand::class,
                InsertSystemLogAdminMenu::class,
                // Hook相关控制台命令
                OrganizationTypeHookCommand::class,
                OrganizationMemberHookCommand::class,
                DepartmentLeaderHookCommand::class,
                DepartmentApproverHookCommand::class,
                SuperiorHookCommand::class,
                BubbleSuperiorHookCommand::class,
                // 用户相关Hook控制台命令
                UserListHookCommand::class,
                UserDetailHookCommand::class,
                UserTypeHookCommand::class,
            ]);
        }
    }

    /**
     * 注册System模块相关的定时任务
     */
    protected function registerSchedules(): void
    {
        // 在应用完全启动后注册定时任务
        $this->app->booted(function () {
            $schedule = $this->app->make(Schedule::class);

            // 每小时清理job_runs表，保留5天数据
            $schedule->command('system:clean-job-runs')
                ->hourly()
                ->description('清理job_runs表过期记录（保留5天）')
                ->withoutOverlapping() // 防止重复执行
                ->runInBackground(); // 后台运行
        });
    }
}
