<?php

namespace Modules\MyToolsMain\Providers;

use Modules\ABase\Support\ServiceProvider;

/**
 * MyToolsMain 模块服务提供者
 *
 * 个人工具主模块，提供项目核心功能
 */
class MyToolsMainServiceProvider extends ServiceProvider
{
    /**
     * 模块名称
     */
    protected string $moduleName = 'MyToolsMain';

    protected string $name = 'MyToolsMain';

    protected string $nameLower = 'module_mytoolsmain';

    /**
     * 启动服务
     */
    public function boot(): void
    {
        $this->modulePath = dirname(__DIR__);
        parent::boot();
    }
}
