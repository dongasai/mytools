<?php

declare(strict_types=1);

namespace Modules\ABase\Hooks\Management;

/**
 * Hook处理器代理类
 *
 * 用于包装处理器信息，提供统一的访问接口
 */
class HookHandlerProxy
{
    /**
     * 处理器名称
     */
    private string $handlerName;

    /**
     * 优先级
     */
    private int $priority;

    /**
     * 类型
     */
    private string $type;

    /**
     * 构造函数
     *
     * @param  string  $handlerName  处理器名称
     * @param  int  $priority  优先级
     * @param  string  $type  类型（static/callable/closure）
     */
    public function __construct(string $handlerName, int $priority, string $type)
    {
        $this->handlerName = $handlerName;
        $this->priority = $priority;
        $this->type = $type;
    }

    /**
     * 获取处理器优先级
     *
     * @return int 优先级数值
     */
    public function getPriority(): int
    {
        return $this->priority;
    }

    /**
     * 获取处理器描述
     *
     * @return string 处理器描述信息
     */
    public function getDescription(): string
    {
        $typeLabel = match ($this->type) {
            'static' => '静态处理器',
            'callable' => '可调用处理器',
            'closure' => '闭包处理器',
            default => '未知类型'
        };

        return "{$typeLabel}: {$this->handlerName}";
    }

    /**
     * 获取处理器名称
     *
     * @return string 处理器名称
     */
    public function getHandlerName(): string
    {
        return $this->handlerName;
    }

    /**
     * 获取处理器类型
     *
     * @return string 处理器类型
     */
    public function getType(): string
    {
        return $this->type;
    }
}