# 通知模块命令类文档

## 命令类说明
本目录包含通知模块的所有命令类，用于处理通知相关的命令行任务。

## 命令类列表

### 1. SendNotificationCommand
发送通知命令，用于手动发送通知。

```php
class SendNotificationCommand extends Command
{
    /**
     * 命令名称
     *
     * @var string
     */
    protected $signature = 'notification:send
                          {--template= : 通知模板ID}
                          {--type= : 通知类型}
                          {--channel= : 通知渠道}
                          {--title= : 通知标题}
                          {--content= : 通知内容}
                          {--data= : 通知数据(JSON)}
                          {--priority= : 优先级}
                          {--to= : 接收者(邮箱/手机号/设备令牌)}
                          {--user= : 用户ID}';

    /**
     * 命令描述
     *
     * @var string
     */
    protected $description = '发送通知';

    /**
     * 通知服务
     *
     * @var NotificationService
     */
    protected $notificationService;

    /**
     * 构造函数
     *
     * @param NotificationService $notificationService
     */
    public function __construct(NotificationService $notificationService)
    {
        parent::__construct();
        $this->notificationService = $notificationService;
    }

    /**
     * 执行命令
     */
    public function handle()
    {
        try {
            // 1. 获取参数
            $data = $this->getData();

            // 2. 验证参数
            if (!$this->validateData($data)) {
                return;
            }

            // 3. 发送通知
            $result = $this->notificationService->send($data);

            // 4. 输出结果
            if ($result['success']) {
                $this->info('通知发送成功');
                $this->info('通知ID: ' . $result['notification_id']);
            } else {
                $this->error('通知发送失败: ' . $result['message']);
            }

        } catch (\Exception $e) {
            $this->error('命令执行失败: ' . $e->getMessage());
        }
    }

    /**
     * 获取参数数据
     *
     * @return array
     */
    protected function getData()
    {
        $data = [
            'template_id' => $this->option('template'),
            'type' => $this->option('type'),
            'channel' => $this->option('channel'),
            'title' => $this->option('title'),
            'content' => $this->option('content'),
            'priority' => $this->option('priority'),
            'user_id' => $this->option('user')
        ];

        // 处理通知数据
        if ($this->option('data')) {
            $data['data'] = json_decode($this->option('data'), true);
        }

        // 处理接收者
        $to = $this->option('to');
        if ($to) {
            switch ($data['channel']) {
                case NotificationChannel::MAIL:
                    $data['to'] = $to;
                    break;
                case NotificationChannel::SMS:
                    $data['phone'] = $to;
                    break;
                case NotificationChannel::PUSH:
                    $data['device_token'] = $to;
                    break;
            }
        }

        return $data;
    }

    /**
     * 验证参数数据
     *
     * @param array $data
     * @return bool
     */
    protected function validateData(array $data)
    {
        $validator = new NotificationValidator();

        if (!$validator->validate($data)) {
            $this->error('参数验证失败:');
            foreach ($validator->getErrors() as $field => $errors) {
                $this->error("{$field}: " . implode(', ', $errors));
            }
            return false;
        }

        return true;
    }
}
```

### 2. RetryNotificationCommand
重试通知命令，用于重试失败的通知。

```php
class RetryNotificationCommand extends Command
{
    /**
     * 命令名称
     *
     * @var string
     */
    protected $signature = 'notification:retry
                          {--id= : 通知ID}
                          {--all : 重试所有失败的通知}';

    /**
     * 命令描述
     *
     * @var string
     */
    protected $description = '重试失败的通知';

    /**
     * 通知服务
     *
     * @var NotificationService
     */
    protected $notificationService;

    /**
     * 构造函数
     *
     * @param NotificationService $notificationService
     */
    public function __construct(NotificationService $notificationService)
    {
        parent::__construct();
        $this->notificationService = $notificationService;
    }

    /**
     * 执行命令
     */
    public function handle()
    {
        try {
            if ($this->option('all')) {
                $this->retryAll();
            } else {
                $this->retryOne();
            }
        } catch (\Exception $e) {
            $this->error('命令执行失败: ' . $e->getMessage());
        }
    }

    /**
     * 重试单个通知
     */
    protected function retryOne()
    {
        $id = $this->option('id');
        if (!$id) {
            $this->error('请指定通知ID');
            return;
        }

        $result = $this->notificationService->retry($id);
        if ($result['success']) {
            $this->info('通知重试成功');
        } else {
            $this->error('通知重试失败: ' . $result['message']);
        }
    }

    /**
     * 重试所有失败的通知
     */
    protected function retryAll()
    {
        $notifications = Notification::where('status', NotificationStatus::FAILED)
            ->where('retry_count', '<', config('notification.max_retry_count'))
            ->get();

        $total = $notifications->count();
        $success = 0;
        $fail = 0;

        $this->info("找到 {$total} 个失败通知");

        foreach ($notifications as $notification) {
            $result = $this->notificationService->retry($notification->id);
            if ($result['success']) {
                $success++;
            } else {
                $fail++;
            }
        }

        $this->info("重试完成: 成功 {$success} 个, 失败 {$fail} 个");
    }
}
```

## 使用示例

### 发送通知
```bash
# 发送邮件通知
php artisan notification:send \
    --template=1 \
    --type=system \
    --channel=mail \
    --title="测试通知" \
    --content="这是一条测试通知" \
    --data='{"name":"张三","amount":100}' \
    --priority=2 \
    --to=example@example.com \
    --user=1

# 发送短信通知
php artisan notification:send \
    --template=2 \
    --type=user \
    --channel=sms \
    --title="验证码" \
    --content="您的验证码是：123456" \
    --data='{"code":"123456"}' \
    --to=13800138000

# 发送推送通知
php artisan notification:send \
    --template=3 \
    --type=business \
    --channel=push \
    --title="订单通知" \
    --content="您的订单已发货" \
    --data='{"order_id":"123456"}' \
    --to=device_token_123
```

### 重试通知
```bash
# 重试单个通知
php artisan notification:retry --id=1

# 重试所有失败的通知
php artisan notification:retry --all
```

## 注意事项
1. 所有命令类都继承自Laravel的Command基类
2. 支持命令行参数和选项
3. 支持参数验证
4. 支持错误处理
5. 支持进度显示
6. 支持批量处理
7. 支持异步处理
8. 支持日志记录
9. 支持命令别名
10. 支持命令描述和帮助信息 
