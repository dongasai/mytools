# DLaravel 队列系统文档

## 概述

DLaravel 队列系统为项目提供了统一的队列任务和事件处理架构，包含以下核心组件：

- `QueueJob` - 队列任务基类
- `ShouldQueue` - 队列事件监听器基类
- `QueueJobInterface` - 队列任务接口
- `JobEvent` - 队列事件处理器
- `Helper` - 队列辅助工具

## 架构设计

### 队列任务类 (QueueJob)

所有队列任务都应该继承 `DLaravel\Queue\QueueJob` 基类：

```php
use DLaravel\Queue\QueueJob;

class MyJob extends QueueJob
{
    protected $data;
    
    public function __construct(array $data)
    {
        $this->data = $data;
        parent::__construct($data);
    }
    
    public function run(): bool
    {
        // 实现具体的任务逻辑
        return true;
    }
    
    public function payload()
    {
        return $this->data;
    }
}
```

### 队列事件监听器 (ShouldQueue)

所有队列事件监听器都应该继承 `DLaravel\Queue\ShouldQueue` 基类：

```php
use DLaravel\Queue\ShouldQueue;

class MyEventListener extends ShouldQueue
{
    public $queue = 'events';
    public $tries = 5;

    public function run(object $event): bool
    {
        // 检查事件类型
        if (!$this->shouldHandle($event)) {
            $this->logInfo('事件被过滤，跳过处理', [
                'event_class' => get_class($event)
            ]);
            return true;
        }

        // 处理事件逻辑
        $this->logInfo('处理事件', [
            'event_id' => $this->getEventId($event)
        ]);

        // 具体的业务逻辑
        if ($event instanceof MyEvent) {
            // 处理特定事件
            $this->processMyEvent($event);
        }

        return true;
    }

    protected function shouldHandle(object $event): bool
    {
        // 自定义过滤逻辑
        return $event instanceof MyEvent;
    }

    protected function handleFailure(\Throwable $exception): void
    {
        // 自定义失败处理
        $this->logError('事件处理失败');
    }

    private function processMyEvent(MyEvent $event): void
    {
        // 具体的事件处理逻辑
    }
}
```

## 迁移指南

### 从 Laravel 原生队列任务迁移

**迁移前：**
```php
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class OldJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    
    public function handle(): void
    {
        // 处理逻辑
    }
}
```

**迁移后：**
```php
use DLaravel\Queue\QueueJob;

class NewJob extends QueueJob
{
    public function run(): bool
    {
        // 处理逻辑
        return true;
    }
    
    public function payload()
    {
        return $this->args;
    }
}
```

### 从 Laravel 原生事件监听器迁移

**迁移前：**
```php
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class OldListener implements ShouldQueue
{
    use InteractsWithQueue;
    
    public function handle(MyEvent $event): void
    {
        // 处理逻辑
    }
}
```

**迁移后：**
```php
use DLaravel\Queue\ShouldQueue;

class NewListener extends ShouldQueue
{
    public $queue = 'events';

    public function run(object $event): bool
    {
        // 处理逻辑
        // Laravel会自动调用handle方法，然后调用这个run方法

        if ($event instanceof MyEvent) {
            // 处理特定事件
        }

        return true;
    }
}
```

## 功能特性

### QueueJob 特性

1. **统一的任务处理流程**: 自动处理任务执行、重试、失败等
2. **日志记录**: 自动记录任务执行日志
3. **错误处理**: 统一的异常处理和重试机制
4. **性能监控**: 自动记录任务执行时间

### ShouldQueue 特性

1. **队列配置管理**: 统一的队列名称、重试次数、超时时间配置
2. **错误处理**: 完善的异常处理和失败回调
3. **日志记录**: 集成项目日志系统
4. **重试策略**: 支持指数退避的重试机制
5. **事件过滤**: 提供事件处理前的过滤机制
6. **安全执行**: 提供安全的事件处理包装器
7. **监控支持**: 提供任务标签用于监控和调试

## 最佳实践

### 队列任务

1. **构造函数**: 总是调用 `parent::__construct()` 传递参数
2. **返回值**: `run()` 方法返回 `bool` 表示任务是否成功
3. **异常处理**: 让基类处理异常和重试逻辑
4. **日志记录**: 使用 `logInfo()` 方法记录关键信息

### 事件监听器

1. **队列配置**: 明确设置 `$queue`、`$tries`、`$timeout` 属性
2. **安全处理**: 使用 `safeHandle()` 方法包装事件处理逻辑
3. **事件过滤**: 重写 `shouldHandle()` 方法实现事件过滤
4. **失败处理**: 重写 `handleFailure()` 方法实现自定义失败处理
5. **日志记录**: 使用内置的日志方法记录处理过程

### 错误处理

1. **不要捕获所有异常**: 让基类处理重试逻辑
2. **记录关键信息**: 在异常发生时记录足够的上下文信息
3. **优雅降级**: 在失败处理中实现优雅降级逻辑

## 配置说明

### 队列配置

```php
// 队列名称
public $queue = 'default';

// 最大重试次数
public $tries = 3;

// 任务超时时间（秒）
public $timeout = 300;

// 重试延迟时间（秒）
public $retryAfter = 60;
```

### 重试策略

系统支持指数退避重试策略：
- 第1次重试：60秒后
- 第2次重试：120秒后  
- 第3次重试：240秒后
- 最大延迟：3600秒（1小时）

## 监控和调试

### 日志记录

系统自动记录以下日志：
- 任务/事件开始处理
- 任务/事件处理完成
- 任务/事件处理失败
- 重试信息

### 任务标签

事件监听器自动提供以下标签用于监控：
- `listener:ClassName` - 监听器类名
- `queue:QueueName` - 队列名称

### 调试信息

可以通过以下方式获取调试信息：
- 查看队列运行日志表 (`myai_job_runs`)
- 查看失败任务表 (`myai_failed_jobs`)
- 使用 `php artisan queue:monitor` 命令监控队列状态

## 注意事项

1. **向后兼容**: 新的基类与 Laravel 原生队列系统完全兼容
2. **性能影响**: 基类增加了日志记录，对性能有轻微影响
3. **内存使用**: 长时间运行的任务注意内存使用情况
4. **数据库连接**: 在长时间运行的任务中注意数据库连接管理

## 故障排除

### 常见问题

1. **任务不执行**: 检查队列工作进程是否运行
2. **重试次数过多**: 检查任务逻辑是否有死循环
3. **内存泄漏**: 检查任务中是否有未释放的资源
4. **数据库连接**: 检查长时间运行任务的数据库连接状态

### 调试步骤

1. 检查队列配置
2. 查看错误日志
3. 检查任务参数
4. 验证业务逻辑
5. 测试重试机制
