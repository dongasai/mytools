<?php

namespace Modules\ABase\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\Log;

/**
 * 模块事件统一转发器基类
 *
 * 一个模块一个转发器，通过eventMap配置模块内Event到跨模块Event的映射
 * 异步执行，队列名称为event
 *
 * @package Modules\ABase\Listeners
 */
abstract class ModuleDispatcher implements ShouldQueue
{
    use Queueable;

    /**
     * 最大尝试次数
     */
    public int $tries = 3;

    /**
     * 超时时间（秒）
     */
    public int $timeout = 30;

    /**
     * 创建实例
     */
    public function __construct()
    {
        // 设置队列名称
        $this->onQueue('event');
    }

    /**
     * 事件映射配置
     *
     * 格式：
     * [
     *     Module内Event::class => [
     *         'target' => 跨模块Event::class,
     *         'fields' => ['field1', 'field2'],  // 要传递的字段
     *         'condition' => 'methodName',        // 可选，条件验证方法名
     *     ],
     * ]
     *
     * @var array
     */
    protected array $eventMap = [];

    /**
     * 处理事件
     *
     * @param object $event 模块内事件
     */
    public function handle(object $event): void
    {
        $eventClass = get_class($event);

        if (!isset($this->eventMap[$eventClass])) {
            return;
        }

        $config = $this->eventMap[$eventClass];

        // 条件验证
        if (isset($config['condition']) && method_exists($event, $config['condition'])) {
            if (!$event->{$config['condition']}()) {
                Log::debug('ModuleDispatcher: 条件不满足，跳过转发', [
                    'event' => $eventClass,
                    'condition' => $config['condition'],
                ]);
                return;
            }
        }

        // 提取参数
        $payload = [];
        foreach ($config['fields'] as $field) {
            if (property_exists($event, $field)) {
                $payload[$field] = $event->{$field};
            }
        }

        // 转发跨模块Event
        $config['target']::dispatch(...array_values($payload));

        Log::debug('ModuleDispatcher: 事件转发成功', [
            'source' => $eventClass,
            'target' => $config['target'],
            'fields' => $config['fields'],
        ]);
    }

    /**
     * 处理失败
     *
     * @param object $event
     * @param \Throwable $exception
     */
    public function failed(object $event, \Throwable $exception): void
    {
        Log::error('ModuleDispatcher: 事件转发失败', [
            'event' => get_class($event),
            'error' => $exception->getMessage(),
            'trace' => $exception->getTraceAsString(),
        ]);
    }
}
