<?php

namespace Modules\DcatAdmin\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * 管理员操作事件
 */
class AdminActionEvent
{
    use Dispatchable, SerializesModels;

    public array $actionData;

    /**
     * 创建新的事件实例
     */
    public function __construct(array $actionData)
    {
        $this->actionData = $actionData;
    }

    /**
     * 获取操作数据
     */
    public function getActionData(): array
    {
        return $this->actionData;
    }

    /**
     * 获取管理员ID
     */
    public function getAdminId(): ?int
    {
        return $this->actionData['admin_id'] ?? null;
    }

    /**
     * 获取管理员名称
     */
    public function getAdminName(): string
    {
        return $this->actionData['admin_name'] ?? 'Unknown';
    }

    /**
     * 获取操作类型
     */
    public function getActionType(): string
    {
        return $this->actionData['action_type'] ?? '';
    }

    /**
     * 获取操作描述
     */
    public function getDescription(): string
    {
        return $this->actionData['description'] ?? '';
    }

    /**
     * 获取操作数据
     */
    public function getData(): array
    {
        return $this->actionData['data'] ?? [];
    }

    /**
     * 获取IP地址
     */
    public function getIpAddress(): string
    {
        return $this->actionData['ip_address'] ?? '';
    }

    /**
     * 获取用户代理
     */
    public function getUserAgent(): string
    {
        return $this->actionData['user_agent'] ?? '';
    }

    /**
     * 获取时间戳
     */
    public function getTimestamp(): ?\Carbon\Carbon
    {
        return $this->actionData['timestamp'] ?? null;
    }
}
