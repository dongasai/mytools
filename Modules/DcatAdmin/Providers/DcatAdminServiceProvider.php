<?php

namespace Modules\DcatAdmin\Providers;

use Illuminate\Support\Facades\Event;
use Illuminate\Support\Str;
use Nwidart\Modules\Traits\PathNamespace;

class DcatAdminServiceProvider extends \Modules\ABase\Support\ServiceProvider
{
    use PathNamespace;

    protected string $name = 'DcatAdmin';

    protected string $nameLower = 'module_dcatadmin';

    /**
     * 启动应用程序事件
     */
    public function boot(): void
    {
        $this->modulePath = dirname(__DIR__);
        $this->registerCommands();
        $this->registerCommandSchedules();
        $this->registerTranslations();
        $this->registerConfig();
        $this->registerViews();
        $this->loadMigrationsFrom(__DIR__.'/../../database/migrations');

        // 注册事件监听器
        $this->registerEventListeners();

        // 注册 Str::unescape() 宏，用于 Dcat Admin Grid\Column
        Str::macro('unescape', function ($value) {
            return $value;
        });

        // 覆盖后台名称配置
        $this->app->config->set('admin.name', '三牛开发者枢纽');
        $this->app->config->set('admin.title', '三牛开发者枢纽');
    }

    /**
     * 注册服务提供者
     */
    public function register(): void
    {
        // 所有ServiceProvider由module.json注册，不应在此手动注册
        $this->registerServices();
    }

    /**
     * 注册命令（格式为 Command::class）
     */
    protected function registerCommands(): void
    {
        $this->commands([
            \Modules\DcatAdmin\Console\CheckMenuValidity::class,
            \Modules\DcatAdmin\Console\CheckSpecificMenus::class,
            \Modules\DcatAdmin\Console\SyncAdminMenuCommand::class,
            \Modules\DcatAdmin\Console\MakeArchModuleCommand::class,
        ]);
    }

    /**
     * 注册命令调度
     */
    protected function registerCommandSchedules(): void
    {
        // $this->app->booted(function () {
        //     $schedule = $this->app->make(Schedule::class);
        //     $schedule->command('inspire')->hourly();
        // });
    }

    /**
     * 注册翻译文件
     */
    public function registerTranslations(): void
    {
        $langPath = resource_path('lang/modules/'.$this->nameLower);

        if (is_dir($langPath)) {
            $this->loadTranslationsFrom($langPath, $this->nameLower);
            $this->loadJsonTranslationsFrom($langPath);
        } else {
            $moduleLangPath = __DIR__.'/../../lang';
            if (is_dir($moduleLangPath)) {
                $this->loadTranslationsFrom($moduleLangPath, $this->nameLower);
                $this->loadJsonTranslationsFrom($moduleLangPath);
            }
        }
    }

    /**
     * 获取服务提供者提供的服务
     */
    public function provides(): array
    {
        return [];
    }

    /**
     * 注册服务
     */
    protected function registerServices(): void
    {
        // 注册 AdminService
        $this->app->singleton('admin.service', function ($app) {
            return new \Modules\DcatAdmin\Services\AdminService;
        });

        // 注册 CacheService
        $this->app->singleton('admin.cache', function ($app) {
            return new \Modules\DcatAdmin\Services\CacheService;
        });

        // 注册 LogService
        $this->app->singleton('admin.log', function ($app) {
            return new \Modules\DcatAdmin\Services\LogService;
        });
    }

    /**
     * 注册事件监听器
     */
    protected function registerEventListeners(): void
    {
        Event::listen(
            \Modules\DcatAdmin\Events\AdminActionEvent::class,
            \Modules\DcatAdmin\Listeners\AdminActionListener::class
        );
    }

    private function getPublishableViewPaths(): array
    {
        $paths = [];
        foreach (config('view.paths') as $path) {
            if (is_dir($path.'/modules/'.$this->nameLower)) {
                $paths[] = $path.'/modules/'.$this->nameLower;
            }
        }

        return $paths;
    }
}
