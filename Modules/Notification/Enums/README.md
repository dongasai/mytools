# 通知模块枚举类文档

## 枚举类说明
本目录包含通知模块的所有枚举类，用于定义通知类型、渠道、优先级和状态等常量。

## 枚举类列表

### 1. NotificationType
通知类型枚举

```php
class NotificationType extends BaseEnum
{
    /**
     * 系统通知
     */
    const SYSTEM = 'system';

    /**
     * 用户通知
     */
    const USER = 'user';

    /**
     * 业务通知
     */
    const BUSINESS = 'business';

    /**
     * 安全通知
     */
    const SECURITY = 'security';

    /**
     * 获取类型列表
     *
     * @return array
     */
    public static function getList()
    {
        return [
            self::SYSTEM => '系统通知',
            self::USER => '用户通知',
            self::BUSINESS => '业务通知',
            self::SECURITY => '安全通知'
        ];
    }
}
```

### 2. NotificationChannel
通知渠道枚举

```php
class NotificationChannel extends BaseEnum
{
    /**
     * 站内信
     */
    const DATABASE = 'database';

    /**
     * 邮件
     */
    const MAIL = 'mail';

    /**
     * 短信
     */
    const SMS = 'sms';

    /**
     * WebSocket
     */
    const WEBSOCKET = 'websocket';

    /**
     * 手机推送
     */
    const PUSH = 'push';

    /**
     * 获取渠道列表
     *
     * @return array
     */
    public static function getList()
    {
        return [
            self::DATABASE => '站内信',
            self::MAIL => '邮件',
            self::SMS => '短信',
            self::WEBSOCKET => 'WebSocket',
            self::PUSH => '手机推送'
        ];
    }
}
```

### 3. NotificationPriority
通知优先级枚举

```php
class NotificationPriority extends BaseEnum
{
    /**
     * 低优先级
     */
    const LOW = 1;

    /**
     * 普通优先级
     */
    const NORMAL = 2;

    /**
     * 高优先级
     */
    const HIGH = 3;

    /**
     * 紧急优先级
     */
    const URGENT = 4;

    /**
     * 获取优先级列表
     *
     * @return array
     */
    public static function getList()
    {
        return [
            self::LOW => '低优先级',
            self::NORMAL => '普通优先级',
            self::HIGH => '高优先级',
            self::URGENT => '紧急优先级'
        ];
    }
}
```

### 4. NotificationStatus
通知状态枚举

```php
class NotificationStatus extends BaseEnum
{
    /**
     * 待发送
     */
    const PENDING = 'pending';

    /**
     * 发送中
     */
    const SENDING = 'sending';

    /**
     * 已发送
     */
    const SENT = 'sent';

    /**
     * 发送失败
     */
    const FAILED = 'failed';

    /**
     * 已读
     */
    const READ = 'read';

    /**
     * 获取状态列表
     *
     * @return array
     */
    public static function getList()
    {
        return [
            self::PENDING => '待发送',
            self::SENDING => '发送中',
            self::SENT => '已发送',
            self::FAILED => '发送失败',
            self::READ => '已读'
        ];
    }
}
```

## 使用示例

### 获取通知类型列表
```php
$types = NotificationType::getList();
// 输出：
// [
//     'system' => '系统通知',
//     'user' => '用户通知',
//     'business' => '业务通知',
//     'security' => '安全通知'
// ]
```

### 获取通知渠道列表
```php
$channels = NotificationChannel::getList();
// 输出：
// [
//     'database' => '站内信',
//     'mail' => '邮件',
//     'sms' => '短信',
//     'websocket' => 'WebSocket',
//     'push' => '手机推送'
// ]
```

### 获取通知优先级列表
```php
$priorities = NotificationPriority::getList();
// 输出：
// [
//     1 => '低优先级',
//     2 => '普通优先级',
//     3 => '高优先级',
//     4 => '紧急优先级'
// ]
```

### 获取通知状态列表
```php
$statuses = NotificationStatus::getList();
// 输出：
// [
//     'pending' => '待发送',
//     'sending' => '发送中',
//     'sent' => '已发送',
//     'failed' => '发送失败',
//     'read' => '已读'
// ]
```

### 在代码中使用枚举值
```php
// 设置通知类型
$notification->type = NotificationType::SYSTEM;

// 设置通知渠道
$notification->channel = NotificationChannel::MAIL;

// 设置通知优先级
$notification->priority = NotificationPriority::HIGH;

// 设置通知状态
$notification->status = NotificationStatus::PENDING;
```

## 注意事项
1. 所有枚举类都继承自BaseEnum基类
2. 每个枚举类都实现了getList()方法，用于获取枚举值的列表
3. 枚举值使用常量定义，便于维护和使用
4. 支持多语言，可以根据需要修改getList()方法返回的文本
5. 枚举值在数据库中使用字符串或整数存储
6. 建议在代码中使用枚举常量而不是直接使用字符串或数字 
