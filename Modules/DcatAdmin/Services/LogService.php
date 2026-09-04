<?php

namespace Modules\DcatAdmin\Services;

use Carbon\Carbon;
use DLaravel\Helper\Logger;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;

/**
 * 日志管理服务
 */
class LogService
{
    /**
     * 清理旧日志
     *
     * @param  int  $days  保留天数
     */
    public function cleanOldLogs(int $days = 30): array
    {
        $results = [];
        $logPath = storage_path('logs');

        try {
            $cutoffDate = Carbon::now()->subDays($days);
            $deletedFiles = 0;
            $deletedSize = 0;

            $files = File::glob($logPath.'/*.log');

            foreach ($files as $file) {
                $fileTime = Carbon::createFromTimestamp(File::lastModified($file));

                if ($fileTime->lt($cutoffDate)) {
                    $size = File::size($file);
                    File::delete($file);
                    $deletedFiles++;
                    $deletedSize += $size;
                }
            }

            $results = [
                'status' => 'success',
                'message' => "清理完成，删除了 {$deletedFiles} 个文件",
                'deleted_files' => $deletedFiles,
                'deleted_size' => $this->formatBytes($deletedSize),
            ];

            Log::info('Admin: 日志清理完成', $results);

        } catch (\Exception $e) {
            $results = [
                'status' => 'error',
                'message' => $e->getMessage(),
            ];

            Logger::error('Admin: 日志清理失败', ['error' => $e->getMessage()]);
        }

        return $results;
    }

    /**
     * 获取日志统计信息
     */
    public function getLogStatistics(): array
    {
        $logPath = storage_path('logs');
        $files = File::glob($logPath.'/*.log');

        $totalSize = 0;
        $fileCount = count($files);
        $oldestFile = null;
        $newestFile = null;

        foreach ($files as $file) {
            $totalSize += File::size($file);
            $fileTime = Carbon::createFromTimestamp(File::lastModified($file));

            if (! $oldestFile || $fileTime->lt($oldestFile)) {
                $oldestFile = $fileTime;
            }

            if (! $newestFile || $fileTime->gt($newestFile)) {
                $newestFile = $fileTime;
            }
        }

        return [
            'total_files' => $fileCount,
            'total_size' => $this->formatBytes($totalSize),
            'oldest_file' => $oldestFile?->format('Y-m-d H:i:s'),
            'newest_file' => $newestFile?->format('Y-m-d H:i:s'),
        ];
    }

    /**
     * 格式化字节数
     */
    protected function formatBytes(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];

        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }

        return round($bytes, 2).' LogService.php'.$units[$i];
    }
}
