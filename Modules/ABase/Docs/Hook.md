# Hook钩子系统

> ABase模块提供的灵活钩子系统，专注于数据转换和过滤，允许代码在特定位置插入自定义逻辑改变数据并返回结果

## 概述

Hook系统的核心功能是**允许代码在特定位置插入自定义逻辑改变数据并返回结果**。它是一种纯过滤器系统，专注于数据转换、验证和过滤。

### Hook系统的核心特点

- **纯过滤器系统**：所有Hook都必须返回处理后的数据，不支持无返回值的操作
- **强类型约束**：参数和返回值都必须是对象（继承HookParameter和HookResult基类）
- **类型安全**：提供完整的类型验证和运行时检查
- **优先级控制**：支持处理器优先级排序，确保执行顺序可控（1-100，数值越小越早执行）
- **链式处理**：支持多个处理器按优先级顺序处理同一数据
- **条件执行**：处理器可以基于参数和结果决定是否执行
- **无注册模式**：Hook处理器可以直接使用类名添加，无需预先注册Hook定义

## Hook基本逻辑

### Hook定义的三要素

HookDefinition定义了Hook的完整规范，包含三个核心要素：

1. **Hook名称**
   - Hook的唯一标识符，使用类全名作为Hook名称
   - 例如：`Modules\ABase\Hooks\Definitions\DemoHook::class`
   - 通过`getName()`方法获取

2. **参数类定义 ($parameter_class)**
   - 定义Hook接受的参数类型
   - 必须继承自HookParameter基类
   - 使用`readonly`属性定义
   - 在运行时进行类型验证

3. **返回值类定义 ($return_class)**
   - 定义Hook返回的数据类型
   - 必须继承自HookResult基类
   - 使用`readonly`属性定义
   - 强制要求返回处理后的数据

### Hook执行流程

Hook的执行遵循严格的流程：

1. **定义阶段** - 创建继承HookDefinition的Hook定义类
   ```php
   use Modules\ABase\Hooks\Core\HookDefinition;

   // 直接定义Hook定义类
   class PostContentFilterHook extends HookDefinition
   {
       public string $description = '过滤和处理文章内容';

       public function __construct()
       {
           $this->parameter_class = PostContentParameter::class;
           $this->return_class = PostProcessResult::class;
           parent::__construct();
       }
   }

   class UserValidatorHook extends HookDefinition
   {
       public string $description = '验证用户数据完整性';

       public function __construct()
       {
           $this->parameter_class = UserDataParameter::class;
           $this->return_class = ValidationResult::class;
           parent::__construct();
       }
   }
   ```

2. **注册阶段** - 添加Hook处理器
   ```php
   use Modules\ABase\Hooks\Management\HookManager;
   use Modules\ABase\Hooks\Management\Hooks;

   // Hook系统采用无注册模式，Hook处理器可以直接添加
   // Hook定义类在add时会被自动验证和使用

   // 添加处理器（使用Hook定义类）
   // 注意：Hook系统移除了注册机制，直接使用类名即可
   HookManager::add(\Modules\ABase\Hooks\Definitions\DemoHook::class, DemoHookHandler::class, $priority);
   HookManager::add(\Modules\ABase\Hooks\Definitions\DemoHook::class, AnotherHandler::class, 10);

   // 或者使用助手类
   Hooks::add(\Modules\ABase\Hooks\Definitions\DemoHook::class, DemoHookHandler::class, $priority);
   Hooks::add(\Modules\ABase\Hooks\Definitions\DemoHook::class, AnotherHandler::class, 10);
   ```

3. **执行阶段** - 传入Hook定义类和参数对象并应用Hook（结果对象可选）
   ```php
   // 创建参数对象
   $contentParameter = new PostContentParameter([
       'content' => '<p>原始内容</p>',
       'format' => 'html'
   ]);

   // 方式1：不提供结果对象，系统自动创建
   $result = HookManager::apply(PostContentFilterHook::class, $contentParameter);

   // 方式2：使用助手类
   $result = Hooks::apply(PostContentFilterHook::class, $contentParameter);

   // 方式3：提供初始结果对象
   $initialResult = PostProcessResult::success([
       'content' => $contentParameter->getContent(),
       'format' => $contentParameter->getFormat()
   ]);
   $result = HookManager::apply(PostContentFilterHook::class, $contentParameter, $initialResult);

   // 方式4：使用便捷方法
   $result = Hooks::filter(PostContentFilterHook::class, [
       'content' => '<p>原始内容</p>',
       'format' => 'html'
   ], '处理文章内容');

   // $result 是 PostProcessResult 对象
   if ($result->isSuccess()) {
       $processedContent = $result->getProcessedContent();
   }
   ```

**重要说明**：Hook系统已经移除了注册机制，所有方法（add、apply等）都不需要验证是否已注册，系统会自动处理重复注册和未注册的情况。

### 数据流转过程

Hook系统的数据流转遵循以下规则：

1. **参数验证**：每个参数都会进行类型检查，确保符合Hook定义
2. **初始结果创建**：HookManager为第一个处理器创建初始的HookResult对象
3. **链式处理**：数据按优先级顺序经过多个处理器，每个都接收前一个的输出
4. **类型约束**：处理器必须返回正确的返回值类型
5. **结果封装**：最终结果封装为返回值对象

#### Hook处理器接口设计

```php
public static function handle(HookParameterInterface $parameter, HookResultInterface $result): HookResultInterface
public static function shouldExecute(HookParameterInterface $parameter, HookResultInterface $result): bool
public static function getPriority(): int
```

**设计原则**：采用严格的静态接口设计，确保类型安全和执行控制

- **handle方法**：
  - **第一个参数** `$parameter`：包含Hook执行的输入数据
  - **第二个参数** `$result`：前一个处理器的输出结果，由HookManager提供初始结果
  - **返回值**：当前处理器处理后的新结果
  - **注意**：这是一个静态方法

- **shouldExecute方法**：
  - 允许处理器基于参数和当前结果决定是否执行
  - 返回false时，HookManager会跳过此处理器
  - 提供了条件执行的能力
  - **注意**：这是一个静态方法

- **getPriority方法**：
  - 返回处理器的优先级数值
  - 数值越小优先级越高（1-100）
  - 建议范围：1-50
  - **注意**：这是一个静态方法

**链式处理流程**：
1. **HookManager** 为第一个处理器创建初始的 `HookResult` 对象
2. **处理器检查**：每个处理器先调用静态方法`shouldExecute`判断是否应该执行
3. **第一个处理器** 接收初始结果，处理后返回新的结果
4. **后续处理器** 接收前一个处理器的结果，继续处理
5. **最终结果** 最后一个处理器的输出作为Hook的最终结果

**优势**：
- **类型安全**：所有参数都使用接口约束，编译时类型检查
- **条件执行**：处理器可以基于业务逻辑决定是否执行
- **接口一致**：所有处理器都有相同的静态方法签名
- **简化逻辑**：处理器无需检查null值，专注于业务逻辑
- **框架职责**：链式处理的复杂性由HookManager处理
- **性能优化**：静态方法调用避免了实例化开销

### Hook与Event的区别

Hook系统与Event系统在设计理念上有本质区别：

**Hook系统特征：**
- 数据导向：专注于数据的转换和过滤
- 返回值强制：必须返回处理后的数据
- 类型安全：严格的参数和返回值类型约束
- 链式处理：支持多个处理器连续处理同一数据

**Event系统特征：**
- 事件导向：专注于业务事件的通知和响应
- 无返回值：监听器不返回数据
- 灵活性：支持任意类型的参数
- 解耦设计：用于业务流程解耦

### 类型安全保障

Hook系统提供多层类型安全：

1. **编译时类型检查**：通过PHP 8的类型提示确保参数和返回值类型正确
2. **运行时类型验证**：HookDefinition验证参数和返回值类是否继承正确的基类
3. **处理器验证**：注册处理器时验证回调函数的参数和返回值类型
4. **执行时检查**：执行Hook时验证实际参数和返回值是否符合定义

### Hook系统 vs Event系统

| 特性 | Hook系统 | Event系统 |
|------|----------|-----------|
| **用途** | 数据转换和过滤 | 业务事件通知和响应 |
| **返回值** | 必须有返回值（HookResult对象） | 事件监听器无返回值 |
| **参数类型** | 必须是对象（HookParameter） | 可以是任意类型 |
| **数据修改** | 直接修改和转换数据 | 只能读取事件数据 |
| **执行顺序** | 支持优先级控制 | 按注册顺序执行 |
| **类型安全** | 强类型约束和验证 | 弱类型，灵活性更高 |
| **典型场景** | 数据验证、格式转换、内容过滤、计算处理 | 业务流程解耦、状态变更通知、异步处理 |

**何时使用Hook系统：**
- 需要转换或过滤数据时
- 需要数据验证和格式化时
- 需要链式处理多个转换步骤时
- 需要强类型约束和类型安全时
- 插件化开发，允许第三方扩展数据处理逻辑时

**何时使用Event系统：**
- 业务流程通知和响应时
- 异步处理和后台任务时
- 多个监听器需要响应同一事件时
- 需要解耦业务逻辑时
- 不需要返回值的场景时

## 核心组件

### HookDefinition
Hook定义抽象类，用于定义Hook的元数据、参数要求和返回值要求。必须创建继承该基类的Hook定义类。

```php
use Modules\ABase\Hooks\Core\HookDefinition;

// 推荐方式：直接定义Hook定义类
class DemoHook extends HookDefinition
{
    public readonly string $parameter_class;
    public readonly string $return_class;
    public string $description = '演示Hook系统的参数处理和结果返回';

    public function __construct()
    {
        $this->parameter_class = DemoHookParameter::class;
        $this->return_class = DemoHookResult::class;
        parent::__construct();
    }
}

// Hook系统采用无注册模式
// 直接使用类名添加处理器和执行Hook
```

**主要方法**：
- `getName()`: 获取Hook名称（使用类全名）
- `getParameterClass()`: 获取参数类名
- `getDescription()`: 获取Hook描述
- `setDescription()`: 设置Hook描述
- `validateParameter()`: 验证参数是否匹配
- `validateResult()`: 验证返回值是否匹配
- `createParameter()`: 创建参数实例
- `createResult()`: 创建返回值实例
- `createSuccessResult()`: 创建成功结果实例
- `createFailureResult()`: 创建失败结果实例
- `toArray()`: 转换为数组
- `toJson()`: 转换为JSON
- `getHash()`: 获取Hook定义的唯一标识

### HookParameter
Hook参数抽象类，所有参数都必须继承此类并实现HookParameterInterface

```php
use Modules\ABase\Hooks\Core\HookParameter;

class DemoHookParameter extends HookParameter
{
    public function __construct(
        public readonly string $name = '',
        public readonly mixed $value = null,
        public readonly array $options = [],
        public readonly bool $enabled = true,
        public readonly string $description = ''
    ) {
        parent::__construct();
    }

    protected function validate(): void
    {
        // 参数验证逻辑
        if (empty($this->name)) {
            throw new \InvalidArgumentException('名称不能为空');
        }
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getValue(): mixed
    {
        return $this->value;
    }

    public function getOptions(): array
    {
        return $this->options;
    }

    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'value' => $this->value,
            'options' => $this->options,
            'enabled' => $this->enabled,
            'description' => $this->description,
        ];
    }

    public static function create(string $name, mixed $value = null, array $options = [], bool $enabled = true, string $description = ''): self
    {
        return new self($name, $value, $options, $enabled, $description);
    }
}
```

**主要方法**：
- `validate()`: 验证参数数据（子类重写）
- `toArray()`: 转换为数组（子类重写）
- `toJson()`: 转换为JSON字符串
- `getData()`: 获取参数数据（基类方法）
- `setData()`: 设置参数数据（基类方法）

### HookResult
Hook返回值抽象类，所有返回值都必须继承此类并实现HookResultInterface

```php
use Modules\ABase\Hooks\Core\HookResult;

class DemoHookResult extends HookResult
{
    public function __construct(
        public readonly string $processedName = '',
        public readonly mixed $processedValue = null,
        public readonly array $appliedOptions = [],
        public readonly string $processingTime = '',
        public readonly string $description = ''
    ) {
        parent::__construct(true);
    }

    public static function success(string $processedName, mixed $processedValue, array $appliedOptions = [], string $processingTime = '', string $description = ''): static
    {
        return new static($processedName, $processedValue, $appliedOptions, $processingTime, $description);
    }

    public static function failure(array $errors = [], string $description = ''): static
    {
        $result = new static('', null, [], '', $description);
        foreach ($errors as $error) {
            $result->addError($error);
        }
        return $result;
    }

    public function getProcessedName(): string
    {
        return $this->processedName;
    }

    public function getProcessedValue(): mixed
    {
        return $this->processedValue;
    }

    public function getAppliedOptions(): array
    {
        return $this->appliedOptions;
    }

    public function getProcessingTime(): string
    {
        return $this->processingTime;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function toArray(): array
    {
        return array_merge(parent::toArray(), [
            'processedName' => $this->processedName,
            'processedValue' => $this->processedValue,
            'appliedOptions' => $this->appliedOptions,
            'processingTime' => $this->processingTime,
            'description' => $this->description,
        ]);
    }
}
```

**主要方法**：
- `isSuccess()`: 检查处理是否成功
- `isFailure()`: 检查是否处理失败
- `hasErrors()`: 检查是否有错误
- `getErrors()`: 获取错误信息
- `addError()`: 添加错误信息
- `getMessage()`: 获取处理消息
- `setMessage()`: 设置处理消息
- `getData()`: 获取结果数据（基类方法）
- `setData()`: 设置结果数据（基类方法）
- `toArray()`: 转换为数组（子类重写）

### HookManager
核心管理器，负责Hook处理器的管理、Hook的执行和调试

```php
use Modules\ABase\Hooks\Management\HookManager;

// Hook系统采用无注册模式，直接添加处理器
HookManager::add(\Modules\ABase\Hooks\Definitions\DemoHook::class, DemoHookHandler::class, $sort = 10);

// 应用过滤器Hook
// HookManager会自动为第一个处理器创建初始结果
// 注意：apply方法不需要验证Hook是否已注册，系统会自动处理
$result = HookManager::apply(\Modules\ABase\Hooks\Definitions\DemoHook::class, $parameter);
```

**主要方法**：
- `add()`: 添加Hook处理器（支持类名、闭包、可调用数组）
- `addHandlers()`: 批量添加处理器
- `removeHandler()`: 移除Hook处理器
- `removeHook()`: 移除Hook及其所有处理器
- `hasHandlers()`: 检查Hook是否有处理器
- `getHandlers()`: 获取Hook的所有处理器（按优先级排序）
- `apply()`: 应用Hook（无注册模式，直接执行）
- `enableDebug()`: 启用调试模式
- `disableDebug()`: 禁用调试模式
- `isDebugEnabled()`: 检查调试模式状态
- `getExecutionLog()`: 获取执行日志
- `clearExecutionLog()`: 清空执行日志
- `debug()`: 获取调试信息
- `clear()`: 清理所有数据
- `setMaxExecutionDepth()`: 设置最大执行深度
- `getMaxExecutionDepth()`: 获取最大执行深度
- `registerSubscriber()`: 注册Hook订阅者
- `registerSubscribers()`: 批量注册订阅者
- `removeSubscriber()`: 移除Hook订阅者
- `getRegisteredSubscribers()`: 获取所有已注册的订阅者
- `isSubscriberRegistered()`: 检查订阅者是否已注册
- `getSubscriber()`: 获取订阅者实例
- `clearSubscribers()`: 清理所有订阅者

**HookManager内部处理流程**：

```php
// HookManager::apply() 的内部逻辑示例
public function apply(string $hookClass, HookParameter $parameter): HookResult
{
    $handlers = $this->getHandlers($hookClass);

    if (empty($handlers)) {
        throw new \Exception("No handlers registered for hook: {$hookClass}");
    }

    // 为第一个处理器创建初始结果
    $initialResult = $this->createInitialResult($hookClass, $parameter);
    $currentResult = $initialResult;

    // 按优先级顺序执行所有处理器
    foreach ($handlers as $handler) {
        $currentResult = $handler->handle($parameter, $currentResult);
    }

    return $currentResult;
}

private function createInitialResult(string $hookClass, HookParameter $parameter): HookResult
{
    // 根据Hook定义创建对应的初始结果对象
    $resultClass = $hookClass::$return_class;
    return $resultClass::fromParameter($parameter);
}
```

### HookHandlerInterface
Hook处理器接口，所有Hook处理器都必须实现此接口

```php
use Modules\ABase\Hooks\Core\HookHandlerInterface;
use Modules\ABase\Hooks\Core\HookParameterInterface;
use Modules\ABase\Hooks\Core\HookResultInterface;

class DemoHookHandler implements HookHandlerInterface
{
    public static function handle(HookParameterInterface $parameter, HookResultInterface $result): HookResultInterface
    {
        // 处理逻辑：基于前一个处理器的结果继续处理
        $data = $parameter->toArray();

        // 处理数据
        $processedName = $data['name'] . '_processed';
        $processedValue = $data['value'] * 2; // 示例：值翻倍
        $appliedOptions = array_merge($data['options'] ?? [], [
            'handler' => static::class,
            'processed_at' => time()
        ]);

        // 返回新的处理结果
        return DemoHookResult::success($processedName, $processedValue, $appliedOptions, (string) time(), 'Processed by DemoHookHandler');
    }

    public static function shouldExecute(HookParameterInterface $parameter, HookResultInterface $result): bool
    {
        // 可以基于参数和结果决定是否执行
        $data = $parameter->toArray();
        return !empty($data['name']) && ($data['enabled'] ?? true); // 只有名称不为空且启用时才执行
    }

    public static function getPriority(): int
    {
        return 20; // 默认优先级
    }
}
```

### Hooks 助手类
提供更友好的API封装

```php
use Modules\ABase\Hooks\Management\Hooks;

// 添加处理器（使用Hook定义类）
// 注意：Hook系统采用无注册模式，直接使用即可
Hooks::add(\Modules\ABase\Hooks\Definitions\DemoHook::class, DemoHookHandler::class, $priority);

// 添加高优先级处理器
Hooks::addFirst(\Modules\ABase\Hooks\Definitions\DemoHook::class, HighPriorityHandler::class);

// 添加低优先级处理器
Hooks::addLast(\Modules\ABase\Hooks\Definitions\DemoHook::class, LowPriorityHandler::class);

// 批量添加处理器
Hooks::addMany(\Modules\ABase\Hooks\Definitions\DemoHook::class, [
    ['class' => Handler1::class, 'priority' => 5],
    ['class' => Handler2::class, 'priority' => 15]
]);

// 应用过滤器Hook（简单版本）
$result = Hooks::apply(\Modules\ABase\Hooks\Definitions\DemoHook::class, $parameter);

// 应用过滤器Hook（带初始结果）
$result = Hooks::applyWithResult(\Modules\ABase\Hooks\Definitions\DemoHook::class, $parameter, $initialResult);

// 移除处理器
Hooks::remove(\Modules\ABase\Hooks\Definitions\DemoHook::class, DemoHookHandler::class);

// 检查是否有处理器
$hasHandlers = Hooks::hasHandlers(\Modules\ABase\Hooks\Definitions\DemoHook::class);

// 获取处理器数量
$handlerCount = Hooks::countHandlers(\Modules\ABase\Hooks\Definitions\DemoHook::class);

// 获取处理器列表
$handlers = Hooks::getHandlers(\Modules\ABase\Hooks\Definitions\DemoHook::class);

// 调试和统计
Hooks::enableDebug();
Hooks::disableDebug();
$isDebugging = Hooks::isDebugging();
$debugInfo = Hooks::debug();
$executionLog = Hooks::getLog();
Hooks::clearLog();

// 执行深度控制
Hooks::setMaxDepth(50);
$maxDepth = Hooks::getMaxDepth();

// 订阅者管理
Hooks::subscribe(DemoHookSubscriber::class);
Hooks::subscribeMany([Subscriber1::class, Subscriber2::class]);
Hooks::unsubscribe(DemoHookSubscriber::class);
$subscribers = Hooks::getSubscribers();
$isSubscribed = Hooks::isSubscribed(DemoHookSubscriber::class);
$subscriber = Hooks::getSubscriber(DemoHookSubscriber::class);
Hooks::clearSubscribers();

// 清理所有数据
Hooks::clear();
```

## 核心钩子列表

### 通用钩子
- `MODULE_CONFIG_FILTER` - 模块配置过滤器，在模块配置加载时过滤配置数据
- `MODULE_ROUTES_FILTER` - 模块路由过滤器，在模块路由注册时过滤路由配置

### 控制器钩子
- `CONTROLLER_RESPONSE_FILTER` - 控制器响应过滤器，在控制器返回响应之前过滤响应数据
- `CONTROLLER_DATA_FILTER` - 控制器数据过滤器，在控制器传递数据给视图之前过滤

### 验证钩子
- `VALIDATION_RULES_FILTER` - 验证规则过滤器，在执行验证之前过滤验证规则
- `VALIDATION_DATA_FILTER` - 验证数据过滤器，在验证之前过滤输入数据
- `VALIDATION_ERRORS_FILTER` - 验证错误过滤器，在返回验证错误之前过滤错误消息

### 缓存钩子
- `CACHE_KEY_FILTER` - 缓存键过滤器，在生成缓存键时过滤
- `CACHE_VALUE_FILTER` - 缓存值过滤器，在设置或获取缓存值时过滤
- `CACHE_TAGS_FILTER` - 缓存标签过滤器，在设置缓存标签时过滤

### 统计钩子
- `STATISTICS_FILTER` - 统计数据过滤器，在返回统计数据之前过滤
- `STATISTICS_PARAMS_FILTER` - 统计参数过滤器，在计算统计数据之前过滤参数

### 数据钩子
- `DATA_INPUT_FILTER` - 数据输入过滤器，在数据入库之前过滤
- `DATA_OUTPUT_FILTER` - 数据输出过滤器，在数据输出之前过滤
- `DATA_SERIALIZATION_FILTER` - 数据序列化过滤器，在数据序列化之前过滤

### 系统钩子
- `ERROR_MESSAGE_FILTER` - 错误消息过滤器，在返回错误消息之前过滤
- `LOG_MESSAGE_FILTER` - 日志消息过滤器，在写入日志之前过滤日志消息
- `CONFIG_VALUE_FILTER` - 配置值过滤器，在获取配置值时过滤

## 使用示例

### 基本用法

```php
use Modules\ABase\Hooks\Definitions\DemoHook;
use Modules\ABase\Hooks\Parameters\DemoHookParameter;
use Modules\ABase\Hooks\Results\DemoHookResult;
use Modules\ABase\Hooks\Handlers\DemoHookHandler;
use Modules\ABase\Hooks\Management\Hooks;

// 1. Hook定义类已经存在（DemoHook）
// 使用现有的DemoHook定义类

// 2. 注册处理器（Hook系统采用无注册模式）
// 处理器已经在服务提供者中注册，这里仅作示例
Hooks::add(DemoHook::class, DemoHookHandler::class, 20);

// 3. 创建参数
$parameter = DemoHookParameter::create(
    name: 'test_content_filter',
    value: '<p><script>alert("xss")</script>原始内容</p>',
    options: ['context' => 'content_filter', 'source' => 'user_input'],
    enabled: true,
    description: '测试内容过滤Hook'
);

// 4. 应用Hook
$result = Hooks::apply(DemoHook::class, $parameter);

// 5. 检查结果
if ($result->isSuccess()) {
    echo "处理成功\n";
    echo "处理后名称: " . $result->getProcessedName() . "\n";
    echo "处理后值: " . $result->getProcessedValue() . "\n";
    echo "处理时间: " . $result->getProcessingTime() . "\n";
    echo "应用选项: " . json_encode($result->getAppliedOptions(), JSON_UNESCAPED_UNICODE) . "\n";
} else {
    echo "处理失败: " . implode(', ', $result->getErrors()) . "\n";
}

// 6. 启用调试模式查看详细过程
Hooks::enableDebug();
$result = Hooks::apply(DemoHook::class, $parameter);
$debugInfo = Hooks::debug();
print_r($debugInfo['execution_log']);
```

### 实际应用示例

```php
// 1. 内容过滤场景
class CommentContentFilterHook extends HookDefinition
{
    public string $description = '评论内容过滤器';

    public function __construct()
    {
        $this->parameter_class = CommentDataParameter::class;
        $this->return_class = CommentProcessResult::class;
        parent::__construct();
    }
}

class CommentContentHandler implements HookHandlerInterface
{
    public function handle(HookParameterInterface $parameter, HookResultInterface $result): HookResultInterface
    {
        $data = $result->toArray();
        $content = $data['content'] ?? '';

        // 清理恶意内容
        $cleanContent = strip_tags($content, '<p><br><a><strong>');
        $cleanContent = preg_replace('/\[spam\]/', '', $cleanContent);

        return CommentProcessResult::success($cleanContent, '内容清理完成');
    }

    public function shouldExecute(HookParameterInterface $parameter, HookResultInterface $result): bool
    {
        $data = $parameter->toArray();
        return !empty($data['content']); // 只有内容不为空时才执行
    }

    public function getPriority(): int
    {
        return 5; // 高优先级，先执行清理
    }
}

// 2. 数据验证场景
class UserDataValidatorHook extends HookDefinition
{
    public string $description = '用户数据验证器';

    public function __construct()
    {
        $this->parameter_class = UserDataParameter::class;
        $this->return_class = UserValidationResult::class;
        parent::__construct();
    }
}

class UserDataNormalizerHandler implements HookHandlerInterface
{
    public function handle(HookParameterInterface $parameter, HookResultInterface $result): HookResultInterface
    {
        $userData = $result->toArray();

        // 标准化用户数据
        $userData['email'] = strtolower(trim($userData['email'] ?? ''));
        $userData['username'] = preg_replace('/[^a-zA-Z0-9_]/', '', $userData['username'] ?? '');

        return UserValidationResult::success($userData, '数据标准化完成');
    }

    public function shouldExecute(HookParameterInterface $parameter, HookResultInterface $result): bool
    {
        $data = $parameter->toArray();
        return !empty($data['email']) || !empty($data['username']);
    }

    public function getPriority(): int
    {
        return 10;
    }
}

// 3. 系统管理场景（注意：系统管理类操作建议使用传统Event系统）
// 这里仅为示例，展示如何将传统动作钩子转换为Hook系统
class AdminMenuHook extends HookDefinition
{
    public string $description = '管理菜单扩展';

    public function __construct()
    {
        $this->parameter_class = AdminMenuParameter::class;
        $this->return_class = AdminMenuResult::class;
        parent::__construct();
    }
}

class CustomMenuHandler implements HookHandlerInterface
{
    public function handle(HookParameterInterface $parameter, HookResultInterface $result): HookResultInterface
    {
        $menuItems = $result->toArray()['menuItems'] ?? [];

        // 添加自定义菜单项
        $menuItems[] = [
            'title' => '自定义菜单',
            'uri' => '/custom',
            'icon' => 'fa fa-cog'
        ];

        return AdminMenuResult::success($menuItems, '菜单扩展完成');
    }

    public function shouldExecute(HookParameterInterface $parameter, HookResultInterface $result): bool
    {
        // 只在管理界面执行
        $data = $parameter->toArray();
        return $data['context'] === 'admin';
    }

    public function getPriority(): int
    {
        return 20; // 低优先级，在其他处理之后
    }
}

// 注册处理器
Hooks::add(CommentContentFilterHook::class, CommentContentHandler::class);
Hooks::add(UserDataValidatorHook::class, UserDataNormalizerHandler::class);
Hooks::add(AdminMenuHook::class, CustomMenuHandler::class);
```

### 优先级控制

```php
// 首先定义Hook定义类
class ProcessDataHook extends HookDefinition
{
    public static readonly string $name = 'process_data';
    public static readonly string $parameter_class = ProcessDataParameter::class;
    public static readonly string $return_class = ProcessDataResult::class;
    public string $description = '数据处理链';
}

// 创建不同优先级的处理器
class DataPreprocessor implements HookHandlerInterface
{
    public function handle(HookParameter $parameter, HookResult $result): HookResult
    {
        // 数据预处理
        $data = $result->getData();
        $preprocessedData = $this->preprocess($data);
        return ProcessDataResult::success($preprocessedData, '预处理完成');
    }

    public function getPriority(): int { return 5; } // 高优先级
    public function getDescription(): string { return '数据预处理器'; }
}

class DataProcessor implements HookHandlerInterface
{
    public function handle(HookParameter $parameter, HookResult $result): HookResult
    {
        // 主要处理逻辑
        $data = $result->getData();
        $processedData = $this->process($data);
        return ProcessDataResult::success($processedData, '主要处理完成');
    }

    public function getPriority(): int { return 10; } // 默认优先级
    public function getDescription(): string { return '数据主处理器'; }
}

class DataLogger implements HookHandlerInterface
{
    public function handle(HookParameter $parameter, HookResult $result): HookResult
    {
        // 后处理：日志记录
        $data = $result->getData();
        $this->logProcessing($data);
        return ProcessDataResult::success($data, '日志记录完成');
    }

    public function getPriority(): int { return 20; } // 低优先级
    public function getDescription(): string { return '数据日志记录器'; }
}

// 注册处理器（按优先级自动排序）
Hooks::add(ProcessDataHook::class, DataPreprocessor::class);  // 优先级 5
Hooks::add(ProcessDataHook::class, DataProcessor::class);     // 优先级 10
Hooks::add(ProcessDataHook::class, DataLogger::class);       // 优先级 20
```

### 条件Hook注册

**注意**：以下功能展示的是Hook系统的高级用法，在新设计中有更好的替代方案。

```php
// 条件注册Hook处理器
if (config('app.debug')) {
    // 调试模式下的Hook处理器
    class DebugHandler implements HookHandlerInterface
    {
        public function handle(HookParameter $parameter, HookResult $result): HookResult
        {
            // 添加调试信息
            $data = $result->getData();
            $data['debug_info'] = [
                'timestamp' => now(),
                'memory_usage' => memory_get_usage(),
                'execution_time' => microtime(true)
            ];

            return DebugResult::success($data, '调试信息已添加');
        }

        public function getPriority(): int { return 100; } // 最低优先级
        public function getDescription(): string { return '调试信息处理器'; }
    }

    // 只在调试模式下注册
    Hooks::add(SomeHook::class, DebugHandler::class);
}

// 对于一次性Hook，建议在处理器内部控制行为
class OneTimeSetupHandler implements HookHandlerInterface
{
    private static bool $hasRun = false;

    public function handle(HookParameter $parameter, HookResult $result): HookResult
    {
        if (self::$hasRun) {
            return $result; // 已经运行过，直接返回原结果
        }

        // 执行一次性初始化逻辑
        $this->performSetup($result->getData());
        self::$hasRun = true;

        return SetupResult::success($result->getData(), '初始化完成');
    }

    private function performSetup(array $data): void
    {
        // 初始化逻辑
    }

    public function getPriority(): int { return 1; } // 最高优先级
    public function getDescription(): string { return '一次性初始化处理器'; }
}
```

### 类方法钩子

```php
// 首先定义Hook定义类
class UserCreatedHook extends HookDefinition
{
    public static readonly string $name = 'user_created';
    public static readonly string $parameter_class = UserDataParameter::class;
    public static readonly string $return_class = UserProcessResult::class;
    public string $description = '用户创建处理器';
}

// 创建处理器类
class UserCreatedHandler implements HookHandlerInterface
{
    public function handle(HookParameter $parameter, HookResult $result): HookResult
    {
        $userData = $result->getUserData();

        // 处理用户创建后的业务逻辑
        $this->sendWelcomeEmail($userData['email']);
        $this->createUserProfile($userData['id']);

        return UserProcessResult::success($userData, '用户创建处理完成');
    }

    private function sendWelcomeEmail(string $email): void
    {
        // 发送欢迎邮件逻辑
    }

    private function createUserProfile(int $userId): void
    {
        // 创建用户档案逻辑
    }
}

// 注册处理器
Hooks::add(UserCreatedHook::class, UserCreatedHandler::class);
```

## 在模块中使用

### 1. 创建模块特定Hook定义类

```php
// Modules/YourModule/Hooks/ProductHooks.php
namespace Modules\YourModule\Hooks;

use Modules\ABase\Hooks\HookDefinition;
use Modules\YourModule\Parameters\ProductDataParameter;
use Modules\YourModule\Results\ProductProcessResult;

class ProductCreatedHook extends HookDefinition
{
    public static readonly string $name = 'product_created';
    public static readonly string $parameter_class = ProductDataParameter::class;
    public static readonly string $return_class = ProductProcessResult::class;
    public string $description = '产品创建处理器';
}

class ProductPriceFilterHook extends HookDefinition
{
    public static readonly string $name = 'product_price_filter';
    public static readonly string $parameter_class = ProductPriceParameter::class;
    public static readonly string $return_class = ProductPriceResult::class;
    public string $description = '产品价格过滤器';
}
```

### 2. 创建Hook处理器

```php
// Modules/YourModule/Hooks/Handlers/ProductHandlers.php
namespace Modules\YourModule\Hooks\Handlers;

use Modules\ABase\Hooks\HookHandlerInterface;
use Modules\ABase\Hooks\HookParameter;
use Modules\ABase\Hooks\HookResult;
use Modules\YourModule\Hooks\ProductHooks;
use Illuminate\Support\Facades\Log;

class ProductCreatedHandler implements HookHandlerInterface
{
    public function handle(HookParameter $parameter, HookResult $result): HookResult
    {
        $productData = $result->getProductData();

        // 记录产品创建日志
        Log::info("产品创建: {$productData['name']}");

        // 可以在这里添加其他处理逻辑，如发送通知、更新缓存等
        $this->updateProductCache($productData);
        $this->notifyAdminUsers($productData);

        return ProductProcessResult::success($productData, '产品创建处理完成');
    }

    private function updateProductCache(array $productData): void
    {
        // 更新产品缓存逻辑
    }

    private function notifyAdminUsers(array $productData): void
    {
        // 通知管理员逻辑
    }

    public function getPriority(): int { return 10; }
    public function getDescription(): string { return '产品创建处理器'; }
}

class ProductPriceHandler implements HookHandlerInterface
{
    public function handle(HookParameter $parameter, HookResult $result): HookResult
    {
        $priceData = $result->getPriceData();
        $price = $priceData['price'];
        $product = $priceData['product'];

        // 应用折扣、税费等计算
        $finalPrice = $this->calculateTax($price, $product);
        $finalPrice = $this->applyDiscount($finalPrice, $product);

        return ProductPriceResult::success($finalPrice, '价格计算完成');
    }

    private function calculateTax(float $price, array $product): float
    {
        return $price * 1.1; // 10% 税费
    }

    private function applyDiscount(float $price, array $product): float
    {
        // 根据产品类型应用折扣逻辑
        return $price * 0.95; // 5% 折扣示例
    }

    public function getPriority(): int { return 10; }
    public function getDescription(): string { return '产品价格处理器'; }
}
```

### 3. 在服务提供者中注册

```php
// Modules/YourModule/Providers/YourModuleServiceProvider.php
namespace Modules\YourModule\Providers;

use Illuminate\Support\ServiceProvider;
use Modules\YourModule\Hooks\ProductHooks;
use Modules\YourModule\Hooks\Handlers\ProductHandlers;

class YourModuleServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        // 注册Hook定义
        $productCreatedHook = new ProductCreatedHook();
        $productPriceFilterHook = new ProductPriceFilterHook();

        Hooks::define($productCreatedHook);
        Hooks::define($productPriceFilterHook);

        // 注册Hook处理器
        Hooks::add(ProductCreatedHook::class, ProductCreatedHandler::class);
        Hooks::add(ProductPriceFilterHook::class, ProductPriceHandler::class);
    }
}
```

## 最佳实践

### 1. 钩子命名规范
- 使用下划线分隔的小写字母
- 采用 `object_action` 或 `object_property_filter` 格式
- 模块特定钩子使用模块前缀

```php
// 好的命名
'user_registered'
'post_content_filter'
'product_price_calculated'

// 模块特定钩子
'demo5_post_published'
'shop_order_created'
```

### 2. 优先级使用
- 1-10: 核心功能、必需处理
- 11-20: 常规功能、标准处理
- 21-30: 可选功能、扩展处理
- 31+: 调试、日志、监控

### 3. 参数设计
- 动作钩子通常传递相关对象作为参数
- 过滤器钩子第一个参数总是要被过滤的值
- 使用类型提示确保参数安全

### 4. Hook处理器设计
- 处理器必须实现HookHandlerInterface接口
- 使用类而不是闭包，提高代码可维护性
- 每个处理器只负责单一职责
- 返回值必须符合Hook定义的返回类型
- 注册处理器时使用Hook定义类而不是字符串，提高类型安全

```php
// 好的处理器设计
class ContentSanitizerHandler implements HookHandlerInterface
{
    public function handle(HookParameter $parameter, HookResult $result): HookResult
    {
        // 单一职责：清理内容，基于前置结果继续处理
        $cleanContent = $this->sanitize($parameter->getContent());

        return ContentProcessResult::success($cleanContent);
    }

    private function sanitize(string $content): string
    {
        // 私有方法处理具体逻辑
        return strip_tags($content, '<p><br><strong>');
    }

    public function getPriority(): int
    {
        return 5; // 高优先级，先执行清理
    }

    public function getDescription(): string
    {
        return '内容清理处理器';
    }
}
```

### 5. 性能考虑
- 避免在钩子中执行耗时操作
- 考虑使用队列处理重任务
- 合理使用缓存减少重复计算

### 6. 调试技巧
```php
use Modules\ABase\Hooks\Management\HookManager;
use Modules\ABase\Hooks\Management\Hooks;

// 查看所有已注册的钩子
$debugInfo = HookManager::debug();
print_r($debugInfo);

// 或者使用助手类
$debugInfo = Hooks::debug();
print_r($debugInfo);

// 获取统计信息
$stats = Hooks::stats();
print_r($stats);

// 检查特定Hook处理器是否存在
if (Hooks::hasHandlers(MyHook::class)) {
    echo "Hook处理器已注册";
}

// 获取Hook的处理器信息
$handlerInfo = Hooks::getHandlerInfo(MyHook::class);
print_r($handlerInfo);

// 测试Hook系统
$testResults = Hooks::test();
print_r($testResults);

// 启用调试模式
Hooks::enableDebug();

// 获取执行日志
$executionLog = Hooks::getLog();
print_r($executionLog);

// 清空执行日志
Hooks::clearLog();

// 设置最大执行深度
Hooks::setMaxDepth(100);

// 获取最大执行深度
$maxDepth = Hooks::getMaxDepth();
echo "最大执行深度: {$maxDepth}";
```

## 管理命令

使用Artisan命令管理钩子系统：

```bash
# 测试钩子系统
php artisan hook:test
```

**Hook测试命令功能**：
- 启用调试模式并显示执行过程
- 创建DemoHook参数并验证
- 检查Hook处理器注册状态
- 执行完整的Hook处理流程（包括Handler和Subscriber）
- 显示详细的执行结果和调试信息
- 提供错误处理和异常报告

**测试输出示例**：
```
开始测试 Hook 系统...
✓ 已启用 Hook 调试模式
✓ 已创建 DemoHook 参数
  参数详情: {"name":"test_hook","value":42,"options":{"source":"HookTestCommand"},"enabled":true,"description":"测试DemoHook功能"}
✓ DemoHook 已注册处理器
  处理器数量: 2
正在执行 DemoHook...
✓ DemoHook 执行成功
  处理后名称: test_hook_processed_by_subscriber
  处理后值: 94
  处理时间: 1699123456
  应用选项: {"source":"HookTestCommand","handler":"DemoHookHandler","processed_at":1699123456,"subscriber":"DemoHookSubscriber","subscriber_processed_at":1699123456,"subscriber_note":"Subscriber added extra processing"}

=== Hook 系统调试信息 ===
已注册Hook数量: 1
调试模式: 启用
当前执行深度: 0
执行日志:
  - [2024-01-01 12:00:00] Static handler added: DemoHookHandler::handle for Modules\ABase\Hooks\Definitions\DemoHook with sort 20
  - [2024-01-01 12:00:00] Subscriber registered: DemoHookSubscriber with 1 hooks
  - [2024-01-01 12:00:00] Static handler executed: DemoHookHandler::handle in 2.50ms
  - [2024-01-01 12:00:00] Callable handler executed: DemoHookSubscriber::handleDemoHook in 1.80ms
  - [2024-01-01 12:00:00] Hook 'Modules\ABase\Hooks\Definitions\DemoHook' executed with 2 handlers
✓ Hook 系统测试完成
```

## 注意事项

1. **避免无限循环**: 钩子系统有内置防护机制，但仍需避免在钩子中触发相同钩子
2. **错误处理**: 钩子执行中的异常会被捕获并记录，不会中断主流程
3. **性能监控**: 在生产环境中监控钩子执行时间，避免性能问题
4. **版本兼容**: 钩子接口变更时需要考虑向后兼容性

## 扩展开发

### 创建自定义钩子管理器

```php
class CustomHookManager extends HookManager
{
    protected static function executeCallback($callback, array $args): mixed
    {
        // 自定义回调执行逻辑
        try {
            return parent::executeCallback($callback, $args);
        } catch (\Exception $e) {
            // 自定义错误处理（注意：这种系统级错误处理建议使用Event系统）
            // 这里仅为示例，实际开发中应该用Event或其他错误处理机制
            Log::error('Hook执行错误', [
                'error' => $e->getMessage(),
                'callback' => $callback
            ]);
            throw $e;
        }
    }
}
```

### Hook系统调试与监控

**重要提示**：以下示例展示的是Hook系统的底层监控功能，主要用于系统调试和开发。在实际应用中，建议使用Laravel的Event系统进行监控和日志记录。

```php
// Hook系统内部监控（仅用于调试）
class HookSystemMonitor
{
    public static function register(): void
    {
        // 注意：这类系统级监控更适合用Event系统实现
        // 这里仅为展示Hook系统的扩展能力

        // 在HookManager内部添加监控逻辑
        HookManager::setBeforeExecuteCallback(function($hookClass, $handler) {
            Log::debug("Hook执行开始", [
                'hook' => $hookClass,
                'handler' => $handler,
                'timestamp' => now()
            ]);
        });

        HookManager::setAfterExecuteCallback(function($hookClass, $handler, $result) {
            Log::debug("Hook执行完成", [
                'hook' => $hookClass,
                'handler' => $handler,
                'execution_time' => microtime(true),
                'result_type' => get_class($result)
            ]);
        });
    }
}

// 在开发环境中启用监控
if (app()->environment('local')) {
    HookSystemMonitor::register();
}
```

钩子系统为Laravel应用提供了强大的扩展能力，使模块化开发更加灵活和可维护。

## Hook订阅者机制

Hook系统提供了订阅者机制，允许开发者通过一个类来管理多个Hook处理器，类似于Laravel的事件订阅者模式。

### 订阅者接口

```php
use Modules\ABase\Hooks\Core\HookSubscriberInterface;

class MyHookSubscriber implements HookSubscriberInterface
{
    /**
     * 为订阅者注册Hook处理器
     *
     * @return array 订阅的Hook配置数组
     */
    public function subscribe(): array
    {
        return [
            \Modules\ABase\Hooks\Definitions\DemoHook::class => 'handleDemoHook',
            \Modules\YourModule\Hooks\PostCreatedHook::class => 'handlePostCreated',
        ];
    }
}
```

### 抽象订阅者基类

系统提供了`AbstractHookSubscriber`抽象基类，简化了订阅者的开发：

```php
use Modules\ABase\Hooks\Core\AbstractHookSubscriber;
use Modules\ABase\Hooks\Core\HookParameterInterface;
use Modules\ABase\Hooks\Core\HookResultInterface;
use Modules\ABase\Hooks\Parameters\DemoHookParameter;
use Modules\ABase\Hooks\Results\DemoHookResult;

class DemoHookSubscriber extends AbstractHookSubscriber
{
    /**
     * 为订阅者注册Hook处理器
     */
    public function subscribe(): array
    {
        return [
            \Modules\ABase\Hooks\Definitions\DemoHook::class => 'handleDemoHook',
        ];
    }

    /**
     * 处理DemoHook
     */
    public function handleDemoHook(HookParameterInterface $parameter, HookResultInterface $result): HookResultInterface
    {
        // 确保参数和结果类型正确
        if (! $parameter instanceof DemoHookParameter || ! $result instanceof DemoHookResult) {
            return $result;
        }

        // 获取前一个处理器的结果
        $processedName = $result->getProcessedName();
        $processedValue = $result->getProcessedValue();
        $appliedOptions = $result->getAppliedOptions();

        // 添加订阅者特有的处理逻辑
        $finalName = $processedName.'_by_subscriber';
        $finalValue = $processedValue + 10; // 增加10
        $finalOptions = array_merge($appliedOptions, [
            'subscriber' => static::class,
            'subscriber_processed_at' => time(),
            'subscriber_note' => 'Subscriber added extra processing',
        ]);

        // 创建新的结果对象
        if ($result->isSuccess()) {
            return DemoHookResult::success(
                $finalName,
                $finalValue,
                $finalOptions,
                (string) time(),
                'DemoHook processed by Handler and Subscriber'
            );
        } else {
            // 如果前一个处理器失败了，添加错误信息但不改变失败状态
            $result->addError('Processed by DemoHookSubscriber but previous handler failed');
            return $result;
        }
    }
}
```

### 注册订阅者

使用Hooks助手类注册订阅者：

```php
use Modules\ABase\Hooks\Management\Hooks;

// 注册单个订阅者（传入类名）
Hooks::subscribe(DemoHookSubscriber::class);

// 或者传入订阅者实例
$subscriber = new DemoHookSubscriber($logger);
Hooks::subscribe($subscriber);

// 批量注册订阅者
$subscribers = [
    DemoHookSubscriber::class,
    AnotherSubscriber::class,
];
Hooks::subscribeMany($subscribers);
```

**注意**：
- 订阅者只需要实现`subscribe()`方法
- HookManager会自动处理订阅者的注册和Hook处理器的创建
- 订阅者方法会被自动包装为可调用数组处理器
- 订阅者支持依赖注入，会在创建时通过Laravel容器解析
- 订阅者默认优先级为1，通常比Handler优先级低，在Handler之后执行

### 订阅者管理

```php
use Modules\ABase\Hooks\Management\Hooks;

// 获取所有已注册的订阅者
$subscribers = Hooks::getSubscribers();

// 检查订阅者是否已注册
$isSubscribed = Hooks::isSubscribed(DemoHookSubscriber::class);

// 获取订阅者实例
$subscriber = Hooks::getSubscriber(DemoHookSubscriber::class);

// 取消订阅
Hooks::unsubscribe(DemoHookSubscriber::class);

// 清理所有订阅者
Hooks::clearSubscribers();
```

### 订阅者 vs 传统处理器

| 特性 | 订阅者 | 传统处理器 |
|------|----------|-----------|
| **组织方式** | 一个类管理多个处理器 | 每个处理器独立类 |
| **代码复用** | 高，共享方法和属性 | 低，每个处理器独立 |
| **生命周期管理** | 统一初始化和清理 | 分散管理 |
| **配置复杂度** | 简单，集中配置 | 较高，分散配置 |
| **调试便利性** | 高，集中调试 | 中，分散调试 |

### 订阅者最佳实践

1. **单一职责原则**：每个订阅者专注于一个业务领域
2. **命名规范**：使用描述性的类名和方法名
3. **错误处理**：在处理器中妥善处理异常
4. **性能考虑**：避免在处理器中执行耗时操作
5. **测试友好**：编写单元测试验证订阅者逻辑

```php
// 好的订阅者设计
class UserContentSubscriber extends AbstractHookSubscriber
{
    public function subscribe(HookManager $hookManager): array
    {
        return [
            'user_content_filter' => 'filterUserContent',
            'user_data_validator' => 'validateUserData',
        ];
    }

    public function filterUserContent(HookParameterInterface $parameter, HookResultInterface $result): HookResultInterface
    {
        try {
            $content = $parameter->getContent();
            $filtered = $this->applyFilters($content);
            
            $result->setData(['content' => $filtered]);
            return $result;
        } catch (\Exception $e) {
            $result->setSuccess(false);
            $result->setMessage('内容过滤失败: ' . $e->getMessage());
            return $result;
        }
    }

    private function applyFilters(string $content): string
    {
        // 过滤逻辑实现
        return $content;
    }
}
```

### 实际应用示例

参考`Modules/ABase/Hooks/Subscribers/UserContentSubscriber.php`查看完整的订阅者实现示例。

#### 基本订阅者示例

```php
use Modules\ABase\Hooks\Core\AbstractHookSubscriber;
use Modules\ABase\Hooks\Core\HookParameterInterface;
use Modules\ABase\Hooks\Core\HookResultInterface;
use Modules\ABase\Hooks\Management\HookManager;

class DemoHookSubscriber extends AbstractHookSubscriber
{
    /**
     * 为订阅者注册Hook处理器
     */
    public function subscribe(HookManager $hookManager): array
    {
        return [
            // 使用DemoHook作为示例
            \Modules\ABase\Hooks\Definitions\DemoHook::class => 'handleDemoHook',
        ];
    }

    /**
     * 处理DemoHook
     */
    public function handleDemoHook(HookParameterInterface $parameter, HookResultInterface $result): HookResultInterface
    {
        // 获取传入的数据
        $data = $parameter->toArray();
        
        // 执行数据处理逻辑
        $processedData = $this->processData($data);
        
        // 记录处理日志
        \Log::info('DemoHook处理完成', [
            'original_data' => $data,
            'processed_data' => $processedData,
            'subscriber' => static::class,
            'process_time' => now()->toDateTimeString()
        ]);
        
        // 更新结果
        $result->setData($processedData);
        $result->setSuccess(true);
        $result->setMessage('DemoHook处理完成');
        
        return $result;
    }

    /**
     * 数据处理方法
     */
    private function processData(array $data): array
    {
        // 示例处理逻辑：添加时间戳和处理标记
        $processedData = $data;
        $processedData['processed_at'] = now()->toDateTimeString();
        $processedData['processed_by'] = static::class;
        $processedData['processed'] = true;
        
        // 如果有content字段，进行简单处理
        if (isset($processedData['content'])) {
            $processedData['content'] = trim($processedData['content']);
            $processedData['content_length'] = strlen($processedData['content']);
        }
        
        return $processedData;
    }
}
```

#### 注册订阅者

```php
use Modules\ABase\Hooks\Management\Hooks;

// 在服务提供者中注册订阅者
class MyServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        // 注册订阅者
        $subscriber = new DemoHookSubscriber();
        Hooks::subscribe($subscriber);
    }
}
```

#### 订阅者 vs 传统处理器对比

| 特性 | 订阅者 | 传统处理器 |
|------|----------|-----------|
| **代码组织** | 一个类管理多个处理器 | 每个处理器独立类 |
| **复用性** | 高，共享方法和属性 | 低，每个处理器独立 |
| **维护性** | 统一管理，易于维护 | 分散管理，维护复杂 |
| **测试友好** | 集中测试，便于模拟 | 分散测试，复杂度高 |
| **适用场景** | 相关处理器集中管理 | 独立处理器，适合简单场景 |

#### 最佳实践建议

1. **单一职责**：每个订阅者专注于一个业务领域
2. **命名规范**：使用描述性的类名和方法名
3. **错误处理**：在处理器中妥善处理异常
4. **性能考虑**：避免在处理器中执行耗时操作
5. **文档完整**：为订阅者编写完整的PHPDoc注释

```php
/**
 * 用户内容订阅者
 *
 * 负责处理用户相关的内容Hook，包括内容过滤、数据验证等
 *
 * @package Modules\ABase\Hooks\Subscribers
 */
class UserContentSubscriber extends AbstractHookSubscriber
{
    /**
     * {@inheritdoc}
     */
    public function subscribe(HookManager $hookManager): array
    {
        return [
            \Modules\ABase\Hooks\Definitions\DemoHook::class => 'handleUserContent',
        ];
    }

    /**
     * 处理用户内容相关Hook
     *
     * @param HookParameterInterface $parameter Hook参数
     * @param HookResultInterface $result Hook结果
     * @return HookResultInterface 处理后的结果
     */
    public function handleUserContent(HookParameterInterface $parameter, HookResultInterface $result): HookResultInterface
    {
        $data = $parameter->toArray();
        
        // 根据数据类型进行不同处理
        if (isset($data['type'])) {
            switch ($data['type']) {
                case 'content_filter':
                    return $this->handleContentFilter($parameter, $result);
                case 'data_validation':
                    return $this->handleDataValidation($parameter, $result);
                default:
                    return $this->handleDefault($parameter, $result);
            }
        }
        
        return $this->handleDefault($parameter, $result);
    }

    /**
     * 处理内容过滤
     */
    private function handleContentFilter(HookParameterInterface $parameter, HookResultInterface $result): HookResultInterface
    {
        $content = $parameter->getContent() ?? '';
        $filteredContent = $this->sanitizeContent($content);
        
        $result->setData(['content' => $filteredContent]);
        $result->setSuccess(true);
        $result->setMessage('内容过滤完成');
        
        return $result;
    }

    /**
     * 处理数据验证
     */
    private function handleDataValidation(HookParameterInterface $parameter, HookResultInterface $result): HookResultInterface
    {
        $data = $parameter->toArray();
        $validation = $this->validateData($data);
        
        if ($validation['valid']) {
            $result->setData(['data' => $data, 'validated' => true]);
            $result->setSuccess(true);
            $result->setMessage('数据验证通过');
        } else {
            $result->setData(['data' => $data, 'validated' => false, 'errors' => $validation['errors']]);
            $result->setSuccess(false);
            $result->setMessage('数据验证失败');
        }
        
        return $result;
    }

    /**
     * 默认处理器
     */
    private function handleDefault(HookParameterInterface $parameter, HookResultInterface $result): HookResultInterface
    {
        $data = $parameter->toArray();
        $processedData = $this->addMetadata($data);
        
        $result->setData($processedData);
        $result->setSuccess(true);
        $result->setMessage('默认处理完成');
        
        return $result;
    }

    /**
     * 内容清理
     */
    private function sanitizeContent(string $content): string
    {
        return strip_tags($content, '<p><br><strong><em>');
    }

    /**
     * 数据验证
     */
    private function validateData(array $data): array
    {
        $errors = [];
        
        if (empty($data['title'] ?? '')) {
            $errors[] = '标题不能为空';
        }
        
        return [
            'valid' => empty($errors),
            'errors' => $errors
        ];
    }

    /**
     * 添加元数据
     */
    private function addMetadata(array $data): array
    {
        $data['processed_at'] = now()->toDateTimeString();
        $data['processed_by'] = static::class;
        return $data;
    }
}
```
