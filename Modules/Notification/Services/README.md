# 通知模块服务类文档

## 服务类说明
本目录包含通知模块的所有服务类，负责处理通知的发送、模板管理等功能。

## 服务类列表

### 1. NotificationService
通知核心服务类，负责通知的发送、批量发送和重试等功能。

#### 主要方法

##### send(array $data)
发送单个通知

参数说明：
```php
$data = [
    'template_id' => int,      // 模板ID
    'user_id' => int,          // 用户ID
    'data' => array,           // 模板变量数据
    'priority' => int,         // 优先级（可选）
    'channel' => string        // 通知渠道（可选）
];
```

返回值：
```php
[
    'success' => bool,         // 是否成功
    'message' => string,       // 提示信息
    'notification_id' => int   // 通知ID
]
```

##### sendBatch(array $data)
批量发送通知

参数说明：
```php
$data = [
    [
        'template_id' => int,
        'user_id' => int,
        'data' => array,
        'priority' => int,
        'channel' => string
    ],
    // ... 更多通知
];
```

返回值：
```php
[
    'success' => bool,
    'message' => string,
    'total' => int,           // 总数
    'success_count' => int,   // 成功数
    'fail_count' => int       // 失败数
]
```

##### retry(int $notificationId)
重试失败的通知

参数说明：
- $notificationId: 通知ID

返回值：
```php
[
    'success' => bool,
    'message' => string
]
```

### 2. MailService
邮件服务类，负责邮件通知的发送。

#### 主要方法

##### send(array $data)
发送邮件通知

参数说明：
```php
$data = [
    'to' => string,           // 收件人邮箱
    'subject' => string,      // 邮件主题
    'content' => string,      // 邮件内容
    'template' => string,     // 邮件模板（可选）
    'data' => array          // 模板变量数据（可选）
];
```

返回值：
```php
[
    'success' => bool,
    'message' => string
]
```

### 3. SmsService
短信服务类，负责短信通知的发送。

#### 主要方法

##### send(array $data)
发送短信通知

参数说明：
```php
$data = [
    'phone' => string,        // 手机号
    'content' => string,      // 短信内容
    'template_id' => string,  // 短信模板ID（可选）
    'data' => array          // 模板变量数据（可选）
];
```

返回值：
```php
[
    'success' => bool,
    'message' => string,
    'request_id' => string    // 短信请求ID
]
```

### 4. PushService
推送服务类，负责手机推送通知的发送。

#### 主要方法

##### send(array $data)
发送推送通知

参数说明：
```php
$data = [
    'device_token' => string, // 设备令牌
    'title' => string,        // 推送标题
    'content' => string,      // 推送内容
    'data' => array,         // 额外数据（可选）
    'sound' => string,       // 提示音（可选）
    'badge' => int          // 角标数（可选）
];
```

返回值：
```php
[
    'success' => bool,
    'message' => string,
    'message_id' => string   // 推送消息ID
]
```

## 使用示例

### 发送通知
```php
$notificationService = new NotificationService();
$result = $notificationService->send([
    'template_id' => 1,
    'user_id' => 1,
    'data' => [
        'name' => '张三',
        'amount' => 100
    ],
    'priority' => NotificationPriority::HIGH
]);
```

### 发送邮件
```php
$mailService = new MailService();
$result = $mailService->send([
    'to' => 'example@example.com',
    'subject' => '测试邮件',
    'content' => '这是一封测试邮件',
    'template' => 'emails.test',
    'data' => [
        'name' => '张三'
    ]
]);
```

### 发送短信
```php
$smsService = new SmsService();
$result = $smsService->send([
    'phone' => '13800138000',
    'content' => '您的验证码是：123456',
    'template_id' => 'SMS_123456789',
    'data' => [
        'code' => '123456'
    ]
]);
```

### 发送推送
```php
$pushService = new PushService();
$result = $pushService->send([
    'device_token' => 'device_token_123',
    'title' => '测试推送',
    'content' => '这是一条测试推送',
    'data' => [
        'type' => 'test'
    ],
    'sound' => 'default',
    'badge' => 1
]);
```

## 注意事项
1. 所有服务类都实现了错误处理和日志记录
2. 支持异步发送和失败重试
3. 支持发送频率限制
4. 支持发送黑名单
5. 支持发送统计
6. 支持模板变量替换
7. 支持多语言 
