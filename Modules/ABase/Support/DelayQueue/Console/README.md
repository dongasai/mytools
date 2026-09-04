# 延迟队列控制台命令

## 概述

DelayQueue 控制台模块提供了延迟队列的管理和监控命令，主要用于队列的定时执行和状态监控。

## 命令列表

### DelayQueueRun 命令

**命令签名**: `app-delayqueue:run`
**描述**: 延迟队列执行模块
**继承**: `DLaravel\Console\CommandSecond`

#### 配置参数

| 参数 | 值 | 说明 |
|------|----|----|
| `waitSecond` | 3 | 等待时间（秒） |
| `sleepSecond` | 2 | 间隔时长（秒） |
| `signature` | `app-delayqueue:run` | 命令签名 |
| `description` | 延迟队列-执行模块 | 命令描述 |

#### 使用方法

```bash
# 运行延迟队列命令
php artisan app-delayqueue:run

# 后台运行
nohup php artisan app-delayqueue:run > /dev/null 2>&1 &
```

#### 实现逻辑

```php
public function handleSecond($s)
{
    Logger::info("");
    return true;
}
```

**注意**: 当前实现为空逻辑，主要用于框架集成和未来扩展。

## 扩展建议

### 监控命令

可以扩展以下监控功能：

```php
// 监控 Redis 键数量
public function monitorRedisKeys()
{
    $redis = \Illuminate\Support\Facades\Redis::client();
    $keys = $redis->keys('delay_queue*');
    $this->info('当前延迟队列任务数: ' . count($keys));
}

// 监控队列状态
public function monitorQueueStatus()
{
    $queueSize = Queue::size();
    $this->info('队列大小: ' . $queueSize);
}
```

### 清理命令

```php
// 清理过期的 Redis 键
public function cleanExpiredKeys()
{
    $redis = \Illuminate\Support\Facades\Redis::client();
    $keys = $redis->keys('delay_queue*');

    $cleaned = 0;
    foreach ($keys as $key) {
        if ($redis->ttl($key) <= 0) {
            $redis->del($key);
            $cleaned++;
        }
    }

    $this->info("清理了 {$cleaned} 个过期键");
}
```

## 最佳实践

1. **定时执行**: 建议通过 cron 定时执行监控命令
2. **日志记录**: 在命令中添加详细的日志记录
3. **错误处理**: 添加异常处理和恢复机制
4. **性能监控**: 监控命令执行时间和资源使用

## 相关文档

- [延迟队列主文档](../README.md)
- [队列任务文档](../Job/README.md)
- [实体类文档](../Entity/README.md)