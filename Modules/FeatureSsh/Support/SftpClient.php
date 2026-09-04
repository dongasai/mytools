<?php

declare(strict_types=1);

namespace Modules\FeatureSsh\Support;

use phpseclib3\Net\SFTP;

/**
 * SFTP客户端封装
 *
 * @author AI开发团队
 * @date 2026-09-02
 */
class SftpClient
{
    /**
     * SFTP连接实例
     */
    private ?SFTP $sftp = null;

    /**
     * 服务器ID
     */
    private int $serverId;

    /**
     * 构造函数
     *
     * @param SFTP $sftp SFTP实例
     * @param int $serverId 服务器ID
     */
    public function __construct(SFTP $sftp, int $serverId)
    {
        $this->sftp = $sftp;
        $this->serverId = $serverId;
    }

    /**
     * 获取服务器ID
     */
    public function getServerId(): int
    {
        return $this->serverId;
    }

    /**
     * 上传文件
     *
     * @param string $localPath 本地文件路径
     * @param string $remotePath 远程文件路径
     * @return bool 是否上传成功
     */
    public function upload(string $localPath, string $remotePath): bool
    {
        if (!$this->isConnected()) {
            return false;
        }

        if (!file_exists($localPath)) {
            return false;
        }

        $result = $this->sftp->put($remotePath, $localPath, SFTP::SOURCE_LOCAL_FILE);

        return $result !== false;
    }

    /**
     * 下载文件
     *
     * @param string $remotePath 远程文件路径
     * @param string $localPath 本地文件路径
     * @return bool 是否下载成功
     */
    public function download(string $remotePath, string $localPath): bool
    {
        if (!$this->isConnected()) {
            return false;
        }

        $result = $this->sftp->get($remotePath, $localPath);

        return $result !== false;
    }

    /**
     * 列出目录内容
     *
     * @param string $path 目录路径
     * @return array 文件列表
     */
    public function listDirectory(string $path): array
    {
        if (!$this->isConnected()) {
            return [];
        }

        $contents = $this->sftp->nlist($path);

        if ($contents === false) {
            return [];
        }

        $result = [];

        foreach ($contents as $item) {
            if ($item === '.' || $item === '..') {
                continue;
            }

            $fullPath = rtrim($path, '/') . '/' . $item;
            $stat = $this->sftp->stat($fullPath);

            $result[] = [
                'name' => $item,
                'path' => $fullPath,
                'type' => $this->sftp->is_dir($fullPath) ? 'directory' : 'file',
                'size' => $stat['size'] ?? 0,
                'permissions' => $stat['permissions'] ?? null,
                'modified_time' => isset($stat['mtime']) ? date('Y-m-d H:i:s', $stat['mtime']) : null,
            ];
        }

        return $result;
    }

    /**
     * 创建目录
     *
     * @param string $path 目录路径
     * @param int $mode 权限模式
     * @param bool $recursive 是否递归创建
     * @return bool 是否创建成功
     */
    public function createDirectory(string $path, int $mode = 0755, bool $recursive = false): bool
    {
        if (!$this->isConnected()) {
            return false;
        }

        return $this->sftp->mkdir($path, $mode, $recursive);
    }

    /**
     * 删除文件或目录
     *
     * @param string $path 文件或目录路径
     * @param bool $recursive 是否递归删除（目录）
     * @return bool 是否删除成功
     */
    public function delete(string $path, bool $recursive = false): bool
    {
        if (!$this->isConnected()) {
            return false;
        }

        if ($this->sftp->is_dir($path)) {
            if ($recursive) {
                return $this->sftp->delete($path, $recursive);
            }

            return $this->sftp->rmdir($path);
        }

        return $this->sftp->delete($path);
    }

    /**
     * 检查路径是否存在
     *
     * @param string $path 路径
     * @return bool 是否存在
     */
    public function exists(string $path): bool
    {
        if (!$this->isConnected()) {
            return false;
        }

        return $this->sftp->file_exists($path);
    }

    /**
     * 检查是否为目录
     *
     * @param string $path 路径
     * @return bool 是否为目录
     */
    public function isDirectory(string $path): bool
    {
        if (!$this->isConnected()) {
            return false;
        }

        return $this->sftp->is_dir($path);
    }

    /**
     * 获取文件内容
     *
     * @param string $path 文件路径
     * @return string|false 文件内容或false
     */
    public function getFile(string $path): string|false
    {
        if (!$this->isConnected()) {
            return false;
        }

        return $this->sftp->get($path);
    }

    /**
     * 写入文件内容
     *
     * @param string $path 文件路径
     * @param string $content 文件内容
     * @return bool 是否写入成功
     */
    public function putFile(string $path, string $content): bool
    {
        if (!$this->isConnected()) {
            return false;
        }

        return $this->sftp->put($path, $content);
    }

    /**
     * 修改权限
     *
     * @param string $path 文件或目录路径
     * @param int $mode 权限模式
     * @return bool 是否修改成功
     */
    public function chmod(string $path, int $mode): bool
    {
        if (!$this->isConnected()) {
            return false;
        }

        return $this->sftp->chmod($mode, $path) !== false;
    }

    /**
     * 获取文件大小
     *
     * @param string $path 文件路径
     * @return int 文件大小（字节）
     */
    public function size(string $path): int
    {
        if (!$this->isConnected()) {
            return 0;
        }

        return $this->sftp->size($path) ?: 0;
    }

    /**
     * 检查连接是否活跃
     *
     * @return bool 是否活跃
     */
    public function isConnected(): bool
    {
        return $this->sftp !== null && $this->sftp->isConnected();
    }

    /**
     * 断开连接
     */
    public function disconnect(): void
    {
        if ($this->sftp !== null) {
            $this->sftp->disconnect();
        }

        $this->sftp = null;
    }
}
