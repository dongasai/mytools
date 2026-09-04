<?php

namespace Modules\ABase\Support;

use DLaravel\Exception\LogicException;
use Illuminate\Support\Facades\Blade;

/**
 * 模块服务提供者基类
 *
 * 提供通用的 publishes 和 merge_config_from 方法
 */
abstract class ServiceProvider extends \Illuminate\Support\ServiceProvider
{
    protected string $nameLower = 'module_abase';

    protected string $name = 'module_abase';

    protected string $modulePath = '';

    /**
     * 注册的命令
     * @var array $commandList
     */
    public  array $commandList = [];


    public function boot()
    {
        $MigrationsPath = $this->modulePath . '/Database/Migrations';

        $this->registerCommands();
        $this->registerCommandSchedules();
        $this->registerTranslations();
        $this->registerConfig();
        $this->registerViews();
        $this->loadMigrationsFrom($MigrationsPath);
    }

    protected function registerCommands(): void
    {
        $this->commands($this->commandList);
    }


    /**
     * Register translations.
     */
    public function registerTranslations(): void
    {
        $langPath = resource_path('lang/modules/' . $this->nameLower);

        if (is_dir($langPath)) {
            $this->loadTranslationsFrom($langPath, $this->nameLower);
            $this->loadJsonTranslationsFrom($langPath);
        } else {
            // 使用 modulePath 属性而不是 module_path 函数，避免模块名查找问题
            $moduleLangPath = $this->modulePath . '/lang';
            $this->loadTranslationsFrom($moduleLangPath, $this->nameLower);
            $this->loadJsonTranslationsFrom($moduleLangPath);
        }
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
     * Merge config from the given path recursively.
     */
    protected function merge_config_from(string $path, string $key): void
    {
        // 获取已存在的配置，如果没有则返回空数组
        $existing = config($key, []);
        // 加载模块的配置文件
        $module_config = require $path;
        // 非数组配置文件跳过合并
        if (!is_array($module_config)) {
            return;
        }
        // 使用递归数组合并，保持原有的配置结构
        config([$key => array_replace_recursive($existing, $module_config)]);
    }

    /**
     * 注册视图
     */
    public function registerViews(): void
    {
        $viewPath = resource_path('views/modules/' . $this->nameLower);
        // 使用直接路径而不是 module_path 函数，避免在模块系统初始化前调用
        if (empty($this->modulePath)) {
            throw new \ErrorException("模块 {$this->name} 缺少属性");
        }
        $sourcePath = $this->modulePath . '/resources/views';

        if (is_dir($sourcePath)) {
            $this->publishes([$sourcePath => $viewPath], ['views', $this->nameLower . '-module-views']);

            $this->loadViewsFrom($sourcePath, $this->nameLower);
        }

        Blade::componentNamespace('Modules\\' . $this->name . '\\View\\Components', $this->nameLower);
    }

    /**
     * 注册配置文件
     */
    protected function registerConfig(): void
    {
        $configPath = $this->modulePath . '/config';
        if (is_dir($configPath)) {


            foreach (glob($configPath . '/*.php') as $file) {
                $name = basename($file, '.php');
                $this->merge_config_from($file, strtolower($this->nameLower) . '::' . $name);
            }
        }
    }
}
