<?php

namespace DLaravel\Queue;

use DLaravel\Helper\Logger;
use Illuminate\Contracts\Queue\ShouldQueue as LaravelShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

/**
 * 队列事件基类
 *
 * 为队列事件监听器提供统一的基础功能，包括：
 * - 队列配置管理
 * - 错误处理和日志记录
 * - 重试机制
 * - 事件处理生命周期管理
 */
abstract class ShouldQueue implements LaravelShouldQueue, ShouldQueueInterface
{
    use InteractsWithQueue;

    /**
     * 队列名称
     *
     * @var string|null
     */
    public $queue = null;

    /**
     * 最大重试次数
     *
     * @var int
     */
    public $tries = 3;

    /**
     * 任务超时时间（秒）
     *
     * @var int
     */
    public $timeout = 300;

    /**
     * 重试延迟时间（秒）
     *
     * @var int
     */
    public $retryAfter = 60;

    /**
     * 是否在模型缺失时删除任务
     *
     * @var bool
     */
    public $deleteWhenMissingModels = true;

    /**
     * 事件数据
     *
     * @var object|null
     */
    protected $event = null;

    /**
     * 创建事件监听器实例
     */
    public function __construct()
    {
        // 子类可以重写此方法进行初始化
    }

    /**
     * Laravel队列系统调用的handle方法
     *
     * 包含类似QueueJob::handle的逻辑，提供统一的执行流程
     *
     * @param  object  $event  事件对象
     */
    public function handle(object $event): void
    {
        $this->event = $event;

        $start = microtime(true);
        $queueName = $this->queue ?? 'default';
        $className = static::class;
        $payload = $this->getEventPayload();

        Helper::add_log('handle', $queueName, $className, $payload);

        $res = null;
        $diff = 0;

        try {
            $res = $this->run($event);
            $diff = microtime(true) - $start;

            if ($res) {
                // 事件处理成功，记录日志
                $this->logInfo('事件处理成功', [
                    'event_class' => get_class($event),
                    'execution_time' => $diff,
                ]);
            }
        } catch (\Throwable $exception) {
            $diff = microtime(true) - $start;
            $res = false;

            Logger::exception('queue_event', $exception);
            $desc = $exception->getMessage()."\n".$exception->getTraceAsString();
            Helper::add_log('Throwable-'.get_class($exception), $queueName, $className, $payload, $desc);

            // 记录异常日志
            $this->logError('事件处理异常', [
                'event_class' => get_class($event),
                'error' => $exception->getMessage(),
                'trace' => $exception->getTraceAsString(),
            ]);

            // 重新抛出异常以触发重试机制
            throw $exception;
        }

        // 统一在这里记录结束日志
        Helper::add_log('runend-'.($res ? 'true' : 'false'), $queueName, $className, $payload, '', $diff);
    }

    /**
     * 实际运行方法（实现ShouldQueueInterface）
     *
     * 子类必须实现此方法来处理具体的事件逻辑
     *
     * @param  object  $event  事件对象
     * @return bool 返回true表示处理成功，false表示处理失败
     */
    abstract public function run(object $event): bool;

    /**
     * 获取事件载荷数据
     *
     * 用于日志记录和调试
     */
    protected function getEventPayload(): array
    {
        if (! $this->event) {
            return [];
        }

        $payload = [
            'event_class' => get_class($this->event),
            'listener_class' => static::class,
            'queue' => $this->queue,
        ];

        // 尝试获取事件的数据
        if (method_exists($this->event, 'toArray')) {
            $payload['event_data'] = $this->event->toArray();
        } elseif (method_exists($this->event, 'getData')) {
            $payload['event_data'] = $this->event->getData();
        } else {
            // 获取事件的公共属性
            $payload['event_data'] = get_object_vars($this->event);
        }

        return $payload;
    }

    /**
     * 任务失败时的处理
     *
     * 当任务执行失败或达到最大重试次数时会调用此方法
     *
     * @param  \Throwable  $exception  导致任务失败的异常
     */
    public function failed(\Throwable $exception): void
    {
        $this->logError('队列事件处理失败', [
            'listener_class' => static::class,
            'error' => $exception->getMessage(),
            'trace' => $exception->getTraceAsString(),
            'attempts' => $this->attempts() ?? 0,
        ]);

        // 子类可以重写此方法实现自定义的失败处理逻辑
        $this->handleFailure($exception);
    }

    /**
     * 自定义失败处理逻辑
     *
     * 子类可以重写此方法实现特定的失败处理逻辑
     */
    protected function handleFailure(\Throwable $exception): void
    {
        // 默认不做任何处理，子类可以重写
    }

    /**
     * 获取重试延迟时间
     *
     * 支持指数退避策略
     */
    public function backoff(): int|array
    {
        $attempt = $this->attempts() ?? 1;

        // 指数退避：第1次60秒，第2次120秒，第3次240秒
        return min($this->retryAfter * pow(2, $attempt - 1), 3600); // 最大1小时
    }

    /**
     * 记录信息日志
     *
     * @param  string  $message  日志消息
     * @param  array  $context  上下文数据
     */
    protected function logInfo(string $message, array $context = []): void
    {
        $context['listener_class'] = static::class;
        $context['queue'] = $this->queue;

        Log::info($message, $context);
        Logger::info('QueueEvent', $message, $context);
    }

    /**
     * 记录错误日志
     *
     * @param  string  $message  日志消息
     * @param  array  $context  上下文数据
     */
    protected function logError(string $message, array $context = []): void
    {
        $context['listener_class'] = static::class;
        $context['queue'] = $this->queue;

        Log::error($message, $context);
        Logger::error('QueueEvent', $message, $context);
    }

    /**
     * 记录警告日志
     *
     * @param  string  $message  日志消息
     * @param  array  $context  上下文数据
     */
    protected function logWarning(string $message, array $context = []): void
    {
        $context['listener_class'] = static::class;
        $context['queue'] = $this->queue;

        Log::warning($message, $context);
        Logger::warning('QueueEvent', $message, $context);
    }

    /**
     * 安全执行事件处理
     *
     * 提供统一的异常处理和日志记录
     * 注意：此方法主要用于向后兼容，建议直接在handleEvent中实现逻辑
     *
     * @param  object  $event  事件对象
     * @param  callable  $handler  处理函数
     */
    protected function safeHandle(object $event, callable $handler): void
    {
        try {
            $this->logInfo('开始处理队列事件', [
                'event_class' => get_class($event),
                'event_data' => method_exists($event, 'toArray') ? $event->toArray() : [],
            ]);

            $handler($event);

            $this->logInfo('队列事件处理完成', [
                'event_class' => get_class($event),
            ]);

        } catch (\Throwable $exception) {
            $this->logError('队列事件处理异常', [
                'event_class' => get_class($event),
                'error' => $exception->getMessage(),
                'trace' => $exception->getTraceAsString(),
            ]);

            // 重新抛出异常以触发重试机制
            throw $exception;
        }
    }

    /**
     * 获取事件的唯一标识
     *
     * 用于去重或追踪
     */
    protected function getEventId(object $event): string
    {
        $eventClass = get_class($event);

        // 尝试获取事件的ID属性
        if (property_exists($event, 'id')) {
            return $eventClass.':'.$event->id;
        }

        // 尝试获取事件的其他标识属性
        foreach (['uuid', 'key', 'identifier'] as $property) {
            if (property_exists($event, $property)) {
                return $eventClass.':'.$event->$property;
            }
        }

        // 如果没有找到标识属性，使用类名和时间戳
        return $eventClass.':'.microtime(true);
    }

    /**
     * 检查事件是否应该被处理
     *
     * 子类可以重写此方法实现自定义的过滤逻辑
     */
    protected function shouldHandle(object $event): bool
    {
        return true;
    }

    /**
     * 获取任务标签
     *
     * 用于监控和调试
     */
    public function tags(): array
    {
        return [
            'listener:'.class_basename(static::class),
            'queue:'.($this->queue ?? 'default'),
        ];
    }
}
