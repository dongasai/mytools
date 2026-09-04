<?php

namespace Modules\ABase\Enums;

/**
 * 配置表备份压缩类型枚举
 */
enum ConfigDbCompressionType: string
{
    case NONE = 'none';
    case GZIP = 'gzip';
    case BZIP2 = 'bzip2';
    case ZIP = 'zip';

    /**
     * 获取压缩类型描述
     */
    public function getDescription(): string
    {
        return match ($this) {
            self::NONE => '不压缩',
            self::GZIP => 'GZIP压缩',
            self::BZIP2 => 'BZIP2压缩',
            self::ZIP => 'ZIP压缩',
        };
    }

    /**
     * 获取文件扩展名
     */
    public function getFileExtension(): string
    {
        return match ($this) {
            self::NONE => '.sql',
            self::GZIP => '.sql.gz',
            self::BZIP2 => '.sql.bz2',
            self::ZIP => '.zip',
        };
    }

    /**
     * 获取压缩级别范围
     */
    public function getCompressionRange(): array
    {
        return match ($this) {
            self::NONE => [],
            self::GZIP => [1, 9],
            self::BZIP2 => [1, 9],
            self::ZIP => [1, 9],
        };
    }

    /**
     * 获取默认压缩级别
     */
    public function getDefaultCompressionLevel(): int
    {
        return match ($this) {
            self::NONE => 0,
            self::GZIP => 6,
            self::BZIP2 => 6,
            self::ZIP => 6,
        };
    }

    /**
     * 是否支持压缩
     */
    public function supportsCompression(): bool
    {
        return $this !== self::NONE;
    }

    /**
     * 获取压缩图标
     */
    public function getIcon(): string
    {
        return match ($this) {
            self::NONE => '📄',
            self::GZIP => '🗜️',
            self::BZIP2 => '🗜️',
            self::ZIP => '📦',
        };
    }

    /**
     * 验证压缩类型是否有效
     */
    public static function isValid(string $type): bool
    {
        return in_array($type, array_column(self::cases(), 'value'));
    }

    /**
     * 获取所有可用压缩类型
     */
    public static function getAllTypes(): array
    {
        return array_map(fn($case) => [
            'value' => $case->value,
            'description' => $case->getDescription(),
            'icon' => $case->getIcon(),
            'file_extension' => $case->getFileExtension(),
            'supports_compression' => $case->supportsCompression(),
            'compression_range' => $case->getCompressionRange(),
            'default_level' => $case->getDefaultCompressionLevel(),
        ], self::cases());
    }
}
