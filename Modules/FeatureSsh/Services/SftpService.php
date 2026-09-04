<?php

declare(strict_types=1);

namespace Modules\FeatureSsh\Services;

use Modules\FeatureSsh\Dtos\FileTransferResult;
use Modules\FeatureSsh\Support\SftpClient;

/**
 * SFTP文件传输服务
 *
 * @author AI开发团队
 * @date 2026-09-02
 */
class SftpService
{
    /**
     * 上传文件
     *
     * @param int $serverId 服务器ID
     * @param string $localPath 本地文件路径
     * @param string $remotePath 远程文件路径
     * @return FileTransferResult 传输结果
     * @throws \InvalidArgumentException 服务器不存在或文件不存在
     * @throws \RuntimeException 上传失败
     */
    public static function upload(int $serverId, string $localPath, string $remotePath): FileTransferResult
    {
        if (!file_exists($localPath)) {
            throw new \InvalidArgumentException('Local file not found: ' . $localPath);
        }

        $client = SshConnectionService::getConnection($serverId);
        $sftp = $client->getSftp();

        if (!$sftp) {
            SshConnectionService::releaseConnection($client);
            throw new \RuntimeException('Failed to get SFTP subsystem');
        }

        $sftpClient = new SftpClient($sftp, $serverId);

        $startTime = microtime(true);
        $fileSize = filesize($localPath);

        $success = $sftpClient->upload($localPath, $remotePath);
        $duration = (int) round((microtime(true) - $startTime) * 1000);

        SshConnectionService::releaseConnection($client);

        $error = null;

        if (!$success) {
            $error = 'Upload failed';
        }

        return new FileTransferResult(
            sourcePath: $localPath,
            destinationPath: $remotePath,
            success: $success,
            bytesTransferred: $success ? $fileSize : 0,
            error: $error,
            duration: $duration,
            type: 'upload',
        );
    }

    /**
     * 下载文件
     *
     * @param int $serverId 服务器ID
     * @param string $remotePath 远程文件路径
     * @param string $localPath 本地文件路径
     * @return FileTransferResult 传输结果
     * @throws \InvalidArgumentException 服务器不存在
     * @throws \RuntimeException 下载失败
     */
    public static function download(int $serverId, string $remotePath, string $localPath): FileTransferResult
    {
        $client = SshConnectionService::getConnection($serverId);
        $sftp = $client->getSftp();

        if (!$sftp) {
            SshConnectionService::releaseConnection($client);
            throw new \RuntimeException('Failed to get SFTP subsystem');
        }

        $sftpClient = new SftpClient($sftp, $serverId);

        $startTime = microtime(true);

        $success = $sftpClient->download($remotePath, $localPath);
        $duration = (int) round((microtime(true) - $startTime) * 1000);
        $fileSize = $success ? filesize($localPath) : 0;

        SshConnectionService::releaseConnection($client);

        $error = null;

        if (!$success) {
            $error = 'Download failed';
        }

        return new FileTransferResult(
            sourcePath: $remotePath,
            destinationPath: $localPath,
            success: $success,
            bytesTransferred: $fileSize,
            error: $error,
            duration: $duration,
            type: 'download',
        );
    }

    /**
     * 列出目录内容
     *
     * @param int $serverId 服务器ID
     * @param string $path 目录路径
     * @return array 文件列表
     * @throws \InvalidArgumentException 服务器不存在
     * @throws \RuntimeException 操作失败
     */
    public static function listDirectory(int $serverId, string $path): array
    {
        $client = SshConnectionService::getConnection($serverId);
        $sftp = $client->getSftp();

        if (!$sftp) {
            SshConnectionService::releaseConnection($client);
            throw new \RuntimeException('Failed to get SFTP subsystem');
        }

        $sftpClient = new SftpClient($sftp, $serverId);
        $contents = $sftpClient->listDirectory($path);

        SshConnectionService::releaseConnection($client);

        return $contents;
    }

    /**
     * 创建目录
     *
     * @param int $serverId 服务器ID
     * @param string $path 目录路径
     * @param int $mode 权限模式
     * @param bool $recursive 是否递归创建
     * @return bool 是否创建成功
     * @throws \InvalidArgumentException 服务器不存在
     * @throws \RuntimeException 操作失败
     */
    public static function createDirectory(int $serverId, string $path, int $mode = 0755, bool $recursive = false): bool
    {
        $client = SshConnectionService::getConnection($serverId);
        $sftp = $client->getSftp();

        if (!$sftp) {
            SshConnectionService::releaseConnection($client);
            throw new \RuntimeException('Failed to get SFTP subsystem');
        }

        $sftpClient = new SftpClient($sftp, $serverId);
        $result = $sftpClient->createDirectory($path, $mode, $recursive);

        SshConnectionService::releaseConnection($client);

        return $result;
    }

    /**
     * 删除文件或目录
     *
     * @param int $serverId 服务器ID
     * @param string $path 文件或目录路径
     * @param bool $recursive 是否递归删除（目录）
     * @return bool 是否删除成功
     * @throws \InvalidArgumentException 服务器不存在
     * @throws \RuntimeException 操作失败
     */
    public static function delete(int $serverId, string $path, bool $recursive = false): bool
    {
        $client = SshConnectionService::getConnection($serverId);
        $sftp = $client->getSftp();

        if (!$sftp) {
            SshConnectionService::releaseConnection($client);
            throw new \RuntimeException('Failed to get SFTP subsystem');
        }

        $sftpClient = new SftpClient($sftp, $serverId);
        $result = $sftpClient->delete($path, $recursive);

        SshConnectionService::releaseConnection($client);

        return $result;
    }

    /**
     * 获取文件内容
     *
     * @param int $serverId 服务器ID
     * @param string $path 文件路径
     * @return string|false 文件内容或false
     * @throws \InvalidArgumentException 服务器不存在
     * @throws \RuntimeException 操作失败
     */
    public static function getFile(int $serverId, string $path): string|false
    {
        $client = SshConnectionService::getConnection($serverId);
        $sftp = $client->getSftp();

        if (!$sftp) {
            SshConnectionService::releaseConnection($client);
            throw new \RuntimeException('Failed to get SFTP subsystem');
        }

        $sftpClient = new SftpClient($sftp, $serverId);
        $content = $sftpClient->getFile($path);

        SshConnectionService::releaseConnection($client);

        return $content;
    }

    /**
     * 写入文件内容
     *
     * @param int $serverId 服务器ID
     * @param string $path 文件路径
     * @param string $content 文件内容
     * @return bool 是否写入成功
     * @throws \InvalidArgumentException 服务器不存在
     * @throws \RuntimeException 操作失败
     */
    public static function putFile(int $serverId, string $path, string $content): bool
    {
        $client = SshConnectionService::getConnection($serverId);
        $sftp = $client->getSftp();

        if (!$sftp) {
            SshConnectionService::releaseConnection($client);
            throw new \RuntimeException('Failed to get SFTP subsystem');
        }

        $sftpClient = new SftpClient($sftp, $serverId);
        $result = $sftpClient->putFile($path, $content);

        SshConnectionService::releaseConnection($client);

        return $result;
    }

    /**
     * 修改文件权限
     *
     * @param int $serverId 服务器ID
     * @param string $path 文件或目录路径
     * @param int $mode 权限模式
     * @return bool 是否修改成功
     * @throws \InvalidArgumentException 服务器不存在
     * @throws \RuntimeException 操作失败
     */
    public static function chmod(int $serverId, string $path, int $mode): bool
    {
        $client = SshConnectionService::getConnection($serverId);
        $sftp = $client->getSftp();

        if (!$sftp) {
            SshConnectionService::releaseConnection($client);
            throw new \RuntimeException('Failed to get SFTP subsystem');
        }

        $sftpClient = new SftpClient($sftp, $serverId);
        $result = $sftpClient->chmod($path, $mode);

        SshConnectionService::releaseConnection($client);

        return $result;
    }
}
