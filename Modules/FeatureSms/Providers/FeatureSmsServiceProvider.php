<?php

namespace Modules\FeatureSms\Providers;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

/**
 * FeatureSms 模块服务提供者
 */
class FeatureSmsServiceProvider extends ServiceProvider
{
    /**
     * 模块名称
     */
    protected string $name = 'FeatureSms';

    /**
     * 模块命名空间
     */
    protected string $namespace = 'Modules\\FeatureSms\\';

    /**
     * 引导应用服务
     */
    public function boot(): void
    {
        $this->registerTranslations();
        $this->registerConfig();
        $this->registerViews();
        $this->loadMigrationsFrom(module_path($this->name, 'Database/Migrations'));
    }

    /**
     * 注册服务
     */
    public function register(): void
    {
        $this->app->register(RouteServiceProvider::class);
    }

    /**
     * 注册翻译文件
     */
    protected function registerTranslations(): void
    {
        $langPath = resource_path('lang/modules/' . $this->name);

        if (is_dir($langPath)) {
            $this->loadTranslationsFrom($langPath, $this->name);
        } else {
            $this->loadTranslationsFrom(module_path($this->name, 'resources/lang'), $this->name);
        }
    }

    /**
     * 注册配置文件
     */
    protected function registerConfig(): void
    {
        $this->mergeConfigFrom(
            module_path($this->name, 'config/config.php'),
            $this->name
        );
    }

    /**
     * 注册视图文件
     */
    public function registerViews(): void
    {
        $this->loadViewsFrom(module_path($this->name, 'resources/views'), $this->name);
    }

    /**
     * 获取提供的服务
     */
    public function provides(): array
    {
        return [];
    }
}
