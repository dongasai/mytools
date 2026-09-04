# DCache 缓存机制详解

> 生成时间: 2026年05月23日
> 项目路径: moyuan/laravel_php (源自 kku_laravel_prod)
> **架构**: DLaravel 扩展库提供 DCache 基类，业务模块继承实现缓存逻辑

## 一、机制概述

DCache 是一个**缓存管理机制**,用于处理需要缓存的数据查询任务。它基于 DLaravel\Helper\Cache 实现缓存存储和管理,主要用于配置数据的缓存场景。

### 核心特性

- **缓存管理**: 自动处理缓存数据的更新和获取
- **防重复机制**: 防止短时间内重复执行相同查询
- **TTL 管理**: 可配置的缓存过期时间
- **静态方法**: 通过静态 `getData()` 方法快速获取缓存
- **自动键生成**: 根据类名和参数自动生成缓存键

### 架构设计

**DLaravel 扩展库提供**:
- `DCacheBase` 抽象基类（纯缓存管理核心逻辑）
- `DCacheInterface` 接口（定义缓存类契约）
- `DCacheJob` 抽象基类（延迟队列缓存任务）
- `DCacheJobInterface` 接口（定义延迟队列任务契约）
- `QueueCacheInterface` 接口（定义队列缓存数据方法）
- `QueueCache` trait（为队列任务提供缓存功能）
- `CacheItem` 实体类（PSR-6 标准缓存项）
- `SCache` 助手类（缓存操作封装）

**业务模块实现**:
- 继承 `DLaravel\DCache\DCacheBase`
- 实现三个抽象方法：`getTtl()`, `getPreventDuplication()`, `getNewData()`
- 通过静态 `getData()` 方法获取缓存数据

**已移除的中间层**:
- ❌ Modules/ABase 的所有缓存中间类（已清理）

## 二、核心组件列表

| 组件类型 | 组件名称 | 文件路径 | 功能说明 |
|---------|---------|---------|---------|
| 抽象基类 | DCacheBase | `extensions/dlaravel/DCache/DCacheBase.php` | 缓存管理核心逻辑 |
| 接口 | DCacheInterface | `extensions/dlaravel/DCache/DCacheInterface.php` | 定义缓存类契约 |
| Trait | QueueCache | `extensions/dlaravel/DCache/QueueCache.php` | 为队列任务提供缓存功能 |
| 缓存助手 | Cache | `extensions/dlaravel/Helper/Cache.php` | DLaravel 底层缓存操作 |

**架构说明**:
- DCache 基类位于 `extensions/dlaravel/DCache/` (框架层)
- 业务 DCache 类位于各模块的 `Modules/{模块}/DCache/` (业务层)
- 底层依赖 `DLaravel\Helper\Cache` 实现缓存存储

## 三、工作流程详解

### 3.1 缓存获取流程

```
调用 getData() → 检查防重复标记
    ↓                    ↓
标记存在 → 返回缓存      标记不存在 → 检查缓存
                              ↓         ↓
                          缓存存在 → 返回  缓存不存在 → getNewData()
                                                    ↓
                                                存储缓存 + 防重复标记
                                                    ↓
                                                返回数据
```

### 3.2 缓存更新逻辑

**防重复机制**:
- 使用 `_PD` 后缀的标记键
- 在 `getPreventDuplication()` 秒内，直接返回缓存数据
- 避免短时间内重复查询数据库

**TTL 管理**:
- 缓存数据存储 `getTtl()` 秒
- 过期后自动失效，下次调用重新获取

## 四、两种缓存实现方式

DCache 体系提供两种缓存实现方式，适用于不同场景：

### 4.1 DCacheBase - 纯缓存基类（推荐）

**适用场景**: 需要缓存配置数据、查询结果等静态数据

**特点**:
- 同步获取缓存数据
- 自动管理缓存生命周期
- 通过静态 `getData()` 方法快速获取
- 无队列依赖

**使用方式**: 见第五章详细说明

### 4.2 DCacheJob - 延迟队列缓存任务

**适用场景**: 需要异步更新缓存、通过事件触发缓存刷新

**特点**:
- 继承 `QueueJob` 队列任务基类
- 通过 Redis 延迟队列异步执行
- 支持事件监听器触发
- 2秒延迟更新缓存

**架构关系**:
```
DCacheJob extends QueueJob
  ├── implements DCacheJobInterface（定义延迟时间）
  ├── implements QueueCacheInterface（定义数据方法）
  └── use QueueCache trait（提供缓存功能）
```

**核心组件**:
- `DCacheJob`: 延迟队列缓存任务抽象类
- `DCacheJobInterface`: 定义 `getDelay()` 方法
- `QueueCacheInterface`: 定义 `getNewData()`, `getTtl()`, `getPreventDuplication()`, `getRequiredArgIndex()`
- `QueueCache` trait: 提供 `getKey()`, `eventListen()`, `jobUpdate()`, `updateSync()` 方法
- `CacheItem`: PSR-6 标准缓存项实体
- `SCache`: 缓存操作封装助手

## 五、DCacheBase 使用方式详解

### 4.1 创建 DCache 缓存类

```php
<?php

namespace Modules\YourModule\DCache;

use DLaravel\DCache\DCacheBase;

class YourConfigCache extends DCacheBase
{
    /**
     * 缓存时间(秒)
     */
    public static function getTtl(): int
    {
        return 3600; // 1小时
    }

    /**
     * 防重复执行时间(秒)
     */
    public static function getPreventDuplication(): int
    {
        return 600; // 10分钟
    }

    /**
     * 获取新数据（业务逻辑）
     */
    protected static function getNewData(array $parameter = []): mixed
    {
        // 实现数据获取逻辑
        return YourService::getData();
    }
}
```

**关键点**:
- 继承 `DLaravel\DCache\DCacheBase`（框架基类）
- 实现 3 个抽象方法（必需）
- 基类自动提供 `getData()` 方法
- 基类自动处理缓存管理逻辑

### 4.2 获取缓存数据

```php
// 获取缓存数据(自动处理缓存逻辑)
$data = YourConfigCache::getData(['param1', 'param2']);

// 强制刷新缓存
$data = YourConfigCache::getData(['param1', 'param2'], true);

// 清除缓存
YourConfigCache::clearCache(['param1', 'param2']);

// 刷新缓存
$data = YourConfigCache::refreshCache(['param1', 'param2']);
```

## 六、DCacheJob 使用方式详解

### 6.1 创建 DCacheJob 缓存任务

```php
<?php

namespace Modules\YourModule\DCache;

use DLaravel\DCache\DCacheJob;

class YourAsyncCache extends DCacheJob
{
    /**
     * 缓存时间(秒)
     */
    public static function getTtl(): int
    {
        return 3600; // 1小时
    }

    /**
     * 防重复执行时间(秒)
     */
    public static function getPreventDuplication(): int
    {
        return 600; // 10分钟
    }

    /**
     * 延迟时间(秒) - 可选覆盖
     */
    public static function getDelay(): int
    {
        return 5; // 默认 2秒，可覆盖
    }

    /**
     * 必填参数索引
     */
    public static function getRequiredArgIndex(): array
    {
        return ['user_id', 'app_id']; // 必需参数列表
    }

    /**
     * 获取新数据（业务逻辑）
     */
    public static function getNewData(array $parameter = []): mixed
    {
        // 实现数据获取逻辑
        return YourService::getData($parameter);
    }
}
```

**关键点**:
- 继承 `DLaravel\DCache\DCacheJob`（框架基类）
- 实现 4 个必需方法（`getTtl`, `getPreventDuplication`, `getNewData`, `getRequiredArgIndex`）
- 可选覆盖 `getDelay()` 方法（默认 2秒）
- 基类自动提供 `eventListen()` 方法用于事件触发

### 6.2 通过事件触发缓存更新

**步骤1: 定义事件**

```php
// 例如: UserUpdatedEvent
class UserUpdatedEvent
{
    public $user;

    public function __construct($user)
    {
        $this->user = $user;
    }
}
```

**步骤2: 创建事件监听器**

```php
use Modules\YourModule\DCache\YourAsyncCache;

class UserCacheListener
{
    public function handle($event)
    {
        // 触发延迟队列缓存更新
        YourAsyncCache::eventListen($event->user);
    }
}
```

**步骤3: 注册事件监听器**

在 `EventServiceProvider` 中注册：

```php
protected $listen = [
    UserUpdatedEvent::class => [
        UserCacheListener::class,
    ],
];
```

### 6.3 工作流程

```
事件触发 → eventListen($user)
    ↓
验证必需参数 (getRequiredArgIndex)
    ↓
添加到 Redis 延迟队列 (jobUpdate)
    ↓
等待延迟时间 (getDelay 秒)
    ↓
队列执行 updateSync()
    ↓
调用 getNewData() 获取数据
    ↓
通过 SCache 存储缓存 (CacheItem)
    ↓
完成
```

### 6.4 CacheItem 和 SCache

**CacheItem 实体类**（PSR-6 标准实现）:

```php
use DLaravel\Entity\CacheItem;

// 创建缓存项
$item = new CacheItem(
    key: 'cache_key',
    value: $data,
    create_ts: time(),
    ttl: 3600
);

// PSR-6 方法
$item->getKey();          // 获取键名
$item->get();             // 获取值（isHit=true 时）
$item->getValue();        // 直接获取值
$item->isHit();           // 检查是否命中
$item->set($value);       // 设置值
$item->expiresAt($time);  // 设置过期时间
$item->expiresAfter($ttl);// 设置过期秒数
```

**SCache 助手类**:

```php
use DLaravel\Helper\SCache;

// 存储缓存
SCache::put($key, $data, $ttl, $tags);
SCache::put($key, $cacheItem, null); // 直接存 CacheItem

// 获取缓存（返回 CacheItem）
$item = SCache::get($key, $default);
$value = $item->getValue();

// 直接获取值
$value = SCache::getValue($key, $default);

// 检查缓存
SCache::has($key);

// 生成键名
$key = SCache::getKey($data);
```

## 七、配置参数详解

### 7.1 DCacheBase 必需实现的抽象方法

| 方法名 | 返回类型 | 说明 |
|-------|---------|------|
| `getTtl()` | int | 缓存有效期(秒),过期后需重新获取 |
| `getPreventDuplication()` | int | 防重复时间窗口(秒) |
| `getNewData(array $parameter)` | mixed | **核心方法**: 定义如何获取新数据 |

### 7.2 DCacheBase 基类自动提供的方法

| 方法名 | 参数 | 返回值 | 功能 |
|-------|------|-------|------|
| `getData()` | `$parameter = [], $force = false` | mixed | 获取缓存数据（核心方法） |
| `getKey()` | `$parameter = []` | string | 获取缓存键名（自动生成） |
| `clearCache()` | `$parameter = []` | void | 清除缓存 |
| `refreshCache()` | `$parameter = []` | mixed | 强制刷新缓存 |

### 7.3 DCacheJob 必需实现的方法

| 方法名 | 返回类型 | 说明 |
|-------|---------|------|
| `getTtl()` | int | 缓存有效期(秒) |
| `getPreventDuplication()` | int | 防重复时间窗口(秒) |
| `getNewData(array $parameter)` | mixed | **核心方法**: 定义如何获取新数据 |
| `getRequiredArgIndex()` | array | 必填参数索引列表（如 `['user_id', 'app_id']`) |

**可选覆盖方法**:

| 方法名 | 返回类型 | 默认值 | 说明 |
|-------|---------|-------|------|
| `getDelay()` | int | 2秒 | 延迟时间，可覆盖 |

### 7.4 DCacheJob 基类自动提供的方法

| 方法名 | 参数 | 返回值 | 功能 |
|-------|------|-------|------|
| `eventListen()` | `$user` (包含必需参数的对象) | void | 事件监听触发入口 |
| `jobUpdate()` | `$parameter` (参数数组) | void | 添加到延迟队列 |
| `updateSync()` | `$parameter` (参数数组) | string | 队列执行时的同步更新方法 |
| `getKey()` | `$parameter = []` | string | 获取缓存键名（自动生成） |

## 八、项目应用示例

### 8.1 Application 模块缓存实现

**目录**: `Modules/Application/DCache/`

**示例文件**:
- `RequestLogRouter.php` - 请求路由缓存
- `RequestLogPath.php` - 请求路径缓存
- `RequestLogRouterSearch.php` - 路由搜索缓存

### 8.2 DCacheBase 完整示例

```php
<?php

namespace Modules\Application\DCache;

use DLaravel\Model\RequestLog;
use DLaravel\DCache\DCacheBase;

/**
 * 请求日志路由缓存
 */
class RequestLogRouter extends DCacheBase
{
    /**
     * 缓存时间(秒)
     */
    public static function getTtl(): int
    {
        return 3600 * 24; // 24小时
    }

    /**
     * 防重复执行时间(秒)
     */
    public static function getPreventDuplication(): int
    {
        return 3600; // 1小时
    }

    /**
     * 获取新数据
     */
    protected static function getNewData(array $parameter = []): array
    {
        $data = RequestLog::query()
            ->groupBy('router')
            ->distinct()
            ->pluck('router', 'router')
            ->toArray();

        $res = [];
        foreach ($data as $k => $v) {
            if (in_array(substr($k, 0, 3), $parameter)) {
                $res[$k] = $k;
            }
        }

        return $res;
    }
}
```

### 8.3 在 Controller 中使用 DCacheBase

```php
use Modules\Application\DCache\RequestLogRouter;

class RequestLogController extends AdminController
{
    protected function grid()
    {
        return Grid::make(new RequireLog, function (Grid $grid) {
            $grid->filter(function (Grid\Filter $filter) {
                // 使用缓存数据作为下拉选项
                $filter->equal('router')->select(RequestLogRouter::getData(['app', 'oap']));
            });
        });
    }
}
```

## 九、关键文件路径索引

### 9.1 DLaravel DCache 基础框架

| 文件路径 | 说明 |
|---------|------|
| [extensions/dlaravel/DCache/DCacheBase.php](../extensions/dlaravel/DCache/DCacheBase.php) | 缓存管理抽象基类 |
| [extensions/dlaravel/DCache/DCacheInterface.php](../extensions/dlaravel/DCache/DCacheInterface.php) | 缓存接口定义 |
| [extensions/dlaravel/DCache/DCacheJob.php](../extensions/dlaravel/DCache/DCacheJob.php) | 延迟队列缓存任务基类 |
| [extensions/dlaravel/DCache/DCacheJobInterface.php](../extensions/dlaravel/DCache/DCacheJobInterface.php) | 延迟队列任务接口 |
| [extensions/dlaravel/DCache/QueueCacheInterface.php](../extensions/dlaravel/DCache/QueueCacheInterface.php) | 队列缓存数据接口 |
| [extensions/dlaravel/DCache/QueueCache.php](../extensions/dlaravel/DCache/QueueCache.php) | 队列缓存 Trait |
| [extensions/dlaravel/Entity/CacheItem.php](../extensions/dlaravel/Entity/CacheItem.php) | PSR-6 缓存项实体 |
| [extensions/dlaravel/Helper/SCache.php](../extensions/dlaravel/Helper/SCache.php) | 缓存操作助手 |

### 9.2 业务模块 DCache 应用

| 文件路径 | 说明 |
|---------|------|
| [Modules/Application/DCache/](../Modules/Application/DCache/) | Application 模块缓存目录 |
| [Modules/Application/DCache/RequestLogRouter.php](../Modules/Application/DCache/RequestLogRouter.php) | 请求路由缓存 |
| [Modules/Application/DCache/RequestLogPath.php](../Modules/Application/DCache/RequestLogPath.php) | 请求路径缓存 |
| [Modules/Application/DCache/RequestLogRouterSearch.php](../Modules/Application/DCache/RequestLogRouterSearch.php) | 路由搜索缓存 |

### 9.3 已移除的 ABase 中间层

**已删除文件**:
- ❌ `Modules/ABase/Services/DQueueJob.php`
- ❌ `Modules/ABase/Services/QueueCache.php`
- ❌ `Modules/ABase/Support/SCache.php`
- ❌ `Modules/ABase/Models/CacheItem.php`
- ❌ `Modules/ABase/Contracts/DQueueJobInterface.php`
- ❌ `Modules/ABase/Contracts/QueueCacheInterface.php`

## 十、设计模式与架构

### 10.1 使用的设计模式

1. **模板方法模式**: 基类定义缓存管理流程,子类实现数据获取
2. **抽象工厂模式**: DCacheBase 作为抽象基类,子类实现具体数据获取逻辑
3. **接口隔离原则**: 通过 DCacheInterface 定义清晰的契约
4. **依赖倒置原则**: 业务层依赖框架层的抽象基类

### 10.2 架构优势

- **框架统一**: DLaravel 提供统一的缓存管理逻辑
- **业务简化**: 业务类只需关注数据获取逻辑
- **依赖清晰**: 符合"框架→业务"的依赖方向
- **易于扩展**: 新增缓存类只需继承基类实现3个方法
- **无中间层**: 移除 ABase 中间层，架构更清晰

## 十一、注意事项与最佳实践

### 11.1 TTL 和防重复时间配置

建议遵循以下规则:
- `getPreventDuplication()` < `getTtl()`
- 防重复时间应小于缓存时间
- 防重复时间避免频繁查询数据库

### 11.2 参数使用

- `$parameter` 参数会自动参与缓存键生成
- 不同参数会生成不同的缓存
- 参数应保持简洁，避免复杂对象

### 11.3 性能优化建议

1. **合理设置 TTL**: 避免频繁更新缓存
2. **优化 getNewData()**: 确保数据获取方法高效
3. **使用防重复机制**: 避短时间重复查询
4. **及时清理缓存**: 数据变更时调用 `clearCache()`

## 十二、常见问题解答

### Q1: 为什么使用 DLaravel\DCache\DCacheBase？

**架构清晰**:
- DLaravel 框架层：提供缓存基础类（`extensions/dlaravel/DCache`）
- 业务模块层：继承基类实现具体缓存逻辑（`Modules/{模块}/DCache`）
- 减少中间抽象层，直接依赖框架

**优势**:
- 框架提供统一的缓存管理逻辑
- 业务类只需关注数据获取逻辑
- 符合"框架→业务"的依赖方向
- 易于维护和扩展

### Q2: DCacheBase 与 DCacheJob 的区别？

| 对比项 | DCacheBase 基类 | DCacheJob 基类 |
|-------|----------------|---------------|
| 类型 | 纯缓存基类 | 延迟队列缓存任务 |
| 继承关系 | extends DCacheBase | extends QueueJob + implements DCacheJobInterface |
| 适用场景 | 同步获取缓存数据 | 异步更新缓存、事件触发 |
| 执行方式 | 立即执行 | 延迟队列异步执行 |
| 触发方式 | 直接调用 getData() | 事件监听器调用 eventListen() |
| 接口实现 | DCacheInterface | DCacheJobInterface + QueueCacheInterface |
| Trait 使用 | 无 | QueueCache trait |

**推荐**:
- 优先使用 DCacheBase（同步缓存）
- 需要异步更新时使用 DCacheJob

### Q3: 缓存键如何生成？

基类的 `getKey()` 方法根据类名和参数自动生成唯一缓存键:

```php
protected static function getKey(array $parameter): string
{
    return md5(serialize([static::class, $parameter]));
}
```

### Q4: 如何强制刷新缓存？

调用 `getData()` 时传入 `$force = true`:

```php
$data = YourConfigCache::getData([], true); // 强制刷新
```

或使用 `refreshCache()` 方法:

```php
$data = YourConfigCache::refreshCache([]); // 强制刷新
```

### Q5: 为什么移除 ABase 的中间层？

**架构简化**:
- 原设计：业务类 → ABase 中间层 → DLaravel
- 新设计：业务类 → DLaravel DCacheBase

**优势**:
- 减少抽象层级，更直接
- 框架直接提供服务，符合模块化原则
- ABase 模块职责更清晰（只提供业务基础服务）

---

**文档版本**: v2.0
**最后更新**: 2026年05月23日
**维护者**: AI Assistant