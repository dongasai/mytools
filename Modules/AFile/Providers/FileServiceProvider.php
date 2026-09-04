<?php

namespace Modules\AFile\Providers;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Events\Dispatcher;
use Modules\AFile\Console\CleanTempFilesCommand;
use Modules\AFile\Console\TestStorageCommand;
use Modules\AFile\Events\FileDeletedEvent;
use Modules\AFile\Events\FileUploadedEvent;
use Modules\AFile\Events\ImageDeletedEvent;
use Modules\AFile\Events\ImageUploadedEvent;
use Modules\AFile\Events\MarkFileUsedEvent;
use Modules\AFile\Events\MarkImageUsedEvent;
use Modules\AFile\Jobs\CleanDanglingFilesJob;
use Modules\AFile\Jobs\MarkDanglingFilesJob;
use Modules\AFile\Listeners\FileEventListener;
use Modules\AFile\Listeners\MarkFileUsedListener;
use Modules\AFile\Listeners\MarkImageUsedListener;
use Modules\AFile\Services\StorageConfigService;
use Modules\DcatAdmin\Support\Traits\RouteServiceProviderTrait;

/**
 * 文件模块服务提供者
 *
 * 注册路由、事件、服务
 */
class FileServiceProvider extends \Modules\ABase\Support\ServiceProvider
{
    use RouteServiceProviderTrait;

    protected string $name = 'AFile';

    protected string $nameLower = 'module_afile';

    public array $commandList = [
        TestStorageCommand::class,
        CleanTempFilesCommand::class,
    ];

    /**
     * 事件到监听器的映射
     *
     * @var array
     */
    protected $listen = [
        FileUploadedEvent::class => [
            FileEventListener::class . '@handleFileUploaded',
        ],
        ImageUploadedEvent::class => [
            FileEventListener::class . '@handleImageUploaded',
        ],
        FileDeletedEvent::class => [
            FileEventListener::class . '@handleFileDeleted',
        ],
        ImageDeletedEvent::class => [
            FileEventListener::class . '@handleImageDeleted',
        ],
        MarkFileUsedEvent::class => [
            MarkFileUsedListener::class,
        ],
        MarkImageUsedEvent::class => [
            MarkImageUsedListener::class,
        ],
    ];

    /**
     * 需要注册的订阅者
     *
     * @var array
     */
    protected $subscribe = [
        FileEventListener::class,
    ];

    /**
     * 注册服务
     */
    public function register(): void
    {
        // 在 register 阶段注册存储配置（防止 OSS 包创建空配置）
        $this->registerStorageConfigsEarly();

        // 注册服务...
        $this->app->singleton('file.service', function () {
            return new \Modules\AFile\Services\FileService;
        });

        $this->app->singleton('img.service', function () {
            return new \Modules\AFile\Services\ImgService;
        });

        $this->app->singleton('temporary.service', function () {
            return new \Modules\AFile\Services\TemporaryService;
        });
    }

    /**
     * 在 register 阶段注册存储配置
     * 防止 aliyun-oss-laravel 包创建空配置
     */
    protected function registerStorageConfigsEarly(): void
    {
        try {
            $storageConfigService = new StorageConfigService;
            $disks = $storageConfigService->getAllDisks();

            foreach ($disks as $disk) {
                $config = array_merge(['driver' => $disk->driver], $disk->config);
                config(["filesystems.disks.{$disk->name}" => $config]);
            }

            // 设置默认磁盘
            $defaultDisk = $storageConfigService->getDefaultDisk();
            if ($defaultDisk) {
                config(['filesystems.default' => $defaultDisk->name]);
            }
        } catch (\Exception) {
            // 如果数据库还未初始化，忽略错误
        }
    }

    /**
     * 启动服务
     */
    public function boot(): void
    {
        $this->modulePath = dirname(__DIR__);
        parent::boot();

        // 动态注册存储配置（从数据库读取）
        $this->registerStorageConfigs();

        $this->registerEvents();

        $this->app->booted(function () {
            $this->map();
        });
    }

    /**
     * 注册存储配置（从数据库读取）
     * 在 boot 阶段再次确认配置正确
     */
    protected function registerStorageConfigs(): void
    {
        try {
            $storageConfigService = app(StorageConfigService::class);
            $storageConfigService->registerStorageConfigs();
        } catch (\Exception) {
            // 如果数据库还未初始化（如迁移前），使用备用配置
            // 默认使用 local 存储
            config(['filesystems.default' => 'local']);
        }
    }

    /**
     * Register commands in the format of Command::class
     */
    protected function registerCommands(): void
    {
        $this->commands($this->commandList);
    }

    /**
     * Register command Schedules.
     */
    protected function registerCommandSchedules(): void
    {
        $schedule = $this->app->make(Schedule::class);

        // 每10分钟标记悬空文件
        $schedule->job(MarkDanglingFilesJob::class)
            ->everyTenMinutes()
            ->name('afile-标记悬空文件')
            ->withoutOverlapping()
            ->onOneServer();

        // 每小时清理悬空文件
        $schedule->job(CleanDanglingFilesJob::class)
            ->hourly()
            ->name('afile-清理悬空文件')
            ->withoutOverlapping()
            ->onOneServer();

        // 每天凌晨2点清理临时文件（默认保留3天）
        $schedule->command(CleanTempFilesCommand::class, ['--days' => 3])
            ->dailyAt('02:00')
            ->name('afile-清理临时文件')
            ->withoutOverlapping()
            ->onOneServer();
    }

    /**
     * Define the routes for the application.
     */
    public function map(): void
    {
        $this->mapAdminRoutes();
        $this->mapApiRoutes();
    }

    /**
     * Get the services provided by the provider.
     */
    public function provides(): array
    {
        return [];
    }

    /**
     * 注册事件监听器
     */
    protected function registerEvents(): void
    {
        /** @var Dispatcher $events */
        $events = $this->app['events'];

        foreach ($this->listen as $event => $listeners) {
            foreach ($listeners as $listener) {
                $events->listen($event, $listener);
            }
        }

        foreach ($this->subscribe as $subscriber) {
            $events->subscribe($subscriber);
        }
    }
}
