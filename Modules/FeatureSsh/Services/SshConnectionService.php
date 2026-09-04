<?php

declare(strict_types=1);

namespace Modules\FeatureSsh\Services;

use Modules\FeatureSsh\Dtos\ServerInfo;
use Modules\FeatureSsh\Models\Authentication;
use Modules\FeatureSsh\Models\Server;
use Modules\FeatureSsh\Support\ConnectionPool;
use Modules\FeatureSsh\Support\SshClient;

/**
 * SSH连接管理服务
 *
 * @author AI开发团队
 * @date 2026-09-02
 */
class SshConnectionService
{
    /**
     * 获取连接（从连接池或新建）
     *
     * @param int $serverId 服务器ID
     * @param int|null $authId 认证ID，null时使用默认认证
     * @return SshClient SSH客户端
     * @throws \InvalidArgumentException 服务器不存在
     * @throws \RuntimeException 连接失败
     */
    public static function getConnection(int $serverId, ?int $authId = null): SshClient
    {
        $client = ConnectionPool::get($serverId, $authId);

        if ($client !== null && $client->isConnected()) {
            return $client;
        }

        $server = Server::find($serverId);

        if (!$server) {
            throw new \InvalidArgumentException('Server not found: ' . $serverId);
        }

        $authentication = self::resolveAuthentication($server, $authId);

        if (!$authentication) {
            throw new \RuntimeException('No valid authentication found for server: ' . $serverId);
        }

        $client = new SshClient($server);

        if (!$client->connect($authentication)) {
            throw new \RuntimeException('Failed to connect to server: ' . $server->getHostPort());
        }

        ConnectionPool::add($serverId, $client, $authId);

        return $client;
    }

    /**
     * 测试连接
     *
     * @param int $serverId 服务器ID
     * @param int|null $authId 认证ID，null时使用默认认证
     * @return array 测试结果
     */
    public static function testConnection(int $serverId, ?int $authId = null): array
    {
        $startTime = microtime(true);

        $server = Server::find($serverId);

        if (!$server) {
            return [
                'success' => false,
                'message' => 'Server not found',
                'latency' => 0,
            ];
        }

        $authentication = self::resolveAuthentication($server, $authId);

        if (!$authentication) {
            return [
                'success' => false,
                'message' => 'No valid authentication found',
                'latency' => 0,
            ];
        }

        $client = new SshClient($server);

        if (!$client->connect($authentication)) {
            return [
                'success' => false,
                'message' => 'Connection failed',
                'latency' => round((microtime(true) - $startTime) * 1000, 2),
            ];
        }

        $latency = round((microtime(true) - $startTime) * 1000, 2);

        $info = $client->getSystemInfo();

        $client->disconnect();

        return [
            'success' => true,
            'message' => 'Connection successful',
            'latency' => $latency,
            'system_info' => $info,
        ];
    }

    /**
     * 释放连接（归还连接池）
     *
     * @param SshClient $client SSH客户端
     */
    public static function releaseConnection(SshClient $client): void
    {
        ConnectionPool::remove($client->getServerId());
    }

    /**
     * 获取服务器信息
     *
     * @param int $serverId 服务器ID
     * @return ServerInfo 服务器信息
     * @throws \InvalidArgumentException 服务器不存在
     * @throws \RuntimeException 获取信息失败
     */
    public static function getServerInfo(int $serverId): ServerInfo
    {
        $client = self::getConnection($serverId);

        $info = $client->getSystemInfo();

        self::releaseConnection($client);

        return ServerInfo::fromCommandOutput($info);
    }

    /**
     * 解析认证方式
     *
     * @param Server $server 服务器模型
     * @param int|null $authId 认证ID
     * @return Authentication|null 认证模型
     */
    private static function resolveAuthentication(Server $server, ?int $authId = null): ?Authentication
    {
        if ($authId !== null) {
            return Authentication::find($authId);
        }

        return $server->defaultAuthentication();
    }

    /**
     * 释放指定服务器的所有连接
     *
     * @param int $serverId 服务器ID
     */
    public static function releaseServerConnections(int $serverId): void
    {
        ConnectionPool::releaseServer($serverId);
    }

    /**
     * 获取连接池状态
     *
     * @return array 连接池状态
     */
    public static function getPoolStatus(): array
    {
        return ConnectionPool::getStatus();
    }

    /**
     * 清理过期连接
     */
    public static function cleanupIdleConnections(): void
    {
        ConnectionPool::cleanup();
    }
}
