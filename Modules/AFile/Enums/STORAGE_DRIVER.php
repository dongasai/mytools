<?php

namespace Modules\AFile\Enums;

use DLaravel\Enum\EnumCore;
use DLaravel\Enum\EnumToInt;

/**
 * 存储驱动枚举
 */
enum STORAGE_DRIVER: string
{
    use EnumCore;

    /**
     * 本地存储
     */
    case LOCAL = 'local';

    /**
     * 公开本地存储
     */
    case PUBLIC = 'public';

    /**
     * AWS S3 存储
     */
    case S3 = 's3';

    /**
     * FTP 存储
     */
    case FTP = 'ftp';

    /**
     * SFTP 存储
     */
    case SFTP = 'sftp';

    /**
     * 阿里云 OSS 存储
     */
    case OSS = 'oss';

    /**
     * 获取所有驱动选项
     *
     * @return array
     */
    public static function getAll(): array
    {
        return [
            self::LOCAL->value => '本地存储',
            self::PUBLIC->value => '公开本地存储',
            self::S3->value => 'AWS S3',
            self::FTP->value => 'FTP',
            self::SFTP->value => 'SFTP',
            self::OSS->value => '阿里云 OSS',
        ];
    }

    /**
     * 获取驱动描述
     *
     * @return string
     */
    public function description(): string
    {
        return match ($this) {
            self::LOCAL => '本地文件存储，文件保存在 storage/app 目录',
            self::PUBLIC => '本地公开存储，文件保存在 storage/app/public 目录，可通过 URL 访问',
            self::S3 => 'Amazon S3 云存储服务',
            self::FTP => 'FTP 文件传输协议存储',
            self::SFTP => 'SSH 文件传输协议存储',
            self::OSS => '阿里云对象存储服务',
        };
    }

    /**
     * 获取驱动图标
     *
     * @return string
     */
    public function icon(): string
    {
        return match ($this) {
            self::LOCAL, self::PUBLIC => 'fa-folder',
            self::S3 => 'fa-cloud',
            self::FTP, self::SFTP => 'fa-server',
            self::OSS => 'fa-cloud-upload',
        };
    }
}