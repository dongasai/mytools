<?php

namespace Modules\Application\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Modules\Application\Models\SystemLog;

/**
 * 系统日志创建事件
 */
class SystemLogCreatedEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * 日志ID
     */
    public int $id;

    /**
     * 日志类型
     */
    public string $type;

    /**
     * 日志级别
     */
    public string $level;

    /**
     * 日志消息
     */
    public string $message;

    /**
     * 日志上下文
     */
    public array $context;

    /**
     * 用户ID
     */
    public ?int $userId;

    /**
     * 系统日志对象
     */
    public SystemLog $log;

    /**
     * 创建一个新的事件实例
     *
     * @return void
     */
    public function __construct(SystemLog $log)
    {
        $this->id = $log->id;
        $this->type = $log->type;
        $this->level = $log->level;
        $this->message = $log->message;
        $this->context = $log->context ?? [];
        $this->userId = $log->user_id;
        $this->log = $log;
    }
}
