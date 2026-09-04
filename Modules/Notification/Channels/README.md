# Channels 目录

自定义通知渠道。

## 职责
- 实现通知发送渠道
- 对接第三方服务
- 处理渠道特定逻辑

## 内置渠道
- `sms`: 短信渠道
- `mail`: 邮件渠道
- `push`: 推送渠道
- `database`: 数据库渠道

## 命名规范
- PascalCase + Channel 后缀
- 示例: `SmsChannel.php`, `PushChannel.php`