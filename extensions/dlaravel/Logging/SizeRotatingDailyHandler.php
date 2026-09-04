<?php

namespace DLaravel\Logging;

use Monolog\Handler\StreamHandler;
use Monolog\Level;
use Monolog\LogRecord;

/**
 * 支持文件大小限制的每日轮转日志处理器
 *
 * 功能特点：
 * 1. 按日期创建日志文件（如：laravel-2025-05-26.log）
 * 2. 当文件大小超过限制时，自动分割为备份文件（如：laravel-2025-05-26-1.log）
 * 3. 支持设置最大文件数量，自动清理旧文件
 */
class SizeRotatingDailyHandler extends StreamHandler
{
    /**
     * 最大文件大小（字节）
     */
    protected int $maxFileSize;

    /**
     * 基础文件名
     */
    protected string $baseFilename;

    /**
     * 最大文件数量
     */
    protected int $maxFiles;

    /**
     * 不活动自动分割时间（秒）
     */
    protected int $inactiveTimeout;

    /**
     * 上次写入时间戳
     */
    protected ?int $lastWriteTime = null;

    /**
     * 构造函数
     *
     * @param  string  $filename  日志文件路径
     * @param  int  $maxFiles  最大文件数量
     * @param  int|string|Level  $level  日志级别
     * @param  bool  $bubble  是否冒泡
     * @param  int|null  $filePermission  文件权限
     * @param  bool  $useLocking  是否使用文件锁
     * @param  int  $maxFileSize  最大文件大小（字节），默认100MB
     * @param  int  $inactiveTimeout  不活动自动分割时间（秒），默认60秒
     */
    public function __construct(
        string $filename,
        int $maxFiles = 0,
        int|string|Level $level = Level::Debug,
        bool $bubble = true,
        ?int $filePermission = null,
        bool $useLocking = false,
        int $maxFileSize = 104857600, // 100MB
        int $inactiveTimeout = 60 // 60秒不活动自动分割
    ) {
        $this->maxFileSize = $maxFileSize;
        $this->baseFilename = $filename;
        $this->maxFiles = $maxFiles;
        $this->inactiveTimeout = $inactiveTimeout;

        // 获取当前应该使用的文件名
        $currentFilename = $this->getCurrentFilename();

        // 初始化上次写入时间
        $this->initializeLastWriteTime($currentFilename);

        parent::__construct($currentFilename, $level, $bubble, $filePermission, $useLocking);
    }

    /**
     * 初始化上次写入时间
     */
    protected function initializeLastWriteTime(string $filename): void
    {
        if (file_exists($filename)) {
            $this->lastWriteTime = filemtime($filename);
        } else {
            $this->lastWriteTime = time();
        }
    }

    /**
     * 获取当前应该使用的文件名
     */
    protected function getCurrentFilename(): string
    {
        // 始终返回基础的时间文件名
        // 文件大小检查和轮转在写入时处理
        return $this->getTimedFilename();
    }

    /**
     * 获取带时间戳的文件名
     */
    protected function getTimedFilename(): string
    {
        $fileInfo = pathinfo($this->baseFilename);
        $timedFilename = $fileInfo['dirname'].'/'.$fileInfo['filename'].'-'.date('Y-m-d');

        if (isset($fileInfo['extension'])) {
            $timedFilename .= '.'.$fileInfo['extension'];
        }

        return $timedFilename;
    }

    /**
     * 获取下一个可用的备份文件名
     *
     * @param  string  $currentFilename  当前文件名
     */
    protected function getNextBackupFilename(string $currentFilename): string
    {
        $fileInfo = pathinfo($currentFilename);
        $baseName = $fileInfo['filename'];
        $extension = isset($fileInfo['extension']) ? '.'.$fileInfo['extension'] : '';
        $directory = $fileInfo['dirname'].'/size_rotating_daily';
        if (! is_dir($directory)) {
            mkdir($directory);
        }

        // 检查是否已经是分割文件（包含日期和计数器）
        // 格式：filename-YYYY-MM-DD-N 或 filename-YYYY-MM-DD
        if (preg_match('/^(.+)-(\d{4}-\d{2}-\d{2})$/', $baseName, $matches)) {
            // 是日期文件但还没有计数器，从1开始
            $baseNameWithDate = $baseName;
            $startCounter = 1;
        } else {
            // 不是预期的格式，直接添加计数器
            $baseNameWithDate = $baseName;
            $startCounter = 1;
        }

        $counter = $startCounter;
        do {
            $backupFilename = $directory.'/'.$baseNameWithDate.'-'.$counter.$extension;
            $counter++;
        } while (file_exists($backupFilename));

        return $backupFilename;
    }

    /**
     * 重写写入方法，添加文件大小和不活动检查
     */
    protected function write(LogRecord $record): void
    {
        // 检查是否需要因为不活动而分割文件
        $this->checkInactiveTimeout();

        // 先调用父类写入方法
        parent::write($record);

        // 更新最后写入时间
        $this->lastWriteTime = time();

        // 在写入后检查文件大小，如果超过限制则进行分割
        if ($this->url && file_exists($this->url) && filesize($this->url) >= $this->maxFileSize) {
            // 进行文件分割
            $this->rotateDueToSize();
        }
    }

    /**
     * 检查不活动超时并进行轮转
     */
    protected function checkInactiveTimeout(): void
    {
        if (!$this->lastWriteTime || $this->inactiveTimeout <= 0) {
            return;
        }

        $currentTime = time();
        $timeSinceLastWrite = $currentTime - $this->lastWriteTime;

        // 如果超过不活动时间，检查当前文件是否存在且有内容
        if ($timeSinceLastWrite >= $this->inactiveTimeout) {
            $currentFilename = $this->getCurrentFilename();

            // 如果当前文件存在且大小大于0，则进行分割
            if (file_exists($currentFilename) && filesize($currentFilename) > 0) {
                // 检查文件最后修改时间与上次写入时间的差异
                $fileModifiedTime = filemtime($currentFilename);

                // 如果文件的最后修改时间等于或早于上次写入时间，说明长时间没有新写入
                if ($fileModifiedTime <= $this->lastWriteTime) {
                    $this->rotateDueToInactive($currentFilename);
                }
            }
        }
    }

    /**
     * 由于不活动而进行轮转
     */
    protected function rotateDueToInactive(string $currentFilename): void
    {
        // 关闭当前文件句柄
        if ($this->stream) {
            fclose($this->stream);
            $this->stream = null;
        }

        // 获取备份文件名
        $backupFilename = $this->getNextBackupFilename($currentFilename);

        // 将当前文件重命名为备份文件
        if (file_exists($currentFilename)) {
            // 添加不活动轮转标识到备份文件名
            $timestamp = date('His');
            $backupInfo = pathinfo($backupFilename);
            $backupWithTimestamp = $backupInfo['dirname'] . '/' . $backupInfo['filename'] . '-inactive-' . $timestamp . '.' . $backupInfo['extension'];

            rename($currentFilename, $backupWithTimestamp);
        }

        // 重新获取当前文件名（因为可能已经跨日期）
        $newFilename = $this->getCurrentFilename();
        if ($newFilename !== $currentFilename) {
            // 如果日期变化，需要更新$this->url
            $this->url = $newFilename;
        }
    }

    /**
     * 由于文件大小超限而进行轮转
     */
    protected function rotateDueToSize(): void
    {
        // 关闭当前文件句柄
        if ($this->stream) {
            fclose($this->stream);
            $this->stream = null;
        }

        // 获取备份文件名
        $backupFilename = $this->getNextBackupFilename($this->url);

        // 将当前文件重命名为备份文件
        if (file_exists($this->url)) {
            // 添加大小轮转标识到备份文件名
            $fileSize = filesize($this->url);
            $sizeFormatted = $this->formatBytes($fileSize);
            $backupInfo = pathinfo($backupFilename);
            $backupWithSize = $backupInfo['dirname'] . '/' . $backupInfo['filename'] . '-size-' . $sizeFormatted . '.' . $backupInfo['extension'];

            rename($this->url, $backupWithSize);
        }

        // 继续使用原始文件名，下次写入时会创建新的空文件
        // 由于 $this->stream 已经设置为 null，父类的 write 方法会重新打开文件
    }

    /**
     * 格式化字节大小
     */
    private function formatBytes(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $bytes = (float) $bytes;
        $unitIndex = 0;

        while ($bytes >= 1024 && $unitIndex < count($units) - 1) {
            $bytes /= 1024;
            $unitIndex++;
        }

        return number_format($bytes, 0) . $units[$unitIndex];
    }
}
