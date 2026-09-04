<?php

declare(strict_types=1);

namespace Modules\FeatureExcel\Providers;

use Modules\FeatureExcel\Console\ParseExcelCommand;
use Modules\FeatureExcel\Console\ValidateTemplateCommand;
use Modules\FeatureExcel\Console\GenerateTemplateCommand;

/**
 * Excel导入导出引擎模块服务提供者
 *
 * 注册模块配置、Console命令和基础服务
 */
class FeatureExcelServiceProvider extends \Modules\ABase\Support\ServiceProvider
{
    /**
     * 模块名称
     */
    protected string $name = 'FeatureExcel';

    /**
     * 模块小写标识（用于配置键名前缀）
     */
    protected string $nameLower = 'module_feature_excel';

    /**
     * 注册的 Console 命令列表（由 ABase ServiceProvider 自动注册）
     *
     * @var array<int, class-string>
     */
    public array $commandList = [
        ValidateTemplateCommand::class,
        ParseExcelCommand::class,
        GenerateTemplateCommand::class,
    ];

    /**
     * 注册服务到容器
     */
    public function register(): void
    {
        // 暂无需要注册的服务
    }

    /**
     * 启动模块服务
     */
    public function boot(): void
    {
        $this->modulePath = dirname(__DIR__);
        parent::boot();
    }
}
