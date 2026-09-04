# 通知模块队列类文档

## 队列类说明
本目录包含通知模块的所有队列类，用于处理通知的异步发送和失败重试等功能。

## 队列类列表

### 1. SendNotificationQueue
通知发送队列，负责处理通知的异步发送。

```php
class SendNotificationQueue extends BaseQueue implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * 通知ID
     *
     * @var int
     */
    protected $notificationId;

    /**
     * 构造函数
     *
     * @param int $notificationId
     */
    public function __construct(int $notificationId)
    {
        $this->notificationId = $notificationId;
    }

    /**
     * 处理队列任务
     */
    public function handle()
    {
        try {
            // 1. 获取通知信息
            $notification = Notification::find($this->notificationId);
            if (!$notification) {
                throw new \Exception('通知不存在');
            }

            // 2. 更新状态为发送中
            $notification->status = NotificationStatus::SENDING;
            $notification->save();

            // 3. 根据渠道选择对应的服务
            $service = $this->getService($notification->channel);

            // 4. 发送通知
            $result = $service->send([
                'notification_id' => $notification->id,
                'template_id' => $notification->template_id,
                'data' => $notification->data
            ]);

            // 5. 更新状态为已发送
            $notification->status = NotificationStatus::SENT;
            $notification->sent_at = now();
            $notification->save();

        } catch (\Exception $e) {
            // 6. 处理失败情况
            $this->handleFailure($notification, $e);
        }
    }

    /**
     * 获取对应的服务实例
     *
     * @param string $channel
     * @return mixed
     */
    protected function getService(string $channel)
    {
        switch ($channel) {
            case NotificationChannel::MAIL:
                return new MailService();
            case NotificationChannel::SMS:
                return new SmsService();
            case NotificationChannel::PUSH:
                return new PushService();
            case NotificationChannel::DATABASE:
                return new DatabaseService();
            case NotificationChannel::WEBSOCKET:
                return new WebSocketService();
            default:
                throw new \Exception('不支持的渠道类型');
        }
    }

    /**
     * 处理失败情况
     *
     * @param Notification $notification
     * @param \Exception $e
     */
    protected function handleFailure($notification, $e)
    {
        // 1. 记录错误日志
        \Log::error('通知发送失败', [
            'notification_id' => $notification->id,
            'error' => $e->getMessage()
        ]);

        // 2. 更新重试次数
        $notification->retry_count++;
        $notification->status = NotificationStatus::FAILED;
        $notification->save();

        // 3. 如果重试次数未超过限制，重新加入队列
        if ($notification->retry_count < config('notification.max_retry_count')) {
            $this->release(config('notification.retry_delay'));
        } else {
            // 4. 超过重试次数，加入失败队列
            RetryNotificationQueue::dispatch($notification->id);
        }
    }
}
```

### 2. RetryNotificationQueue
通知重试队列，负责处理失败通知的重试。

```php
class RetryNotificationQueue extends BaseQueue implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * 通知ID
     *
     * @var int
     */
    protected $notificationId;

    /**
     * 构造函数
     *
     * @param int $notificationId
     */
    public function __construct(int $notificationId)
    {
        $this->notificationId = $notificationId;
    }

    /**
     * 处理队列任务
     */
    public function handle()
    {
        try {
            // 1. 获取通知信息
            $notification = Notification::find($this->notificationId);
            if (!$notification) {
                throw new \Exception('通知不存在');
            }

            // 2. 重置状态和重试次数
            $notification->status = NotificationStatus::PENDING;
            $notification->retry_count = 0;
            $notification->save();

            // 3. 重新加入发送队列
            SendNotificationQueue::dispatch($notification->id);

        } catch (\Exception $e) {
            \Log::error('通知重试失败', [
                'notification_id' => $this->notificationId,
                'error' => $e->getMessage()
            ]);
        }
    }
}
```

## 使用示例

### 发送通知
```php
// 创建通知记录
$notification = Notification::create([
    'template_id' => 1,
    'type' => NotificationType::SYSTEM,
    'channel' => NotificationChannel::MAIL,
    'title' => '测试通知',
    'content' => '这是一条测试通知',
    'data' => ['name' => '张三'],
    'priority' => NotificationPriority::NORMAL,
    'status' => NotificationStatus::PENDING
]);

// 加入发送队列
SendNotificationQueue::dispatch($notification->id);
```

### 重试失败通知
```php
// 重试失败的通知
RetryNotificationQueue::dispatch($notificationId);
```

## 队列配置

### 配置文件
```php
// config/notification.php
return [
    // 最大重试次数
    'max_retry_count' => 3,

    // 重试延迟（秒）
    'retry_delay' => 60,

    // 队列名称
    'queue' => 'notifications',

    // 失败队列名称
    'failed_queue' => 'notifications-failed',

    // 队列超时时间（秒）
    'timeout' => 60,

    // 队列最大尝试次数
    'tries' => 3
];
```

## 注意事项
1. 所有队列类都继承自BaseQueue基类
2. 使用Laravel的队列特性，支持多种队列驱动
3. 实现了失败重试机制
4. 支持队列超时和最大尝试次数限制
5. 支持队列延迟
6. 支持队列优先级
7. 支持队列批处理
8. 支持队列监控和统计
9. 支持队列日志记录
10. 支持队列错误处理 
