# 通知模块文档 Notification

## 模块说明
通知模块是一个统一的消息通知系统，整合了短信、邮件、推送等多个通知渠道，提供统一的通知接口。

- 数据表前缀`notification_`

## 目录结构
```
app/Module/Notification/
├── Controllers/          # 控制器目录
├── Models/              # 模型目录
├── Repositorys/         # 仓库目录
├── Services/            # 服务目录
├── Validators/          # 验证器目录
├── Validations/         # 验证规则目录
├── Queues/              # 队列目录
├── Enums/               # 枚举目录
└── Commands/            # 命令目录
```

## 数据表设计

### 通知模板表 (notification_templates)
| 字段名 | 类型 | 说明 |
|--------|------|------|
| id | bigint | 主键ID |
| name | varchar(100) | 模板名称 |
| code | varchar(50) | 模板代码 |
| title | varchar(255) | 通知标题 |
| content | text | 通知内容 |
| variables | json | 变量定义 |
| channels | json | 通知渠道配置 |
| status | tinyint | 状态：0禁用 1启用 |
| created_at | timestamp | 创建时间 |
| updated_at | timestamp | 更新时间 |

### 通知记录表 (notification_logs)
| 字段名 | 类型 | 说明 |
|--------|------|------|
| id | bigint | 主键ID |
| template_id | bigint | 模板ID |
| user_id | bigint | 用户ID |
| channels | json | 通知渠道 |
| title | varchar(255) | 通知标题 |
| content | text | 通知内容 |
| data | json | 通知数据 |
| status | varchar(20) | 状态 |
| message | varchar(255) | 发送结果 |
| sent_at | timestamp | 发送时间 |
| created_at | timestamp | 创建时间 |
| updated_at | timestamp | 更新时间 |

## 枚举定义

### 通知状态 (NotificationStatus)
- PENDING: 待发送
- SENDING: 发送中
- SENT: 已发送
- FAILED: 发送失败

### 通知渠道 (NotificationChannel)
- SMS: 短信
- MAIL: 邮件
- PUSH: 推送

## 核心服务

### NotificationService
通知核心服务，负责通知的发送、模板管理等功能。

主要方法：
- send(array $data): 发送通知
- sendBatch(array $data): 批量发送通知
- getTemplate(int $id): 获取模板
- createTemplate(array $data): 创建模板
- updateTemplate(int $id, array $data): 更新模板
- deleteTemplate(int $id): 删除模板

## 依赖关系

### 模块依赖
通知模块依赖以下模块：
- 短信模块 (Sms)
- 邮件模块 (Mail)
- 推送模块 (Push)

### 服务依赖
通知服务通过以下服务发送通知：
- SmsService: 发送短信
- MailService: 发送邮件
- PushService: 发送推送

## 使用示例

```php
// 发送通知
$notificationService = new NotificationService();
$notificationService->send([
    'template_id' => 1,
    'user_id' => 1,
    'channels' => [
        NotificationChannel::SMS,
        NotificationChannel::MAIL,
        NotificationChannel::PUSH
    ],
    'data' => [
        'order_id' => '123456',
        'amount' => 100
    ]
]);

// 批量发送通知
$notificationService->sendBatch([
    [
        'template_id' => 1,
        'user_id' => 1,
        'channels' => [NotificationChannel::SMS],
        'data' => ['order_id' => '123456']
    ],
    [
        'template_id' => 1,
        'user_id' => 2,
        'channels' => [NotificationChannel::MAIL],
        'data' => ['order_id' => '654321']
    ]
]);
```

## 注意事项
1. 通知发送采用队列异步处理
2. 支持失败重试机制
3. 支持变量替换
4. 支持多渠道组合
5. 支持发送频率限制
6. 支持黑名单
7. 支持发送统计
8. 支持渠道优先级
9. 支持渠道降级策略
10. 支持渠道熔断机制 
