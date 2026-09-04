# 延迟队列实体类

## 概述

DelayQueue Entity 模块定义了延迟队列系统中使用的数据实体，主要包含队列任务的基本信息和执行参数。

## 核心实体

### Queue 实体 (`Queue.php`)

Queue 实体是延迟队列系统的核心数据载体，包含了任务执行所需的所有信息。

#### 类定义

```php
namespace DLaravel\Helper\Entity;

class Queue
{
    /**
     * 创建时间
     * @var int
     */
    public int $create_ts;

    /**
     * 延迟时间
     * @var int
     */
    public int $delay_ts;

    /**
     * 运行类
     * @var string
     */
    public string $runClass;

    /**
     * 运行方法
     * @var string
     */
    public string $runMethod;

    /**
     * 运行参数
     * @var mixed
     */
    public $runParam;
}
```

#### 属性详解

| 属性 | 类型 | 说明 | 示例 |
|------|------|------|------|
| `create_ts` | `int` | 任务创建时间戳 | `1625097600` |
| `delay_ts` | `int` | 延迟执行时间（秒） | `10` |
| `runClass` | `string` | 执行类的完整类名 | `App\Services\UserService` |
| `runMethod` | `string` | 执行方法名 | `updateUserStats` |
| `runParam` | `mixed` | 传递给方法的参数 | `['user_id' => 123]` |

## 使用示例

### 基本创建

```php
use DLaravel\Dto\Queue;

// 创建队列实体
$queue = new Queue();
$queue->create_ts = time();
$queue->delay_ts = 10;
$queue->runClass = 'App\Services\UserService';
$queue->runMethod = 'updateUserStats';
$queue->runParam = [
    'user_id' => 123,
    'action' => 'increment_score',
    'value' => 100
];
```

### 复杂参数示例

```php
// 复杂业务场景
$queue = new Queue();
$queue->create_ts = time();
$queue->delay_ts = 30;
$queue->runClass = 'App\Module\UrsPromotion\Services\UrsTalentUpstreamUpdateService';
$queue->runMethod = 'updateTalentLevel';
$queue->runParam = [
    'referrer_id' => 12345,
    'original_user_id' => 67890,
    'level' => 2,
    'trigger_time' => time(),
    'metadata' => [
        'source' => 'talent_level_up',
        'batch_id' => 'batch_001'
    ]
];
```

### 批量操作示例

```php
// 批量创建队列实体
$queues = [];
$userIds = [123, 456, 789];

foreach ($userIds as $userId) {
    $queue = new Queue();
    $queue->create_ts = time();
    $queue->delay_ts = 5;
    $queue->runClass = 'App\Services\NotificationService';
    $queue->runMethod = 'sendWelcomeMessage';
    $queue->runParam = ['user_id' => $userId];

    $queues[] = $queue;
}
```

## 数据验证

### 基本验证

```php
class QueueValidator
{
    public static function validate(Queue $queue): bool
    {
        // 验证创建时间
        if ($queue->create_ts <= 0) {
            throw new \InvalidArgumentException('创建时间必须大于0');
        }

        // 验证延迟时间
        if ($queue->delay_ts < 0 || $queue->delay_ts > 3600) {
            throw new \InvalidArgumentException('延迟时间必须在0-3600秒之间');
        }

        // 验证类名
        if (empty($queue->runClass) || !class_exists($queue->runClass)) {
            throw new \InvalidArgumentException('运行类不存在');
        }

        // 验证方法名
        if (empty($queue->runMethod) || !method_exists($queue->runClass, $queue->runMethod)) {
            throw new \InvalidArgumentException('运行方法不存在');
        }

        // 验证回调可调用性
        if (!is_callable([$queue->runClass, $queue->runMethod])) {
            throw new \InvalidArgumentException('回调方法不可调用');
        }

        return true;
    }
}
```

### 参数验证

```php
class QueueParameterValidator
{
    public static function validateRunParam($runParam): bool
    {
        // 检查参数类型
        if (!is_array($runParam) && !is_string($runParam) && !is_numeric($runParam)) {
            throw new \InvalidArgumentException('运行参数类型不支持');
        }

        // 检查数组参数
        if (is_array($runParam)) {
            // 检查数组深度（避免过深的嵌套）
            if (self::getArrayDepth($runParam) > 5) {
                throw new \InvalidArgumentException('参数数组嵌套过深');
            }

            // 检查数组大小（避免过大的参数）
            if (count($runParam, COUNT_RECURSIVE) > 1000) {
                throw new \InvalidArgumentException('参数数组过大');
            }
        }

        return true;
    }

    private static function getArrayDepth(array $array): int
    {
        $maxDepth = 1;

        foreach ($array as $value) {
            if (is_array($value)) {
                $depth = self::getArrayDepth($value) + 1;
                if ($depth > $maxDepth) {
                    $maxDepth = $depth;
                }
            }
        }

        return $maxDepth;
    }
}
```

## 序列化和反序列化

### JSON 序列化

```php
class QueueSerializer
{
    public static function toJson(Queue $queue): string
    {
        return json_encode([
            'create_ts' => $queue->create_ts,
            'delay_ts' => $queue->delay_ts,
            'runClass' => $queue->runClass,
            'runMethod' => $queue->runMethod,
            'runParam' => $queue->runParam
        ], JSON_UNESCAPED_UNICODE);
    }

    public static function fromJson(string $json): Queue
    {
        $data = json_decode($json, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \InvalidArgumentException('JSON 解析失败: ' . json_last_error_msg());
        }

        $queue = new Queue();
        $queue->create_ts = $data['create_ts'];
        $queue->delay_ts = $data['delay_ts'];
        $queue->runClass = $data['runClass'];
        $queue->runMethod = $data['runMethod'];
        $queue->runParam = $data['runParam'];

        return $queue;
    }
}
```

### 数组转换

```php
class QueueArrayConverter
{
    public static function toArray(Queue $queue): array
    {
        return [
            'create_ts' => $queue->create_ts,
            'delay_ts' => $queue->delay_ts,
            'runClass' => $queue->runClass,
            'runMethod' => $queue->runMethod,
            'runParam' => $queue->runParam
        ];
    }

    public static function fromArray(array $data): Queue
    {
        $queue = new Queue();
        $queue->create_ts = $data['create_ts'] ?? time();
        $queue->delay_ts = $data['delay_ts'] ?? 0;
        $queue->runClass = $data['runClass'] ?? '';
        $queue->runMethod = $data['runMethod'] ?? '';
        $queue->runParam = $data['runParam'] ?? null;

        return $queue;
    }
}
```

## 工厂模式

### Queue 工厂类

```php
class QueueFactory
{
    /**
     * 创建标准队列实体
     */
    public static function create(
        string $class,
        string $method,
        $param = null,
        int $delay = 0
    ): Queue {
        $queue = new Queue();
        $queue->create_ts = time();
        $queue->delay_ts = $delay;
        $queue->runClass = $class;
        $queue->runMethod = $method;
        $queue->runParam = $param;

        return $queue;
    }

    /**
     * 创建用户相关队列实体
     */
    public static function createUserQueue(
        int $userId,
        string $action,
        array $data = [],
        int $delay = 5
    ): Queue {
        return self::create(
            'App\Services\UserService',
            'processUserAction',
            array_merge(['user_id' => $userId, 'action' => $action], $data),
            $delay
        );
    }

    /**
     * 创建通知队列实体
     */
    public static function createNotificationQueue(
        int $userId,
        string $message,
        string $type = 'info',
        int $delay = 0
    ): Queue {
        return self::create(
            'App\Services\NotificationService',
            'sendNotification',
            [
                'user_id' => $userId,
                'message' => $message,
                'type' => $type,
                'timestamp' => time()
            ],
            $delay
        );
    }
}
```

## 最佳实践

### 1. 属性设置

```php
// ✅ 推荐：明确设置所有属性
$queue = new Queue();
$queue->create_ts = time();
$queue->delay_ts = 10;
$queue->runClass = SomeService::class;
$queue->runMethod = 'someMethod';
$queue->runParam = ['key' => 'value'];

// ❌ 不推荐：遗漏属性设置
$queue = new Queue();
$queue->runClass = SomeService::class;
// 缺少其他必要属性
```

### 2. 参数结构

```php
// ✅ 推荐：结构化参数
$queue->runParam = [
    'user_id' => 123,
    'action' => 'update',
    'data' => ['score' => 100],
    'metadata' => ['source' => 'api']
];

// ❌ 不推荐：简单值或复杂对象
$queue->runParam = 123; // 太简单
$queue->runParam = $complexObject; // 可能序列化问题
```

### 3. 类名使用

```php
// ✅ 推荐：使用类常量
$queue->runClass = SomeService::class;

// ❌ 不推荐：硬编码字符串
$queue->runClass = 'App\Services\SomeService';
```

## 扩展建议

### 1. 添加状态字段

```php
class ExtendedQueue extends Queue
{
    public string $status = 'pending'; // pending, processing, completed, failed
    public ?string $error = null;
    public ?int $executed_at = null;
    public int $retry_count = 0;
}
```

### 2. 添加优先级

```php
class PriorityQueue extends Queue
{
    public int $priority = 0; // 0=normal, 1=high, -1=low
    public string $queue_name = 'default';
}
```

### 3. 添加标签

```php
class TaggedQueue extends Queue
{
    public array $tags = [];
    public ?string $group = null;
    public ?string $batch_id = null;
}
```

## 相关文档

- [延迟队列主文档](../README.md)
- [队列任务文档](../Job/README.md)
- [控制台命令文档](../Console/README.md)
