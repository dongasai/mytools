<?php

namespace Modules\FeatureAi\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

/**
 * 清理AI生成的临时图片文件
 *
 * 删除超过24小时的临时文件（ai/temp/feature_ai/目录）
 */
class CleanTempFilesCommand extends Command
{
    protected $signature = 'feature-ai:clean-temp-files
                            {--hours=24 : 文件过期时间(小时)}
                            {--force : 强制删除所有临时文件}';

    protected $description = '清理AI生成的临时图片文件(默认24小时过期)';

    public function handle()
    {
        $hours = (int) $this->option('hours');
        $force = $this->option('force');
        $disk = config('ai_providers.image_storage.disk', 'local');
        $tempPath = 'ai/temp/feature_ai';

        $this->info('=== 清理FeatureAi临时文件 ===');
        $this->info('存储磁盘: ' . $disk);
        $this->info('临时目录: ' . $tempPath);
        $this->info('过期时间: ' . $hours . ' 小时');
        $this->info('强制删除: ' . ($force ? '是' : '否'));

        if (!Storage::disk($disk)->exists($tempPath)) {
            $this->info('临时目录不存在，无需清理');
            return Command::SUCCESS;
        }

        $cutoffTime = Carbon::now()->subHours($hours);
        $deletedCount = 0;
        $deletedSize = 0;

        // 递归遍历临时目录
        $allFiles = Storage::disk($disk)->allFiles($tempPath);

        foreach ($allFiles as $file) {
            // 获取文件最后修改时间
            $lastModified = Storage::disk($disk)->lastModified($file);
            $fileTime = Carbon::createFromTimestamp($lastModified);

            // 强制删除或过期删除
            $shouldDelete = $force || $fileTime->lt($cutoffTime);

            if ($shouldDelete) {
                $fileSize = Storage::disk($disk)->size($file);
                Storage::disk($disk)->delete($file);
                $deletedCount++;
                $deletedSize += $fileSize;

                $this->line("已删除: {$file} ({$fileSize} bytes, 创建时间: {$fileTime})");
            }
        }

        $this->newLine();
        $this->info("清理完成:");
        $this->info("  删除文件: {$deletedCount} 个");
        $this->info("  释放空间: " . $this->formatSize($deletedSize));

        // 清理空目录
        $this->cleanEmptyDirectories($disk, $tempPath);

        return Command::SUCCESS;
    }

    /**
     * 清理空目录
     */
    protected function cleanEmptyDirectories(string $disk, string $basePath): void
    {
        $allDirs = Storage::disk($disk)->allDirectories($basePath);

        // 从最深层的目录开始清理
        $sortedDirs = array_reverse($allDirs);

        foreach ($sortedDirs as $dir) {
            $files = Storage::disk($disk)->files($dir);
            $subDirs = Storage::disk($disk)->directories($dir);

            if (empty($files) && empty($subDirs)) {
                Storage::disk($disk)->deleteDirectory($dir);
                $this->line("已删除空目录: {$dir}");
            }
        }
    }

    /**
     * 格式化文件大小
     */
    protected function formatSize(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $size = $bytes;
        $unit = 0;

        while ($size >= 1024 && $unit < count($units) - 1) {
            $size /= 1024;
            $unit++;
        }

        return round($size, 2) . ' ' . $units[$unit];
    }
}