<?php

namespace DLaravel\Logging;

use Monolog\Formatter\LineFormatter;
use Monolog\Level;
use Monolog\Logger;

/**
 * 支持文件大小限制的每日轮转日志驱动
 */
class SizeRotatingDailyLogger
{
    /**
     * 创建自定义 Monolog 实例
     */
    public function __invoke(array $config): Logger
    {
        $logger = new Logger($config['name'] ?? 'laravel');

        // 获取配置参数
        $path = $config['path'] ?? storage_path('logs/laravel.log');
        $level = $config['level'] ?? 'debug';
        $maxFiles = $config['days'] ?? 7;
        $maxFileSize = $this->parseFileSize($config['max_file_size'] ?? '100M');
        $permission = $config['permission'] ?? null;
        $locking = $config['locking'] ?? false;

        // 创建处理器
        $handler = new SizeRotatingDailyHandler(
            $path,
            $maxFiles,
            $this->parseLevel($level),
            true,
            $permission,
            $locking,
            $maxFileSize
        );

        // 设置格式化器
        $formatter = new LineFormatter(
            $config['format'] ?? null,
            $config['date_format'] ?? null,
            $config['allow_inline_line_breaks'] ?? false,
            $config['ignore_empty_context_and_extra'] ?? false
        );

        $handler->setFormatter($formatter);
        $logger->pushHandler($handler);

        return $logger;
    }

    /**
     * 解析日志级别
     */
    protected function parseLevel(string $level): Level
    {
        return match (strtolower($level)) {
            'debug' => Level::Debug,
            'info' => Level::Info,
            'notice' => Level::Notice,
            'warning' => Level::Warning,
            'error' => Level::Error,
            'critical' => Level::Critical,
            'alert' => Level::Alert,
            'emergency' => Level::Emergency,
            default => Level::Debug,
        };
    }

    /**
     * 解析文件大小配置
     */
    protected function parseFileSize(string $size): int
    {
        $size = trim($size);
        $unit = strtoupper(substr($size, -1));
        $value = (int) substr($size, 0, -1);

        return match ($unit) {
            'K' => $value * 1024,
            'M' => $value * 1024 * 1024,
            'G' => $value * 1024 * 1024 * 1024,
            default => (int) $size, // 如果没有单位，假设是字节
        };
    }
}
