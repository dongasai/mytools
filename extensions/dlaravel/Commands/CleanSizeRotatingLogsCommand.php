<?php

namespace DLaravel\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

/**
 * 清理 size_rotating_daily 日志文件的计划任务
 *
 * 功能：
 * 1. 删除超过指定天数的日志备份文件
 * 2. 自动读取 size_rotating_daily.days 配置作为默认保留天数
 * 3. 提供详细的清理报告
 */
class CleanSizeRotatingLogsCommand extends Command
{
    /**
     * 命令签名
     *
     * @var string
     */
    protected $signature = 'ucore:clean-size-rotating-logs {--days= : 保留的天数，默认使用 size_rotating_daily.days 配置} {--dry-run : 仅显示将要删除的文件，不实际删除}';

    /**
     * 命令描述
     *
     * @var string
     */
    protected $description = '清理 size_rotating_daily 目录中超过指定天数的日志备份文件';

    /**
     * 日志备份目录路径
     */
    protected string $logBackupPath;

    /**
     * 构造函数
     */
    public function __construct()
    {
        parent::__construct();
        $this->logBackupPath = storage_path('logs/size_rotating_daily');
    }

    /**
     * 获取默认保留天数
     * 从 size_rotating_daily 日志配置中读取 days 参数
     */
    protected function getDefaultDays(): int
    {
        $loggingConfig = config('logging.channels.size_rotating_daily', []);

        return $loggingConfig['days'] ?? 7; // 如果配置不存在，默认7天
    }

    /**
     * 执行命令
     */
    public function handle(): int
    {
        // 如果没有指定 days 参数，使用配置文件中的值
        $days = $this->option('days');
        if ($days === null) {
            $days = $this->getDefaultDays();
        } else {
            $days = (int) $days;
        }

        $dryRun = $this->option('dry-run');

        $this->info('开始清理 size_rotating_daily 日志文件...');

        // 显示配置来源
        if ($this->option('days') === null) {
            $this->info("保留天数: {$days} 天 (来自 size_rotating_daily.days 配置)");
        } else {
            $this->info("保留天数: {$days} 天 (命令行参数)");
        }

        $this->info("备份目录: {$this->logBackupPath}");

        if ($dryRun) {
            $this->warn('*** 这是一次试运行，不会实际删除文件 ***');
        }

        // 检查目录是否存在
        if (! File::exists($this->logBackupPath)) {
            $this->warn("备份目录不存在: {$this->logBackupPath}");

            return Command::SUCCESS;
        }

        // 计算截止日期
        $cutoffDate = Carbon::now()->subDays($days);
        $this->info("将删除 {$cutoffDate->format('Y-m-d')} 之前的日志文件");

        // 获取所有日志文件
        $files = File::files($this->logBackupPath);

        if (empty($files)) {
            $this->info('备份目录中没有找到任何文件');

            return Command::SUCCESS;
        }

        $deletedFiles = [];
        $keptFiles = [];
        $totalSize = 0;

        foreach ($files as $file) {
            $filename = $file->getFilename();
            $filePath = $file->getPathname();
            $fileModifiedTime = Carbon::createFromTimestamp($file->getMTime());
            $fileSize = $file->getSize();

            // 检查文件是否超过保留期限
            if ($fileModifiedTime->lt($cutoffDate)) {
                $deletedFiles[] = [
                    'name' => $filename,
                    'path' => $filePath,
                    'modified' => $fileModifiedTime->format('Y-m-d H:i:s'),
                    'size' => $fileSize,
                ];
                $totalSize += $fileSize;

                // 如果不是试运行，则删除文件
                if (! $dryRun) {
                    try {
                        File::delete($filePath);
                        $this->line("✓ 已删除: {$filename} ({$this->formatBytes($fileSize)})");
                    } catch (\Exception $e) {
                        $this->error("✗ 删除失败: {$filename} - {$e->getMessage()}");
                    }
                } else {
                    $this->line("○ 将删除: {$filename} ({$this->formatBytes($fileSize)}) - 修改时间: {$fileModifiedTime->format('Y-m-d H:i:s')}");
                }
            } else {
                $keptFiles[] = [
                    'name' => $filename,
                    'modified' => $fileModifiedTime->format('Y-m-d H:i:s'),
                    'size' => $fileSize,
                ];
                $this->line("○ 保留: {$filename} ({$this->formatBytes($fileSize)}) - 修改时间: {$fileModifiedTime->format('Y-m-d H:i:s')}");
            }
        }

        // 显示清理报告
        $this->newLine();
        $this->info('=== 清理报告 ===');
        $this->info('总文件数: '.count($files));
        $this->info('删除文件数: '.count($deletedFiles));
        $this->info('保留文件数: '.count($keptFiles));
        $this->info('释放空间: '.$this->formatBytes($totalSize));

        if ($dryRun && ! empty($deletedFiles)) {
            $this->newLine();
            $this->warn("要实际执行删除操作，请运行: php artisan ucore:clean-size-rotating-logs --days={$days}");
        }

        return Command::SUCCESS;
    }

    /**
     * 格式化字节大小
     */
    protected function formatBytes(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);

        $bytes /= pow(1024, $pow);

        return round($bytes, 2).' '.$units[$pow];
    }
}
