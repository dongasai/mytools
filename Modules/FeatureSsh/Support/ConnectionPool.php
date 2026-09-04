<?php

declare(strict_types=1);

namespace Modules\FeatureSsh\Support;

/**
 * SSH连接池管理
 *
 * @author AI开发团队
 * @date 2026-09-02
 */
class ConnectionPool
{
    /**
     * 连接池
     *
     * @var array<int, array<int, SshClient>>
     */
    private static array $pool = [];

    /**
     * 连接配置
     */
    private static array $config = [
        'max_connections' => 10,
        'max_per_server' => 3,
        'idle_timeout' => 300,
    ];

    /**
     * 初始化配置
     *
     * @param array $config 配置数组
     */
    public static function configure(array $config): void
    {
        self::$config = array_merge(self::$config, $config);
    }

    /**
     * 获取连接
     *
     * @param int $serverId 服务器ID
     * @param int|null $authId 认证ID
     * @return SshClient|null SSH客户端
     */
    public static function get(int $serverId, ?int $authId = null): ?SshClient
    {
        $poolKey = self::getPoolKey($serverId, $authId);

        if (isset(self::$pool[$serverId][$poolKey])) {
            $client = self::$pool[$serverId][$poolKey];

            if ($client->isConnected() && $client->getIdleTime() < self::$config['idle_timeout']) {
                return $client;
            }

            self::remove($serverId, $authId);
        }

        return null;
    }

    /**
     * 添加连接
     *
     * @param int $serverId 服务器ID
     * @param SshClient $client SSH客户端
     * @param int|null $authId 认证ID
     */
    public static function add(int $serverId, SshClient $client, ?int $authId = null): void
    {
        if (!isset(self::$pool[$serverId])) {
            self::$pool[$serverId] = [];
        }

        $serverConnectionCount = count(self::$pool[$serverId]);

        if ($serverConnectionCount >= self::$config['max_per_server']) {
            self::cleanupOldestForServer($serverId);
        }

        $poolKey = self::getPoolKey($serverId, $authId);
        self::$pool[$serverId][$poolKey] = $client;
    }

    /**
     * 移除连接
     *
     * @param int $serverId 服务器ID
     * @param int|null $authId 认证ID
     */
    public static function remove(int $serverId, ?int $authId = null): void
    {
        $poolKey = self::getPoolKey($serverId, $authId);

        if (isset(self::$pool[$serverId][$poolKey])) {
            $client = self::$pool[$serverId][$poolKey];
            $client->disconnect();
            unset(self::$pool[$serverId][$poolKey]);
        }
    }

    /**
     * 释放指定服务器的所有连接
     *
     * @param int $serverId 服务器ID
     */
    public static function releaseServer(int $serverId): void
    {
        if (!isset(self::$pool[$serverId])) {
            return;
        }

        foreach (self::$pool[$serverId] as $client) {
            $client->disconnect();
        }

        unset(self::$pool[$serverId]);
    }

    /**
     * 释放所有连接
     */
    public static function releaseAll(): void
    {
        foreach (self::$pool as $serverId => $connections) {
            foreach ($connections as $client) {
                $client->disconnect();
            }
        }

        self::$pool = [];
    }

    /**
     * 清理过期连接
     */
    public static function cleanup(): void
    {
        $now = time();

        foreach (self::$pool as $serverId => $connections) {
            foreach ($connections as $key => $client) {
                $idleTime = $client->getIdleTime();

                if ($idleTime >= self::$config['idle_timeout'] || !$client->isConnected()) {
                    $client->disconnect();
                    unset(self::$pool[$serverId][$key]);
                }
            }

            if (empty(self::$pool[$serverId])) {
                unset(self::$pool[$serverId]);
            }
        }
    }

    /**
     * 获取连接池状态
     *
     * @return array 连接池状态
     */
    public static function getStatus(): array
    {
        $status = [];
        $totalConnections = 0;

        foreach (self::$pool as $serverId => $connections) {
            $serverConnections = [];

            foreach ($connections as $key => $client) {
                $serverConnections[] = [
                    'key' => $key,
                    'idle_time' => $client->getIdleTime(),
                    'is_connected' => $client->isConnected(),
                ];
            }

            $status['servers'][$serverId] = [
                'connection_count' => count($connections),
                'connections' => $serverConnections,
            ];

            $totalConnections += count($connections);
        }

        $status['total_connections'] = $totalConnections;
        $status['max_connections'] = self::$config['max_connections'];
        $status['max_per_server'] = self::$config['max_per_server'];

        return $status;
    }

    /**
     * 获取连接池键
     *
     * @param int $serverId 服务器ID
     * @param int|null $authId 认证ID
     * @return string 连接池键
     */
    private static function getPoolKey(int $serverId, ?int $authId = null): string
    {
        return $authId ? "{$serverId}:{$authId}" : "{$serverId}:default";
    }

    /**
     * 清理指定服务器的最旧连接
     *
     * @param int $serverId 服务器ID
     */
    private static function cleanupOldestForServer(int $serverId): void
    {
        if (empty(self::$pool[$serverId])) {
            return;
        }

        $oldestKey = null;
        $oldestIdleTime = 0;

        foreach (self::$pool[$serverId] as $key => $client) {
            $idleTime = $client->getIdleTime();

            if ($oldestKey === null || $idleTime > $oldestIdleTime) {
                $oldestKey = $key;
                $oldestIdleTime = $idleTime;
            }
        }

        if ($oldestKey !== null) {
            $client = self::$pool[$serverId][$oldestKey];
            $client->disconnect();
            unset(self::$pool[$serverId][$oldestKey]);
        }
    }
}
