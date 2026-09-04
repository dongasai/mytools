<?php

namespace Modules\FeatureAi\Providers;

use Nwidart\Modules\Support\ModuleServiceProvider;
use Illuminate\Console\Scheduling\Schedule;
use Modules\FeatureAi\Commands\Text2ImageCommand;

class FeatureAiServiceProvider extends ModuleServiceProvider
{
    /**
     * The name of the module.
     */
    protected string $name = 'FeatureAi';

    /**
     * The lowercase version of the module name.
     */
    protected string $nameLower = 'featureai';

    /**
     * Command classes to register.
     *
     * @var string[]
     */
    protected array $commands = [
        Text2ImageCommand::class,
    ];

    /**
     * Provider classes to register.
     *
     * @var string[]
     */
    protected array $providers = [
        EventServiceProvider::class,
        DcatAdminRouteServiceProvider::class,
    ];

    /**
     * Register module services.
     */
    public function register(): void
    {
        // 注册 providers 数组中的服务提供者（父类会自动注册）
        parent::register();

        // 加载模块配置
        $this->loadConfigs();
    }

    /**
     * Load module configuration files.
     */
    protected function loadConfigs(): void
    {
        $configPath = module_path('FeatureAi', 'config');

        if (is_dir($configPath)) {
            foreach (glob($configPath . '/*.php') as $configFile) {
                $configName = basename($configFile, '.php');
                $this->mergeConfigFrom($configFile, $configName);
            }
        }
    }

    /**
     * Define module schedules.
     *
     * @param $schedule
     */
    // protected function configureSchedules(Schedule $schedule): void
    // {
    //     $schedule->command('inspire')->hourly();
    // }
}