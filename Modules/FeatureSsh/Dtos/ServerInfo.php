<?php

declare(strict_types=1);

namespace Modules\FeatureSsh\Dtos;

use Modules\FeatureSsh\Enums\SystemType;

/**
 * 服务器信息DTO
 *
 * @author AI开发团队
 * @date 2026-09-02
 */
class ServerInfo
{
    /**
     * 主机名
     */
    public readonly string $hostname;

    /**
     * 系统类型
     */
    public readonly SystemType $systemType;

    /**
     * 操作系统版本
     */
    public readonly string $osVersion;

    /**
     * 内核版本
     */
    public readonly string $kernel;

    /**
     * 系统架构
     */
    public readonly string $architecture;

    /**
     * CPU核心数
     */
    public readonly int $cpuCount;

    /**
     * CPU使用率
     */
    public readonly float $cpuUsage;

    /**
     * 总内存（MB）
     */
    public readonly int $memoryTotal;

    /**
     * 已用内存（MB）
     */
    public readonly int $memoryUsed;

    /**
     * 空闲内存（MB）
     */
    public readonly int $memoryFree;

    /**
     * 内存使用率
     */
    public readonly float $memoryUsage;

    /**
     * 总磁盘空间（GB）
     */
    public readonly int $diskTotal;

    /**
     * 已用磁盘空间（GB）
     */
    public readonly int $diskUsed;

    /**
     * 空闲磁盘空间（GB）
     */
    public readonly int $diskFree;

    /**
     * 磁盘使用率
     */
    public readonly float $diskUsage;

    /**
     * 1分钟平均负载
     */
    public readonly float $loadAverage1m;

    /**
     * 5分钟平均负载
     */
    public readonly float $loadAverage5m;

    /**
     * 15分钟平均负载
     */
    public readonly float $loadAverage15m;

    /**
     * 运行时间（秒）
     */
    public readonly int $uptimeSeconds;

    /**
     * 网络接口信息
     */
    public readonly array $networkInterfaces;

    /**
     * 构造函数
     *
     * @param string $hostname 主机名
     * @param SystemType $systemType 系统类型
     * @param string $osVersion 操作系统版本
     * @param string $kernel 内核版本
     * @param string $architecture 系统架构
     * @param int $cpuCount CPU核心数
     * @param float $cpuUsage CPU使用率
     * @param int $memoryTotal 总内存
     * @param int $memoryUsed 已用内存
     * @param int $memoryFree 空闲内存
     * @param float $memoryUsage 内存使用率
     * @param int $diskTotal 总磁盘空间
     * @param int $diskUsed 已用磁盘空间
     * @param int $diskFree 空闲磁盘空间
     * @param float $diskUsage 磁盘使用率
     * @param float $loadAverage1m 1分钟平均负载
     * @param float $loadAverage5m 5分钟平均负载
     * @param float $loadAverage15m 15分钟平均负载
     * @param int $uptimeSeconds 运行时间
     * @param array $networkInterfaces 网络接口
     */
    public function __construct(
        string $hostname = '',
        SystemType $systemType = SystemType::LINUX,
        string $osVersion = '',
        string $kernel = '',
        string $architecture = '',
        int $cpuCount = 0,
        float $cpuUsage = 0.0,
        int $memoryTotal = 0,
        int $memoryUsed = 0,
        int $memoryFree = 0,
        float $memoryUsage = 0.0,
        int $diskTotal = 0,
        int $diskUsed = 0,
        int $diskFree = 0,
        float $diskUsage = 0.0,
        float $loadAverage1m = 0.0,
        float $loadAverage5m = 0.0,
        float $loadAverage15m = 0.0,
        int $uptimeSeconds = 0,
        array $networkInterfaces = [],
    ) {
        $this->hostname = $hostname;
        $this->systemType = $systemType;
        $this->osVersion = $osVersion;
        $this->kernel = $kernel;
        $this->architecture = $architecture;
        $this->cpuCount = $cpuCount;
        $this->cpuUsage = $cpuUsage;
        $this->memoryTotal = $memoryTotal;
        $this->memoryUsed = $memoryUsed;
        $this->memoryFree = $memoryFree;
        $this->memoryUsage = $memoryUsage;
        $this->diskTotal = $diskTotal;
        $this->diskUsed = $diskUsed;
        $this->diskFree = $diskFree;
        $this->diskUsage = $diskUsage;
        $this->loadAverage1m = $loadAverage1m;
        $this->loadAverage5m = $loadAverage5m;
        $this->loadAverage15m = $loadAverage15m;
        $this->uptimeSeconds = $uptimeSeconds;
        $this->networkInterfaces = $networkInterfaces;
    }

    /**
     * 从命令输出行解析服务器信息
     *
     * @param array $data 数据数组
     * @return ServerInfo 服务器信息DTO
     */
    public static function fromCommandOutput(array $data): self
    {
        $systemType = match ($data['system_type'] ?? 'linux') {
            'windows' => SystemType::WINDOWS,
            'macos' => SystemType::MACOS,
            default => SystemType::LINUX,
        };

        return new self(
            $data['hostname'] ?? '',
            $systemType,
            $data['os_version'] ?? '',
            $data['kernel'] ?? '',
            $data['architecture'] ?? '',
            (int) ($data['cpu_count'] ?? 0),
            (float) ($data['cpu_usage'] ?? 0.0),
            (int) ($data['memory_total'] ?? 0),
            (int) ($data['memory_used'] ?? 0),
            (int) ($data['memory_free'] ?? 0),
            (float) ($data['memory_usage'] ?? 0.0),
            (int) ($data['disk_total'] ?? 0),
            (int) ($data['disk_used'] ?? 0),
            (int) ($data['disk_free'] ?? 0),
            (float) ($data['disk_usage'] ?? 0.0),
            (float) ($data['load_average_1m'] ?? 0.0),
            (float) ($data['load_average_5m'] ?? 0.0),
            (float) ($data['load_average_15m'] ?? 0.0),
            (int) ($data['uptime_seconds'] ?? 0),
            $data['network_interfaces'] ?? [],
        );
    }

    /**
     * 获取运行时间格式化字符串
     *
     * @return string 格式化后的运行时间
     */
    public function getUptimeFormatted(): string
    {
        $days = floor($this->uptimeSeconds / 86400);
        $hours = floor(($this->uptimeSeconds % 86400) / 3600);
        $minutes = floor(($this->uptimeSeconds % 3600) / 60);

        $parts = [];

        if ($days > 0) {
            $parts[] = $days . '天';
        }

        if ($hours > 0) {
            $parts[] = $hours . '小时';
        }

        if ($minutes > 0 || empty($parts)) {
            $parts[] = $minutes . '分钟';
        }

        return implode('', $parts);
    }

    /**
     * 获取内存使用率百分比
     *
     * @return int 内存使用率
     */
    public function getMemoryUsagePercent(): int
    {
        return (int) round($this->memoryUsage * 100);
    }

    /**
     * 获取磁盘使用率百分比
     *
     * @return int 磁盘使用率
     */
    public function getDiskUsagePercent(): int
    {
        return (int) round($this->diskUsage * 100);
    }

    /**
     * 获取CPU使用率百分比
     *
     * @return int CPU使用率
     */
    public function getCpuUsagePercent(): int
    {
        return (int) round($this->cpuUsage * 100);
    }

    /**
     * 转换为数组
     *
     * @return array 数组表示
     */
    public function toArray(): array
    {
        return [
            'hostname' => $this->hostname,
            'system_type' => $this->systemType->value,
            'system_type_label' => $this->systemType->label(),
            'os_version' => $this->osVersion,
            'kernel' => $this->kernel,
            'architecture' => $this->architecture,
            'cpu_count' => $this->cpuCount,
            'cpu_usage' => $this->cpuUsage,
            'cpu_usage_percent' => $this->getCpuUsagePercent(),
            'memory_total' => $this->memoryTotal,
            'memory_used' => $this->memoryUsed,
            'memory_free' => $this->memoryFree,
            'memory_usage' => $this->memoryUsage,
            'memory_usage_percent' => $this->getMemoryUsagePercent(),
            'disk_total' => $this->diskTotal,
            'disk_used' => $this->diskUsed,
            'disk_free' => $this->diskFree,
            'disk_usage' => $this->diskUsage,
            'disk_usage_percent' => $this->getDiskUsagePercent(),
            'load_average_1m' => $this->loadAverage1m,
            'load_average_5m' => $this->loadAverage5m,
            'load_average_15m' => $this->loadAverage15m,
            'uptime_seconds' => $this->uptimeSeconds,
            'uptime_formatted' => $this->getUptimeFormatted(),
            'network_interfaces' => $this->networkInterfaces,
        ];
    }
}
