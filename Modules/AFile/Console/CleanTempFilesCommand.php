<?php

namespace Modules\AFile\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

/**
 * 清理临时文件命令
 *
 * 定期清理超过指定天数的临时文件
 */
class CleanTempFilesCommand extends Command
{
    /**
     * 命令签名
     *
     * @var string
     */
    protected $signature = 'afile:clean-temp {--days=3 : 保留天数，默认3天}';

    /**
     * 命令描述
     *
     * @var string
     */
    protected $description = '清理超过指定天数的临时文件（默认保留3天）';

    /**
     * 临时文件目录路径
     *
     * @var string
     */
    private string $tempPath;

    /**
     * 执行命令
     */
    public function handle(): int
    {
        $this->tempPath = public_path('storage/temp');

        // 检查目录是否存在
        if (!File::isDirectory($this->tempPath)) {
            $this->info('临时文件目录不存在，无需清理');
            return self::SUCCESS;
        }

        $days = (int) $this->option('days');
        $this->info("开始清理 {$days} 天前的临时文件...");

        // 统计信息
        $deletedFiles = 0;
        $deletedSize = 0;
        $deletedDirs = 0;

        // 获取截止时间
        $cutoffTime = now()->subDays($days)->getTimestamp();

        // 遍历年月目录
        $yearMonthDirs = File::directories($this->tempPath);

        foreach ($yearMonthDirs as $yearMonthDir) {
            // 遍历日期目录
            $dayDirs = File::directories($yearMonthDir);

            foreach ($dayDirs as $dayDir) {
                // 检查目录修改时间
                $dirTime = filemtime($dayDir);

                if ($dirTime < $cutoffTime) {
                    // 删除整个日期目录
                    $files = File::allFiles($dayDir);
                    foreach ($files as $file) {
                        $deletedSize += $file->getSize();
                    }

                    $deletedFiles += count($files);

                    File::deleteDirectory($dayDir);
                    $deletedDirs++;

                    $this->line("  ✓ 已删除: " . basename($yearMonthDir) . '/' . basename($dayDir));
                }
            }

            // 删除空的年月目录
            if (empty(File::allFiles($yearMonthDir)) && empty(File::directories($yearMonthDir))) {
                File::deleteDirectory($yearMonthDir);
                $deletedDirs++;
            }
        }

        // 输出统计信息
        $this->newLine();
        $this->info("清理完成！");
        $this->table(
            ['统计项', '数量'],
            [
                ['删除文件数', $deletedFiles],
                ['删除目录数', $deletedDirs],
                ['释放空间', $this->formatBytes($deletedSize)],
            ]
        );

        return self::SUCCESS;
    }

    /**
     * 格式化字节大小
     *
     * @param int $bytes 字节数
     * @return string 格式化后的字符串
     */
    private function formatBytes(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= pow(1024, $pow);

        return round($bytes, 2) . ' ' . $units[$pow];
    }
}