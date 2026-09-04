<?php

namespace Modules\ABase\Commands;

use Illuminate\Console\Command;
use Modules\ABase\Services\SystemInfoService;

/**
 * 输出版本信息命令
 *
 * 输出构建信息、框架版本和系统基本信息
 * php artisan version
 */
class VersionCommand extends Command
{
    /**
     * 命令的名称和签名
     *
     * @var string
     */
    protected $signature = 'version';

    /**
     * 命令的描述
     *
     * @var string
     */
    protected $description = '输出版本信息';

    /**
     * 执行命令
     */
    public function handle(): int
    {
        $this->info('╔════════════════════════════════════════╗');
        $this->info('║       能碳管理EMS 版本信息             ║');
        $this->info('╚════════════════════════════════════════╝');
        $this->newLine();

        $info = SystemInfoService::getAll();

        // 构建信息
        $this->info('【构建信息】');
        $this->line(sprintf('  构建时间(UTC): <comment>%s</comment>', $info['build']['build_time_utc']));
        $this->line(sprintf('  构建时间(本地): <comment>%s</comment>', $info['build']['build_time_local']));
        $this->line(sprintf('  构建分支: <comment>%s</comment>', $info['build']['build_branch']));
        $this->line(sprintf('  构建 Commit: <comment>%s</comment>', $info['build']['build_commit']));
        $this->line(sprintf('  构建执行者: <comment>%s</comment>', $info['build']['build_runner']));
        $this->newLine();

        // 框架信息
        $this->info('【框架信息】');
        $this->line(sprintf('  Laravel 版本: <comment>%s</comment>', $info['framework']['laravel_version']));
        $this->line(sprintf('  PHP 版本: <comment>%s</comment>', $info['framework']['php_version']));
        $this->line(sprintf('  应用名称: <comment>%s</comment>', $info['framework']['app_name']));
        $this->newLine();

        // 运行环境
        $this->info('【运行环境】');
        $this->line(sprintf('  运行环境: <comment>%s</comment>', $info['runtime']['environment']));
        $this->line(sprintf('  服务器时区: <comment>%s</comment>', $info['runtime']['timezone']));
        $this->line(sprintf('  操作系统: <comment>%s</comment>', $info['runtime']['os']));
        $this->line(sprintf('  运行方式: <comment>%s</comment>', $info['runtime']['sapi']));
        $this->newLine();

        // 数据库信息
        $this->info('【数据库信息】');
        $this->line(sprintf('  数据库连接: <comment>%s</comment>', $info['database']['connection']));
        $this->line(sprintf('  数据库名称: <comment>%s</comment>', $info['database']['database_name']));

        $dbVersionStyle = $info['database']['database_version'] === '无法获取' ? 'error' : 'comment';
        $this->line(sprintf('  数据库版本: <%s>%s</%s>', $dbVersionStyle, $info['database']['database_version'], $dbVersionStyle));

        $this->line(sprintf('  Composer 版本: <comment>%s</comment>', $info['database']['composer_version']));
        $this->newLine();

        $this->info('╔════════════════════════════════════════╗');
        $this->info('║        构建信息已输出完成              ║');
        $this->info('╚════════════════════════════════════════╝');

        return self::SUCCESS;
    }
}
