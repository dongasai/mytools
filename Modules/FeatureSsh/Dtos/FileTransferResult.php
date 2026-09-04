<?php

declare(strict_types=1);

namespace Modules\FeatureSsh\Dtos;

/**
 * 文件传输结果DTO
 *
 * @author AI开发团队
 * @date 2026-09-02
 */
class FileTransferResult
{
    /**
     * 源路径
     */
    public readonly string $sourcePath;

    /**
     * 目标路径
     */
    public readonly string $destinationPath;

    /**
     * 是否成功
     */
    public readonly bool $success;

    /**
     * 传输字节数
     */
    public readonly int $bytesTransferred;

    /**
     * 错误信息
     */
    public readonly ?string $error;

    /**
     * 耗时（毫秒）
     */
    public readonly int $duration;

    /**
     * 传输类型
     */
    public readonly string $type;

    /**
     * 构造函数
     *
     * @param string $sourcePath 源路径
     * @param string $destinationPath 目标路径
     * @param bool $success 是否成功
     * @param int $bytesTransferred 传输字节数
     * @param string|null $error 错误信息
     * @param int $duration 耗时（毫秒）
     * @param string $type 传输类型
     */
    public function __construct(
        string $sourcePath,
        string $destinationPath,
        bool $success,
        int $bytesTransferred = 0,
        ?string $error = null,
        int $duration = 0,
        string $type = 'upload',
    ) {
        $this->sourcePath = $sourcePath;
        $this->destinationPath = $destinationPath;
        $this->success = $success;
        $this->bytesTransferred = $bytesTransferred;
        $this->error = $error;
        $this->duration = $duration;
        $this->type = $type;
    }

    /**
     * 是否成功
     */
    public function isSuccess(): bool
    {
        return $this->success;
    }

    /**
     * 是否失败
     */
    public function isFailed(): bool
    {
        return !$this->success;
    }

    /**
     * 获取传输速度（字节/秒）
     */
    public function getTransferSpeed(): float
    {
        if ($this->duration <= 0) {
            return 0.0;
        }

        return ($this->bytesTransferred / $this->duration) * 1000;
    }

    /**
     * 获取格式化的传输速度
     */
    public function getFormattedSpeed(): string
    {
        $speed = $this->getTransferSpeed();

        if ($speed < 1024) {
            return sprintf('%.2f B/s', $speed);
        } elseif ($speed < 1024 * 1024) {
            return sprintf('%.2f KB/s', $speed / 1024);
        } else {
            return sprintf('%.2f MB/s', $speed / (1024 * 1024));
        }
    }

    /**
     * 获取格式化的字节数
     */
    public function getFormattedBytes(): string
    {
        $bytes = $this->bytesTransferred;

        if ($bytes < 1024) {
            return $bytes . ' B';
        } elseif ($bytes < 1024 * 1024) {
            return sprintf('%.2f KB', $bytes / 1024);
        } elseif ($bytes < 1024 * 1024 * 1024) {
            return sprintf('%.2f MB', $bytes / (1024 * 1024));
        } else {
            return sprintf('%.2f GB', $bytes / (1024 * 1024 * 1024));
        }
    }

    /**
     * 转换为数组
     */
    public function toArray(): array
    {
        return [
            'source_path' => $this->sourcePath,
            'destination_path' => $this->destinationPath,
            'success' => $this->success,
            'bytes_transferred' => $this->bytesTransferred,
            'formatted_bytes' => $this->getFormattedBytes(),
            'error' => $this->error,
            'duration' => $this->duration,
            'speed' => $this->getTransferSpeed(),
            'formatted_speed' => $this->getFormattedSpeed(),
            'type' => $this->type,
        ];
    }
}
