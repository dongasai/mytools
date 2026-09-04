<?php

namespace Modules\DcatAdmin\DcatAdmin\Controllers;

use Dcat\Admin\Http\Controllers\AdminController;
use Dcat\Admin\Layout\Content;
use Dcat\Admin\Widgets\Card;
use Dcat\Admin\Widgets\Table;
use Modules\ABase\Services\SystemInfoService;

/**
 * 系统信息控制器
 *
 * 展示构建信息、框架版本、运行环境和数据库信息
 */
class SystemInfoController extends AdminController
{
    /**
     * 系统信息页面
     */
    public function index(Content $content): Content
    {
        $info = SystemInfoService::getAll();

        return $content
            ->title('系统信息')
            ->description('构建信息、框架版本和运行环境')
            ->body($this->buildBuildCard($info['build']))
            ->body($this->buildFrameworkCard($info['framework']))
            ->body($this->buildRuntimeCard($info['runtime']))
            ->body($this->buildDatabaseCard($info['database']));
    }

    /**
     * 构建信息卡片
     */
    private function buildBuildCard(array $build): Card
    {
        $rows = [
            ['构建时间(UTC)', $build['build_time_utc']],
            ['构建时间(本地)', $build['build_time_local']],
            ['构建分支', $build['build_branch']],
            ['构建 Commit', $build['build_commit']],
            ['构建执行者', $build['build_runner']],
        ];

        return Card::make('构建信息', new Table(['项目', '值'], $rows));
    }

    /**
     * 框架信息卡片
     */
    private function buildFrameworkCard(array $framework): Card
    {
        $rows = [
            ['Laravel 版本', $framework['laravel_version']],
            ['PHP 版本', $framework['php_version']],
            ['应用名称', $framework['app_name']],
        ];

        return Card::make('框架信息', new Table(['项目', '值'], $rows));
    }

    /**
     * 运行环境卡片
     */
    private function buildRuntimeCard(array $runtime): Card
    {
        $rows = [
            ['运行环境', $runtime['environment']],
            ['服务器时区', $runtime['timezone']],
            ['操作系统', $runtime['os']],
            ['运行方式', $runtime['sapi']],
        ];

        return Card::make('运行环境', new Table(['项目', '值'], $rows));
    }

    /**
     * 数据库信息卡片
     */
    private function buildDatabaseCard(array $database): Card
    {
        $rows = [
            ['数据库连接', $database['connection']],
            ['数据库名称', $database['database_name']],
            ['数据库版本', $database['database_version']],
            ['Composer 版本', $database['composer_version']],
        ];

        return Card::make('数据库信息', new Table(['项目', '值'], $rows));
    }
}
