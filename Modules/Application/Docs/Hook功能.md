# Hook 系统功能实现原理

> ABase 模块的 Hook 系统完整技术文档

---

## 一、系统概述

### 什么是 Hook 系统？

Hook 系统是一个**数据过滤管道框架**，采用链式处理器模式，用于在数据处理流程中插入可扩展的处理逻辑。

### 核心设计理念

```
输入数据 → 多个处理器链式处理 → 输出结果
```

类似于 Linux 管道命令：`data | processor1 | processor2 | result`

### 主要特性

- **强类型约束**：参数和返回值类型安全
- **链式处理**：多个处理器按优先级顺序执行
- **灵活控制**：条件执行、优先级排序、单处理器模式
- **易于调试**：详细的执行日志和调试信息
- **高性能**：静态方法调用，无实例化开销
- **可扩展**：支持订阅者模式批量注册处理器

---

## 二、核心架构

### 架构图

```
┌─────────────────────────────────────────────────────┐
│                   Hooks 助手类                        │
│              (用户友好的 API 接口)                     │
└─────────────────┬───────────────────────────────────┘
                  │
                  ↓
┌─────────────────────────────────────────────────────┐
│                  HookManager                          │
│              (核心管理引擎)                            │
│  - Hook 注册验证                                      │
│  - 处理器管理                                         │
│  - 执行调度                                           │
│  - 调试日志                                           │
└─────────────────┬───────────────────────────────────┘
                  │
                  ↓
┌─────────────────────────────────────────────────────┐
│            核心组件 (Core 层)                         │
├─────────────────┬───────────────────────────────────┤
│  HookDefinition  │  HookParameter   HookResult      │
│  (Hook 定义)     │  (参数封装)       (结果封装)      │
│                  │                                   │
│  HookHandlerInterface                               │
│  (处理器接口)                                        │
└─────────────────┴───────────────────────────────────┘
```

### 文件结构

```
Modules/ABase/Hooks/
├── Core/                      # 核心组件层
│   ├── HookDefinition.php     # Hook 定义基类
│   ├── HookParameter.php      # 参数基类
│   ├── HookResult.php         # 结果基类
│   ├── HookHandlerInterface.php # 处理器接口
│   ├── HookParameterInterface.php # 参数接口
│   ├── HookResultInterface.php    # 结果接口
│   └── HookSubscriberInterface.php # 订阅者接口
│
├── Management/                # 管理层
│   ├── HookManager.php        # 核心管理器
│   ├── Hooks.php              # 用户助手类
│   └── HookHandlerProxy.php   # 处理器代理
│
├── Definitions/               # Hook 定义
│   └── DemoHook.php           # 示例 Hook
│
├── Parameters/                # 参数类
│   └── DemoHookParameter.php  # 示例参数
│
├── Results/                   # 结果类
│   └── DemoHookResult.php     # 示例结果
│
├── Handlers/                  # 处理器
│   └── DemoHookHandler.php    # 示例处理器
│
└── Subscribers/               # 订阅者
    └── DemoHookSubscriber.php # 示例订阅者
```

---

## 三、核心组件详解

### 1. HookDefinition（Hook 定义）

**文件位置**：`Modules/ABase/Hooks/Core/HookDefinition.php`

**作用**：定义 Hook 的契约规范，强制参数和返回值的类型约束。

#### 关键属性

```php
abstract class HookDefinition {
    public readonly string $parameter_class;  // 规定参数类型
    public readonly string $return_class;     // 规定返回值类型
    public string $description;               // Hook 描述
    public bool $is_single_processor = false; // 单处理器模式标记
}
```

#### 核心功能

**类型验证**（构造函数中自动验证）：

```php
protected function validateDefinition(): void {
    // 验证参数类存在
    if (!class_exists($this->parameter_class)) {
        throw new InvalidArgumentException("Parameter class does not exist");
    }

    // 验证参数类继承关系
    if (!is_subclass_of($this->parameter_class, HookParameter::class)) {
        throw new InvalidArgumentException("Parameter class must extend HookParameter");
    }

    // 验证返回值类
    if (!class_exists($this->return_class)) {
        throw new InvalidArgumentException("Return class does not exist");
    }

    if (!is_subclass_of($this->return_class, HookResult::class)) {
        throw new InvalidArgumentException("Return class must extend HookResult");
    }
}
```

**工厂方法**：

```php
// 创建参数实例
public function createParameter(mixed ...$args): HookParameter {
    $parameterClass = $this->parameter_class;
    return new $parameterClass(...$args);
}

// 创建结果实例
public function createResult(mixed ...$args): HookResult {
    $resultClass = $this->return_class;
    return new $resultClass(...$args);
}

// 创建初始成功结果（用于处理器链的起点）
public function createSuccessResult(mixed $data = [], string $message = ''): HookResult {
    // 使用反射自动填充默认值
}
```

**单处理器支持**：

```php
// 检查是否为单处理器模式
public function isSingleProcessor(): bool {
    return $this->is_single_processor;
}

// 验证单处理器结果是否有效
public function isValidSingleProcessorResult(HookResult $result): bool {
    return $result->isProcessed();
}
```

---

### 2. HookParameter（参数基类）

**文件位置**：`Modules/ABase/Hooks/Core/HookParameter.php`

**作用**：封装输入数据，提供统一的参数接口和验证机制。

#### 关键特性

```php
abstract class HookParameter implements HookParameterInterface, JsonSerializable {
    public function __construct() {
        $this->validate();  // 构造时自动验证
    }

    protected function validate(): void {}  // 子类实现验证逻辑
    public function toArray(): array {}     // 转换为数组
}
```

#### 实现要点

**自动验证机制**：

- 构造函数中调用 `validate()` 方法
- 子类重写 `validate()` 实现具体验证逻辑
- 验证失败抛出 `InvalidArgumentException`

**数据转换支持**：

```php
// 转换为数组（处理器访问数据的主要方式）
public function toArray(): array {
    return [];
}

// JSON 序列化支持
public function jsonSerialize(): array {
    return $this->toArray();
}

public function toJson(int $options = 0): string {
    return json_encode($this, $options);
}
```

#### 实际示例

```php
class DemoHookParameter extends HookParameter {
    public function __construct(
        public readonly string $name = '',
        public readonly mixed $value = null,
        public readonly array $options = []
    ) {
        parent::__construct();  // 触发自动验证
    }

    protected function validate(): void {
        if (empty($this->name)) {
            throw new InvalidArgumentException('名称不能为空');
        }
    }

    public function toArray(): array {
        return [
            'name' => $this->name,
            'value' => $this->value,
            'options' => $this->options,
        ];
    }

    // 静态工厂方法
    public static function create(string $name, mixed $value = null, array $options = []): self {
        return new self($name, $value, $options);
    }
}
```

---

### 3. HookResult（结果基类）

**文件位置**：`Modules/ABase/Hooks/Core/HookResult.php`

**作用**：封装输出数据，管理处理状态、错误信息和处理追踪。

#### 核心属性

```php
abstract class HookResult implements HookResultInterface, JsonSerializable {
    protected bool $success = true;      // 成功标识
    protected array $errors = [];        // 错误列表
    protected string $message = '';      // 处理消息
    protected bool $processed = false;   // 处理标识（单处理器模式）
    protected string $processor = '';    // 处理器标识（调试追踪）
}
```

#### 状态管理

**成功状态**：

```php
public function isSuccess(): bool {
    return $this->success;
}

public function setSuccess(bool $success): self {
    $this->success = $success;
    return $this;  // 支持链式调用
}
```

**错误管理**：

```php
public function hasErrors(): bool {
    return !empty($this->errors);
}

public function getErrors(): array {
    return $this->errors;
}

public function addError(string $error): self {
    $this->errors[] = $error;
    $this->success = false;  // 自动设置失败状态
    return $this;
}

public function addErrors(array $errors): self {
    foreach ($errors as $error) {
        $this->addError((string) $error);
    }
    return $this;
}
```

**处理追踪**（单处理器模式关键）：

```php
public function isProcessed(): bool {
    return $this->processed;
}

public function setProcessed(bool $processed): self {
    $this->processed = $processed;
    return $this;
}

public function getProcessor(): string {
    return $this->processor;
}

public function setProcessor(string $processor): self {
    $this->processor = $processor;
    return $this;
}

// 便捷方法
public function markAsProcessed(string $processor = ''): self {
    $this->processed = true;
    if (!empty($processor)) {
        $this->processor = $processor;
    }
    return $this;
}
```

#### 实际示例

```php
class DemoHookResult extends HookResult {
    public function __construct(
        public readonly string $processedName = '',
        public readonly mixed $processedValue = null,
        public readonly array $metadata = []
    ) {
        parent::__construct(true);  // 初始状态为成功
    }

    public static function success(string $name, mixed $value, array $metadata = []): static {
        return new static($name, $value, $metadata);
    }

    public function toArray(): array {
        return [
            'success' => $this->success,
            'errors' => $this->errors,
            'message' => $this->message,
            'processedName' => $this->processedName,
            'processedValue' => $this->processedValue,
            'metadata' => $this->metadata,
        ];
    }
}
```

---

### 4. HookHandlerInterface（处理器接口）

**文件位置**：`Modules/ABase/Hooks/Core/HookHandlerInterface.php`

**作用**：定义处理器必须实现的三个核心方法。

#### 接口定义

```php
interface HookHandlerInterface {
    // 1. 核心处理方法
    public static function handle(
        HookParameterInterface $parameter,
        HookResultInterface $result
    ): HookResultInterface;

    // 2. 优先级定义（1-100，越小越早执行）
    public static function getPriority(): int;

    // 3. 条件执行判断
    public static function shouldExecute(
        HookParameterInterface $parameter,
        HookResultInterface $result
    ): bool;
}
```

#### 方法详解

**handle() - 核心处理方法**：

```php
/**
 * 处理 Hook 的核心逻辑
 *
 * @param  HookParameterInterface  $parameter  Hook 参数（输入数据）
 * @param  HookResultInterface  $result  前一个处理器的输出结果
 * @return HookResultInterface 当前处理器处理后的新结果
 *
 * 关键：接收前一个结果，返回新结果（链式传递）
 */
public static function handle(
    HookParameterInterface $parameter,
    HookResultInterface $result
): HookResultInterface;
```

**getPriority() - 优先级定义**：

```php
/**
 * 获取处理器优先级
 *
 * 数值越小优先级越高，执行顺序越靠前
 * 建议范围：1-50
 * - 1-10: 核心功能、必需处理（如验证、清理）
 * - 11-20: 常规功能、标准处理（如转换、计算）
 * - 21-30: 可选功能、扩展处理（如日志、通知）
 * - 31+: 调试、监控、统计
 *
 * @return int 优先级数值
 */
public static function getPriority(): int;
```

**shouldExecute() - 条件执行**：

```php
/**
 * 检查处理器是否应该执行
 *
 * 可以基于参数或其他条件判断是否应该执行此处理器
 * 返回 false 时，HookManager 会跳过此处理器
 *
 * 用途：
 * - 验证前置条件
 * - 检查数据完整性
 * - 性能优化（避免无意义处理）
 *
 * @param  HookParameterInterface  $parameter  Hook 参数
 * @param  HookResultInterface  $result  当前结果
 * @return bool 是否应该执行
 */
public static function shouldExecute(
    HookParameterInterface $parameter,
    HookResultInterface $result
): bool;
```

#### 实际示例

```php
class DemoHookHandler implements HookHandlerInterface {
    private static string $defaultPrefix = 'demo';
    private static int $multiplier = 2;

    public static function handle(
        HookParameterInterface $parameter,
        HookResultInterface $result
    ): HookResultInterface {
        // 获取参数数据
        $data = $parameter->toArray();

        // 处理逻辑
        $processedName = self::$defaultPrefix . '_' . $data['name'];
        $processedValue = is_numeric($data['value'])
            ? $data['value'] * self::$multiplier
            : $data['value'];

        // 返回新结果
        return DemoHookResult::success($processedName, $processedValue, [
            'handler' => static::class,
            'original_name' => $data['name'],
            'original_value' => $data['value'],
            'multiplier' => self::$multiplier,
        ]);
    }

    public static function getPriority(): int {
        return 10;  // 中等优先级
    }

    public static function shouldExecute(
        HookParameterInterface $parameter,
        HookResultInterface $result
    ): bool {
        $data = $parameter->toArray();
        return !empty($data['name']);  // 只有有名称时才执行
    }
}
```

---

## 四、核心管理器实现

### HookManager（核心引擎）

**文件位置**：`Modules/ABase/Hooks/Management/HookManager.php`

**作用**：Hook 系统的"大脑"，管理所有 Hook 的注册、处理器排序和执行。

#### 关键数据结构

```php
// Hook 注册表（存储 Hook 定义实例）
private static array $registeredHooks = [];
// 结构: [hookClassName => HookDefinition实例]

// 处理器存储（按优先级分组）
private static array $handlers = [];
// 结构: [hookClass => [priority => [handlerCallable, ...]]]

// Hook 订阅者存储
private static array $subscribers = [];
// 结构: [subscriberClass => subscriberInstance]

// 执行日志
private static array $executionLog = [];

// 调试模式
private static bool $debugMode = false;

// 最大执行深度（防止无限循环）
private static int $maxExecutionDepth = 50;

// 当前执行深度
private static int $currentExecutionDepth = 0;
```

---

#### 核心 API

**1. Hook 注册**

```php
/**
 * 注册 Hook 定义
 *
 * 只有注册过的 Hook 才能添加处理器和执行
 *
 * @param  string  $hookClass  Hook 定义类名
 * @throws InvalidArgumentException 当 Hook 类不存在或不继承 HookDefinition
 */
public static function registerHook(string $hookClass): void {
    // 验证类存在性
    if (!class_exists($hookClass)) {
        throw new InvalidArgumentException("Hook class '{$hookClass}' does not exist");
    }

    // 验证继承关系
    if (!is_subclass_of($hookClass, HookDefinition::class)) {
        throw new InvalidArgumentException("Hook class '{$hookClass}' must extend HookDefinition");
    }

    // 创建实例并存储
    self::$registeredHooks[$hookClass] = new $hookClass;

    if (self::$debugMode) {
        self::logDebug("Hook registered: {$hookClass}");
    }
}
```

**关键点**：
- 强制注册验证机制
- 自动创建 Hook 定义实例
- 验证类型约束

---

**2. 处理器添加**

```php
/**
 * 添加 Hook 处理器
 *
 * 支持多种处理器类型：类名、闭包、可调用数组
 *
 * @param  string  $hookClass  Hook 定义类名
 * @param  string|Closure|array  $handler  处理器
 * @param  int  $sort  执行排序值（1-100），越小越早执行
 */
public static function add(string $hookClass, string|Closure|array $handler, int $sort = 10): void {
    // 1. 验证 Hook 已注册
    self::validateHookRegistered($hookClass);

    // 2. 验证排序值范围
    if ($sort < 1 || $sort > 100) {
        throw new InvalidArgumentException("Sort value must be between 1 and 100");
    }

    // 3. 初始化存储结构
    if (!isset(self::$handlers[$hookClass])) {
        self::$handlers[$hookClass] = [];
    }
    if (!isset(self::$handlers[$hookClass][$sort])) {
        self::$handlers[$hookClass][$sort] = [];
    }

    // 4. 处理不同类型的处理器
    if ($handler instanceof Closure) {
        // 闭包处理器直接存储
        self::$handlers[$hookClass][$sort][] = $handler;
    } elseif (is_array($handler) && count($handler) === 2) {
        // 可调用数组 [$object, 'method']
        self::$handlers[$hookClass][$sort][] = $handler;
    } elseif (is_string($handler)) {
        // 类处理器 - 转换为静态调用数组
        if (!class_exists($handler)) {
            throw new InvalidArgumentException("Handler class '{$handler}' does not exist");
        }
        if (!is_subclass_of($handler, HookHandlerInterface::class)) {
            throw new InvalidArgumentException("Handler must implement HookHandlerInterface");
        }
        // 统一转换为 [$handler, 'handle'] 格式
        self::$handlers[$hookClass][$sort][] = [$handler, 'handle'];
    }
}
```

**关键设计**：
- **强制注册验证**：必须先注册 Hook 才能添加处理器
- **优先级分组**：处理器按优先级分组存储，便于后续排序
- **统一格式**：所有处理器转换为可调用数组格式

---

**3. Hook 执行（核心流程）**

```php
/**
 * 应用 Hook - 执行所有处理器
 *
 * @param  string  $hookClass  Hook 类名
 * @param  HookParameter  $parameter  Hook 参数
 * @param  HookResult|null  $initialResult  初始结果（可选）
 * @return HookResult 最终结果
 */
public static function apply(
    string $hookClass,
    HookParameter $parameter,
    ?HookResult $initialResult = null
): HookResult {
    // 1. 验证 Hook 已注册
    self::validateHookRegistered($hookClass);

    // 2. 检查执行深度（防止无限循环）
    if (++self::$currentExecutionDepth > self::$maxExecutionDepth) {
        throw new Exception('Maximum hook execution depth exceeded');
    }

    try {
        // 3. 获取处理器列表（已按优先级排序）
        $handlers = self::getHandlers($hookClass);

        if (empty($handlers)) {
            throw new InvalidArgumentException("No handlers registered for hook: {$hookClass}");
        }

        // 4. 获取 Hook 定义（检查单处理器模式）
        $hookDefinition = self::getRegisteredHook($hookClass);
        $isSingleProcessor = $hookDefinition->isSingleProcessor();

        // 5. 创建初始结果（如果未提供）
        if ($initialResult === null) {
            $initialResult = $hookDefinition->createSuccessResult([], '');
        }

        $currentResult = $initialResult;
        $executedHandlers = [];
        $hasValidResult = false;  // 单处理器模式标记

        // 6. 链式执行处理器
        foreach ($handlers as $handlerData) {
            // 单处理器模式：已有有效结果则停止
            if ($isSingleProcessor && $hasValidResult) {
                if (self::$debugMode) {
                    self::logDebug("Single processor hook stopped: valid result obtained");
                }
                break;
            }

            try {
                // 记录执行开始时间（调试模式）
                if (self::$debugMode) {
                    $startTime = microtime(true);
                }

                // 处理器类型判断
                if ($handlerData['type'] === 'closure') {
                    // 闭包处理器
                    $handler = $handlerData['handler'];
                    $currentResult = $handler($parameter, $currentResult);

                } elseif ($handlerData['type'] === 'callable') {
                    // 可调用数组处理器
                    $handler = $handlerData['handler'];
                    $callable = $handlerData['callable'];
                    $method = $handlerData['method'];

                    // 静态类处理器 - 检查 shouldExecute
                    if (is_string($callable) && is_subclass_of($callable, HookHandlerInterface::class)) {
                        if (!$callable::shouldExecute($parameter, $currentResult)) {
                            if (self::$debugMode) {
                                self::logDebug("Handler skipped (shouldExecute returned false)");
                            }
                            continue;  // 跳过此处理器
                        }
                    }

                    // 执行处理器
                    $currentResult = $handler($parameter, $currentResult);
                    $executedHandlers[] = $callable . '::' . $method;
                }

                // 单处理器模式：检查结果是否有效
                if ($isSingleProcessor && $hookDefinition->isValidSingleProcessorResult($currentResult)) {
                    $hasValidResult = true;
                    if (self::$debugMode) {
                        self::logDebug("Single processor hook found valid result");
                    }
                }

                // 记录执行时间（调试模式）
                if (self::$debugMode) {
                    $executionTime = (microtime(true) - $startTime) * 1000;
                    self::logDebug("Handler executed in " . number_format($executionTime, 2) . 'ms');
                }

            } catch (Exception $e) {
                // 错误容错：记录错误但不中断流程
                $errorMessage = "Handler execution failed: " . $e->getMessage();
                if (self::$debugMode) {
                    self::logDebug($errorMessage);
                }
                $currentResult->addError($errorMessage);
            }
        }

        // 7. 记录执行统计
        if (self::$debugMode) {
            $debugMessage = "Hook '{$hookClass}' executed with " . count($executedHandlers) . ' handlers';
            if ($isSingleProcessor) {
                $debugMessage .= ' (single processor mode';
                $debugMessage .= $hasValidResult ? ', valid result obtained)' : ', no valid result)';
            }
            self::logDebug($debugMessage);
        }

        self::$currentExecutionDepth--;

        // 8. 返回最终结果
        return $currentResult;

    } catch (Exception $e) {
        self::$currentExecutionDepth--;
        throw $e;
    }
}
```

---

**4. 处理器排序**

```php
/**
 * 获取 Hook 的所有处理器（按优先级排序）
 *
 * @param  string  $hookClass  Hook 类名
 * @return array 处理器数组，按优先级排序
 */
public static function getHandlers(string $hookClass): array {
    if (!isset(self::$handlers[$hookClass])) {
        return [];
    }

    // 按优先级排序（ksort 升序排列）
    ksort(self::$handlers[$hookClass]);

    $result = [];
    foreach (self::$handlers[$hookClass] as $priority => $handlers) {
        foreach ($handlers as $handler) {
            if ($handler instanceof Closure) {
                $result[] = [
                    'type' => 'closure',
                    'handler' => $handler,
                    'priority' => $priority,
                ];
            } elseif (is_array($handler) && count($handler) === 2) {
                $result[] = [
                    'type' => 'callable',
                    'handler' => $handler,
                    'priority' => $priority,
                    'callable' => $handler[0],
                    'method' => $handler[1],
                ];
            }
        }
    }

    return $result;
}
```

---

**5. 调试支持**

```php
/**
 * 启用调试模式
 */
public static function enableDebug(): void {
    self::$debugMode = true;
}

/**
 * 禁用调试模式
 */
public static function disableDebug(): void {
    self::$debugMode = false;
}

/**
 * 记录调试信息
 */
private static function logDebug(string $message): void {
    $timestamp = date('Y-m-d H:i:s');
    self::$executionLog[] = "[{$timestamp}] {$message}";
}

/**
 * 获取执行日志
 */
public static function getExecutionLog(): array {
    return self::$executionLog;
}

/**
 * 获取调试信息
 */
public static function debug(): array {
    return [
        'registered_hooks' => array_keys(self::$handlers),
        'handlers' => self::$handlers,
        'subscribers' => self::$subscribers,
        'execution_log' => self::$executionLog,
        'debug_mode' => self::$debugMode,
        'total_handlers' => count(self::$handlers),
        'current_execution_depth' => self::$currentExecutionDepth,
    ];
}
```

---

## 五、执行流程详解

### 完整执行流程图

```
┌─────────────────────────────────────────────────────────────┐
│ 用户调用: Hooks::apply(MyHook::class, $parameter)            │
└───────────────────────┬─────────────────────────────────────┘
                        │
                        ↓
┌─────────────────────────────────────────────────────────────┐
│ Step 1: HookManager::apply()                                 │
│  - 验证 Hook 已注册                                           │
│  - 检查执行深度（防止无限循环）                                │
└───────────────────────┬─────────────────────────────────────┘
                        │
                        ↓
┌─────────────────────────────────────────────────────────────┐
│ Step 2: 获取处理器列表                                        │
│  - 从 $handlers 中获取处理器                                  │
│  - ksort() 按优先级排序（升序）                               │
│  - 返回排序后的处理器数组                                      │
└───────────────────────┬─────────────────────────────────────┘
                        │
                        ↓
┌─────────────────────────────────────────────────────────────┐
│ Step 3: 获取 Hook 定义                                        │
│  - 从 $registeredHooks 获取 HookDefinition 实例              │
│  - 检查是否为单处理器模式                                      │
│  - $isSingleProcessor = $hookDefinition->isSingleProcessor()│
└───────────────────────┬─────────────────────────────────────┘
                        │
                        ↓
┌─────────────────────────────────────────────────────────────┐
│ Step 4: 创建初始结果                                          │
│  - $initialResult = $hookDefinition->createSuccessResult()  │
│  - 作为处理器链的起点                                         │
│  - $currentResult = $initialResult                           │
└───────────────────────┬─────────────────────────────────────┘
                        │
                        ↓
┌─────────────────────────────────────────────────────────────┐
│ Step 5: 链式执行处理器                                        │
│  foreach ($handlers as $handlerData) {                       │
│                                                              │
│    ┌───────────────────────────────────────────────┐         │
│    │ 单处理器检查                                    │         │
│    │ if ($isSingleProcessor && $hasValidResult) {  │         │
│    │     break;  // 停止执行后续处理器               │         │
│    │ }                                              │         │
│    └────────────────────┬──────────────────────────┘         │
│                         │                                    │
│                         ↓                                    │
│    ┌───────────────────────────────────────────────┐         │
│    │ shouldExecute 检查                             │         │
│    │ if (!$handler::shouldExecute($param, $result))│         │
│    │     continue;  // 跳过此处理器                  │         │
│    │ }                                              │         │
│    └────────────────────┬──────────────────────────┘         │
│                         │                                    │
│                         ↓                                    │
│    ┌───────────────────────────────────────────────┐         │
│    │ 执行处理器                                      │         │
│    │ $currentResult = $handler(                     │         │
│    │     $parameter,                                │         │
│    │     $currentResult  // 前一个处理器的结果       │         │
│    │ );                                             │         │
│    └────────────────────┬──────────────────────────┘         │
│                         │                                    │
│                         ↓                                    │
│    ┌───────────────────────────────────────────────┐         │
│    │ 单处理器结果验证                                │         │
│    │ if ($isSingleProcessor &&                     │         │
│    │     $hookDefinition->isValidResult($result))  │         │
│    │ {                                              │         │
│    │     $hasValidResult = true;                    │         │
│    │ }                                              │         │
│    └────────────────────┬──────────────────────────┘         │
│                         │                                    │
│                         ↓                                    │
│    ┌───────────────────────────────────────────────┐         │
│    │ 记录执行日志（调试模式）                        │         │
│    │ - 处理器类名                                    │         │
│    │ - 执行时间                                      │         │
│    │ - 结果状态                                      │         │
│    └────────────────────┬──────────────────────────┘         │
│  }                                                           │
└───────────────────────┬─────────────────────────────────────┘
                        │
                        ↓
┌─────────────────────────────────────────────────────────────┐
│ Step 6: 返回最终结果                                          │
│  return $currentResult;                                      │
│  - 包含所有处理器的累积结果                                    │
│  - 成功状态、错误信息、处理追踪                                │
└─────────────────────────────────────────────────────────────┘
```

---

### 处理器链式传递机制

```
初始状态:
  $parameter = DemoHookParameter::create('test', 42)
  $currentResult = DemoHookResult::success('', null, [])

┌─────────────────────────────────────────────────────────────┐
│ Processor 1 (优先级 5): ContentCleanerHandler               │
│                                                              │
│ 输入:                                                         │
│  $parameter = {name: 'test', value: 42}                     │
│  $currentResult = {success: true, processedName: '', ...}   │
│                                                              │
│ 处理:                                                         │
│  $processedName = 'clean_' . $parameter->name               │
│  $processedValue = $parameter->value                        │
│                                                              │
│ 输出:                                                         │
│  return DemoHookResult::success('clean_test', 42, [...])    │
│                                                              │
│ 新状态:                                                       │
│  $currentResult = {processedName: 'clean_test', ...}        │
└───────────────────────┬─────────────────────────────────────┘
                        │
                        ↓
┌─────────────────────────────────────────────────────────────┐
│ Processor 2 (优先级 10): ValueMultiplierHandler             │
│                                                              │
│ 输入:                                                         │
│  $parameter = {name: 'test', value: 42}                     │
│  $currentResult = {processedName: 'clean_test', value: 42}  │
│                                                              │
│ 处理:                                                         │
│  $newValue = $currentResult->processedValue * 2             │
│                                                              │
│ 输出:                                                         │
│  return DemoHookResult::success(                            │
│      'clean_test',                                           │
│      84,  // 42 * 2                                          │
│      [...]                                                   │
│  )                                                           │
│                                                              │
│ 新状态:                                                       │
│  $currentResult = {processedName: 'clean_test', value: 84}  │
└───────────────────────┬─────────────────────────────────────┘
                        │
                        ↓
┌─────────────────────────────────────────────────────────────┐
│ Processor 3 (优先级 20): MetadataHandler                     │
│                                                              │
│ 输入:                                                         │
│  $parameter = {name: 'test', value: 42}                     │
│  $currentResult = {processedName: 'clean_test', value: 84}  │
│                                                              │
│ 处理:                                                         │
│  $metadata = ['timestamp' => time(), 'handler' => 'Metadata']│
│                                                              │
│ 输出:                                                         │
│  return DemoHookResult::success(                            │
│      'clean_test',                                           │
│      84,                                                     │
│      ['timestamp' => 1234567890, ...]                       │
│  )                                                           │
│                                                              │
│ 最终状态:                                                     │
│  $currentResult = {                                          │
│      processedName: 'clean_test',                            │
│      processedValue: 84,                                     │
│      metadata: {...},                                        │
│      success: true                                           │
│  }                                                           │
└───────────────────────┬─────────────────────────────────────┘
                        │
                        ↓
┌─────────────────────────────────────────────────────────────┐
│ 返回最终结果                                                  │
│  return $currentResult;                                      │
└─────────────────────────────────────────────────────────────┘
```

---

### 单处理器模式流程

```
┌─────────────────────────────────────────────────────────────┐
│ UserListHook (单处理器模式)                                  │
│  is_single_processor = true                                 │
└───────────────────────┬─────────────────────────────────────┘
                        │
                        ↓
┌─────────────────────────────────────────────────────────────┐
│ Processor 1: UserListHandler1                               │
│                                                              │
│ 执行:                                                         │
│  $result = UserListResult::success([...users...])           │
│  $result->markAsProcessed('UserListHandler1');              │
│                                                              │
│ 验证:                                                         │
│  if ($hookDefinition->isValidResult($result)) {             │
│      // $result->isProcessed() === true                     │
│      $hasValidResult = true;                                │
│  }                                                           │
└───────────────────────┬─────────────────────────────────────┘
                        │
                        ↓
┌─────────────────────────────────────────────────────────────┐
│ 单处理器检查                                                  │
│  if ($isSingleProcessor && $hasValidResult) {               │
│      break;  // 停止执行后续处理器                            │
│  }                                                           │
│                                                              │
│ 结果：跳过 Processor 2 和 Processor 3                        │
│  直接返回 UserListHandler1 的结果                            │
└───────────────────────┬─────────────────────────────────────┘
                        │
                        ↓
┌─────────────────────────────────────────────────────────────┐
│ 返回结果                                                      │
│  return $currentResult;  // UserListHandler1 的结果          │
│                                                              │
│ 性能优势:                                                     │
│  - 只执行了 1 个处理器                                        │
│  - 避免了后续处理器的无意义调用                                │
│  - 性能提升约 60-80%                                         │
└─────────────────────────────────────────────────────────────┘
```

---

## 六、关键特性实现原理

### 1. 强类型约束实现

**目的**：确保参数和返回值的类型安全，防止类型错误导致的运行时异常。

**实现方式**：

```php
// HookDefinition 构造函数验证
protected function validateDefinition(): void {
    // 验证参数类存在且继承正确基类
    if (!class_exists($this->parameter_class)) {
        throw new InvalidArgumentException("Parameter class does not exist");
    }
    if (!is_subclass_of($this->parameter_class, HookParameter::class)) {
        throw new InvalidArgumentException("Parameter class must extend HookParameter");
    }

    // 验证返回值类
    if (!class_exists($this->return_class)) {
        throw new InvalidArgumentException("Return class does not exist");
    }
    if (!is_subclass_of($this->return_class, HookResult::class)) {
        throw new InvalidArgumentException("Return class must extend HookResult");
    }
}

// HookParameter 构造函数验证
public function __construct() {
    $this->validate();  // 自动触发子类的验证逻辑
}

// HookHandlerInterface 接口约束
interface HookHandlerInterface {
    public static function handle(
        HookParameterInterface $parameter,  // 强制类型
        HookResultInterface $result         // 强制类型
    ): HookResultInterface;                // 强制返回类型
}
```

**效果**：
- 编译时类型检查（通过 PHP 类型声明）
- 运行时类型验证（通过构造函数验证）
- 错误在早期被发现，而不是在执行过程中

---

### 2. 优先级排序实现

**目的**：按优先级顺序执行处理器，确保核心功能先执行，扩展功能后执行。

**实现方式**：

```php
// 处理器存储结构（按优先级分组）
private static array $handlers = [
    'HookClass' => [
        1   => [Handler1, Handler2],   // 最高优先级
        5   => [Handler3],             // 高优先级
        10  => [Handler4],             // 中等优先级
        20  => [Handler5],             // 低优先级
        50  => [Handler6],             // 最低优先级
    ]
];

// 获取处理器时排序
public static function getHandlers(string $hookClass): array {
    if (!isset(self::$handlers[$hookClass])) {
        return [];
    }

    // 按优先级升序排序（数字小的排在前面）
    ksort(self::$handlers[$hookClass]);

    // 遍历并转换为统一格式
    $result = [];
    foreach (self::$handlers[$hookClass] as $priority => $handlers) {
        foreach ($handlers as $handler) {
            $result[] = [
                'type' => 'callable',
                'handler' => $handler,
                'priority' => $priority,
            ];
        }
    }

    return $result;
}
```

**执行顺序**：

```
Priority 1  → Handler1, Handler2  (最先执行)
Priority 5  → Handler3             (第二执行)
Priority 10 → Handler4             (第三执行)
Priority 20 → Handler5             (第四执行)
Priority 50 → Handler6             (最后执行)
```

---

### 3. 条件执行实现

**目的**：根据条件判断是否应该执行处理器，避免无意义的处理，提升性能。

**实现方式**：

```php
// HookHandlerInterface 接口定义
interface HookHandlerInterface {
    // 子类实现此方法判断是否应该执行
    public static function shouldExecute(
        HookParameterInterface $parameter,
        HookResultInterface $result
    ): bool;
}

// HookManager 执行时检查
foreach ($handlers as $handlerData) {
    // 静态类处理器 - 检查 shouldExecute
    if (is_string($callable) && is_subclass_of($callable, HookHandlerInterface::class)) {
        if (!$callable::shouldExecute($parameter, $currentResult)) {
            if (self::$debugMode) {
                self::logDebug("Handler skipped (shouldExecute returned false)");
            }
            continue;  // 跳过此处理器
        }
    }

    // 执行处理器
    $currentResult = $handler($parameter, $currentResult);
}
```

**实际应用示例**：

```php
class EmailValidatorHandler implements HookHandlerInterface {
    public static function shouldExecute(
        HookParameterInterface $parameter,
        HookResultInterface $result
    ): bool {
        $data = $parameter->toArray();
        // 只有有 email 字段时才执行验证
        return isset($data['email']) && !empty($data['email']);
    }

    public static function handle(...): HookResultInterface {
        // 验证 email 格式
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return ValidationResult::failure(['邮箱格式无效']);
        }
        return ValidationResult::success($data);
    }
}
```

**效果**：
- 性能优化：跳过不必要的处理
- 灵活控制：根据数据状态决定执行
- 条件验证：检查前置条件满足性

---

### 4. 单处理器模式实现

**目的**：对于只需要一个处理器的场景，有效结果后立即停止，避免后续无意义处理。

**实现方式**：

```php
// HookDefinition 定义
abstract class HookDefinition {
    public bool $is_single_processor = false;

    public function isSingleProcessor(): bool {
        return $this->is_single_processor;
    }

    // 验证单处理器结果是否有效
    public function isValidSingleProcessorResult(HookResult $result): bool {
        return $result->isProcessed();  // 检查是否被处理
    }
}

// HookResult 处理追踪
abstract class HookResult {
    protected bool $processed = false;
    protected string $processor = '';

    public function markAsProcessed(string $processor = ''): self {
        $this->processed = true;
        $this->processor = $processor;
        return $this;
    }
}

// HookManager 执行控制
public static function apply(...): HookResult {
    $hookDefinition = self::getRegisteredHook($hookClass);
    $isSingleProcessor = $hookDefinition->isSingleProcessor();
    $hasValidResult = false;

    foreach ($handlers as $handlerData) {
        // 单处理器模式：已有有效结果则停止
        if ($isSingleProcessor && $hasValidResult) {
            if (self::$debugMode) {
                self::logDebug("Single processor hook stopped");
            }
            break;  // 停止执行后续处理器
        }

        // 执行处理器
        $currentResult = $handler($parameter, $currentResult);

        // 单处理器模式：检查结果是否有效
        if ($isSingleProcessor && $hookDefinition->isValidSingleProcessorResult($currentResult)) {
            $hasValidResult = true;
        }
    }

    return $currentResult;
}
```

**使用示例**：

```php
// 定义单处理器 Hook
class UserListHook extends HookDefinition {
    public readonly string $parameter_class;
    public readonly string $return_class;
    public bool $is_single_processor = true;  // 启用单处理器模式

    public function __construct() {
        $this->parameter_class = UserListParameter::class;
        $this->return_class = UserListResult::class;
        parent::__construct();
    }
}

// 处理器实现
class UserListHandler implements HookHandlerInterface {
    public static function handle(...): HookResultInterface {
        // 查询用户列表
        $users = User::all();

        $result = UserListResult::success($users);
        $result->markAsProcessed('UserListHandler');  // 标记为已处理

        return $result;
    }

    public static function shouldExecute(...): bool {
        // 总是执行（主处理器）
        return true;
    }

    public static function getPriority(): int {
        return 1;  // 最高优先级
    }
}
```

**性能对比**：

```
普通模式（多处理器链式执行）:
  Processor 1: 10ms
  Processor 2: 15ms
  Processor 3: 20ms
  Processor 4: 25ms
  Processor 5: 30ms
  总耗时: 100ms

单处理器模式（有效结果后停止）:
  Processor 1: 10ms (返回有效结果)
  Processor 2-5: 跳过
  总耗时: 10ms
  性能提升: 90%
```

---

### 5. 错误容错实现

**目的**：处理器失败不中断整个流程，累积错误信息，确保其他处理器能继续执行。

**实现方式**：

```php
// HookManager 执行流程中的错误处理
foreach ($handlers as $handlerData) {
    try {
        // 执行处理器
        $currentResult = $handler($parameter, $currentResult);

        // 记录执行日志（调试模式）
        if (self::$debugMode) {
            $executionTime = (microtime(true) - $startTime) * 1000;
            self::logDebug("Handler executed in " . number_format($executionTime, 2) . 'ms');
        }

    } catch (Exception $e) {
        // 错误容错：记录错误但不中断流程
        $errorMessage = "Handler execution failed: " . $e->getMessage();

        if (self::$debugMode) {
            self::logDebug($errorMessage);
        }

        // 将错误信息添加到当前结果中
        $currentResult->addError($errorMessage);

        // 继续执行下一个处理器
    }
}

// HookResult 错误累积
public function addError(string $error): self {
    $this->errors[] = $error;
    $this->success = false;  // 自动设置失败状态
    return $this;
}

public function addErrors(array $errors): self {
    foreach ($errors as $error) {
        $this->addError((string) $error);
    }
    return $this;
}
```

**实际效果**：

```
执行流程（有错误）:

Processor 1: 成功
  $result->success = true

Processor 2: 异常
  throw new Exception("验证失败")
  → catch 捕获异常
  → $result->addError("验证失败")
  → $result->success = false
  → 继续执行 Processor 3

Processor 3: 成功
  继续处理
  → $result 保持 success = false 状态

Processor 4: 成功
  继续处理

最终结果:
  $result->success = false
  $result->errors = ["验证失败"]
  所有处理器都已执行，错误被累积
```

---

### 6. 调试追踪实现

**目的**：记录详细的执行日志，便于问题定位、性能分析和流程追踪。

**实现方式**：

```php
// HookManager 调试支持
private static bool $debugMode = false;
private static array $executionLog = [];

public static function enableDebug(): void {
    self::$debugMode = true;
}

private static function logDebug(string $message): void {
    if (!self::$debugMode) {
        return;
    }
    $timestamp = date('Y-m-d H:i:s');
    self::$executionLog[] = "[{$timestamp}] {$message}";
}

// 执行流程中记录详细日志
public static function apply(...): HookResult {
    if (self::$debugMode) {
        self::logDebug("Hook '{$hookClass}' execution started");
    }

    foreach ($handlers as $handlerData) {
        if (self::$debugMode) {
            $startTime = microtime(true);
        }

        // 执行处理器
        $currentResult = $handler($parameter, $currentResult);

        if (self::$debugMode) {
            $executionTime = (microtime(true) - $startTime) * 1000;
            self::logDebug("Handler '{$handlerClass}' executed in " . number_format($executionTime, 2) . 'ms');
            self::logDebug("Result: " . json_encode($currentResult->toArray()));
        }
    }

    if (self::$debugMode) {
        self::logDebug("Hook '{$hookClass}' execution completed with {$executedCount} handlers");
    }
}
```

**调试日志示例**：

```
[2026-05-22 21:45:00] Hook 'Modules\ABase\Hooks\Definitions\DemoHook' execution started
[2026-05-22 21:45:00] Handler 'Modules\ABase\Hooks\Handlers\DemoHookHandler' executed in 2.34ms
[2026-05-22 21:45:00] Result: {"success":true,"processedName":"demo_test","processedValue":84}
[2026-05-22 21:45:00] Handler 'Modules\ABase\Hooks\Handlers\MetadataHandler' executed in 1.56ms
[2026-05-22 21:45:00] Result: {"success":true,"metadata":{"timestamp":1234567890}}
[2026-05-22 21:45:00] Hook 'Modules\ABase\Hooks\Definitions\DemoHook' execution completed with 2 handlers
```

---

## 七、使用示例

### 基础使用示例

```php
use Modules\ABase\Hooks\Management\Hooks;
use Modules\ABase\Hooks\Definitions\DemoHook;
use Modules\ABase\Hooks\Parameters\DemoHookParameter;

// 1. 注册 Hook
Hooks::register(DemoHook::class);

// 2. 注册处理器
Hooks::add(DemoHook::class, DemoHookHandler::class, 10);

// 3. 创建参数
$parameter = DemoHookParameter::create(
    name: 'test_value',
    value: 42,
    options: ['source' => 'example']
);

// 4. 执行 Hook
$result = Hooks::apply(DemoHook::class, $parameter);

// 5. 检查结果
if ($result->isSuccess()) {
    echo "处理成功\n";
    echo "处理后的名称: " . $result->processedName . "\n";
    echo "处理后的值: " . $result->processedValue . "\n";
} else {
    echo "处理失败\n";
    foreach ($result->getErrors() as $error) {
        echo "错误: " . $error . "\n";
    }
}
```

---

### 多处理器链式使用

```php
use Modules\ABase\Hooks\Management\Hooks;

// 注册 Hook
Hooks::register(ContentFilterHook::class);

// 注册多个处理器（不同优先级）
Hooks::add(ContentFilterHook::class, XSSCleanerHandler::class, 1);    // 最高优先级
Hooks::add(ContentFilterHook::class, HtmlFormatterHandler::class, 10); // 中等优先级
Hooks::add(ContentFilterHook::class, MarkdownParserHandler::class, 20); // 低优先级

// 执行 Hook
$parameter = ContentParameter::create(content: '<p>Hello World</p>');
$result = Hooks::apply(ContentFilterHook::class, $parameter);

// 结果经过了三个处理器的链式处理:
// 1. XSSCleanerHandler (Priority 1): 清理 XSS
// 2. HtmlFormatterHandler (Priority 10): 格式化 HTML
// 3. MarkdownParserHandler (Priority 20): 解析 Markdown
```

---

### 单处理器使用

```php
use Modules\ABase\Hooks\Management\Hooks;

// 注册单处理器 Hook
Hooks::register(UserListHook::class);

// 只注册一个处理器
Hooks::add(UserListHook::class, UserListHandler::class, 1);

// 执行 Hook
$parameter = UserListParameter::create(page: 1, limit: 20);
$result = Hooks::apply(UserListHook::class, $parameter);

// UserListHandler 返回有效结果后立即停止
// 不会有后续处理器执行
```

---

### 调试使用

```php
use Modules\ABase\Hooks\Management\Hooks;

// 启用调试模式
Hooks::enableDebug();

// 执行 Hook
$result = Hooks::apply(DemoHook::class, $parameter);

// 获取执行日志
$log = Hooks::getLog();
foreach ($log as $entry) {
    echo $entry . "\n";
}

// 获取详细调试信息
$debugInfo = Hooks::debug();
echo "总 Hook 数: " . $debugInfo['hook_count'] . "\n";
echo "处理器总数: " . $debugInfo['total_handler_instances'] . "\n";
```

---

## 八、最佳实践

### 1. Hook 设计原则

**✅ 推荐做法**：

- **单一职责**：每个 Hook 只处理一类数据
- **明确命名**：Hook 名称清晰表达用途（如 `ContentFilterHook`）
- **类型约束**：参数和返回值类型明确
- **文档完善**：提供详细的 description 说明

**❌ 避免做法**：

- 功能混杂：一个 Hook 处理多种不相关数据
- 命名模糊：Hook 名称不清晰（如 `DataHook`）
- 类型松散：参数或返回值使用 `mixed` 类型
- 缺少文档：没有 description 说明

---

### 2. 处理器设计原则

**✅ 推荐做法**：

- **静态方法**：所有方法使用 `static` 关键字
- **条件判断**：实现 `shouldExecute()` 避免无意义处理
- **合理优先级**：核心功能优先级低（1-10），扩展功能优先级高（20+）
- **错误处理**：返回失败结果而不是抛出异常

**❌ 避免做法**：

- 使用实例方法
- 忽略 `shouldExecute()` 实现
- 优先级设置不合理（核心功能优先级高）
- 直接抛出异常中断流程

---

### 3. 参数设计原则

**✅ 推荐做法**：

- **自动验证**：在 `validate()` 中验证参数合法性
- **readonly 属性**：使用 `readonly` 定义不可变属性
- **工厂方法**：提供 `create()` 静态方法方便创建
- **数组转换**：实现 `toArray()` 方便处理器访问数据

**❌ 過免做法**：

- 不验证参数
- 使用可变属性
- 没有工厂方法
- 不实现 `toArray()`

---

### 4. 结果设计原则

**✅ 推荐做法**：

- **状态管理**：维护 `success` 和 `errors` 状态
- **处理追踪**：使用 `processed` 和 `processor` 追踪处理
- **静态工厂**：提供 `success()` 和 `failure()` 静态方法
- **链式调用**：设置方法返回 `$this` 支持链式操作

**❌ 避免做法**：

- 不维护状态
- 缺少处理追踪
- 没有工厂方法
- 不支持链式调用

---

### 5. 性能优化建议

**优先级设置**：

- 简单快速处理：优先级 1-10
- 常规业务处理：优先级 10-20
- 可选扩展处理：优先级 20-30
- 调试监控处理：优先级 30+

**条件执行**：

- 使用 `shouldExecute()` 避免无意义处理
- 检查数据完整性后才执行
- 检查前置条件满足性

**单处理器模式**：

- 对于简单场景优先使用单处理器
- 有效结果后立即停止
- 性能提升约 60-90%

**调试模式**：

- 开发环境启用调试
- 生产环境关闭调试
- 性能敏感场景关闭调试

---

## 九、常见问题

### Q1: Hook 未注册错误

**错误信息**：
```
InvalidArgumentException: Hook 'MyHook' is not registered
```

**解决方案**：
```php
// 使用前先注册 Hook
Hooks::register(MyHook::class);
Hooks::add(MyHook::class, MyHandler::class);
```

---

### Q2: 参数类不存在错误

**错误信息**：
```
InvalidArgumentException: Parameter class 'MyParameter' does not exist
```

**解决方案**：
```php
// 确保 Hook 定义中参数类路径正确
class MyHook extends HookDefinition {
    public readonly string $parameter_class;

    public function __construct() {
        $this->parameter_class = MyParameter::class;  // 确保类存在
        parent::__construct();
    }
}
```

---

### Q3: 处理器未实现接口错误

**错误信息**：
```
InvalidArgumentException: Handler 'MyHandler' must implement HookHandlerInterface
```

**解决方案**：
```php
// 处理器必须实现 HookHandlerInterface
class MyHandler implements HookHandlerInterface {
    public static function handle(...): HookResultInterface {
        // 实现处理逻辑
    }

    public static function getPriority(): int {
        return 10;
    }

    public static function shouldExecute(...): bool {
        return true;
    }
}
```

---

### Q4: 无限循环错误

**错误信息**：
```
Exception: Maximum hook execution depth exceeded - possible infinite loop
```

**解决方案**：
```php
// 避免 Hook 间相互调用
// 错误示例:
class Handler1 {
    public static function handle(...) {
        // 错误：调用另一个会触发当前 Hook 的 Hook
        Hooks::apply(AnotherHook::class, $param);  // AnotherHook 可能触发当前 Hook
    }
}

// 正确示例:
class Handler1 {
    public static function handle(...) {
        // 直接处理，不触发其他 Hook
        return MyResult::success($data);
    }
}
```

---

## 十、总结

### Hook 系统的核心价值

1. **类型安全**：强类型约束确保参数和返回值正确
2. **可扩展性**：链式处理器支持灵活扩展
3. **高性能**：静态方法、条件跳过、单处理器优化
4. **易调试**：详细执行日志和调试信息
5. **易维护**：清晰的架构和统一的接口

### 适用场景

- **数据过滤**：内容清理、XSS 防护、格式化
- **数据验证**：输入验证、业务规则验证
- **数据转换**：格式转换、数据标准化
- **事件处理**：数据变更通知、状态更新
- **权限控制**：访问权限检查、数据权限过滤

### 技术亮点

- **强类型约束**：编译时和运行时双重验证
- **链式处理**：多处理器协同工作
- **优先级控制**：灵活的执行顺序
- **条件执行**：智能跳过无意义处理
- **单处理器模式**：性能优化利器
- **调试支持**：完整的日志和追踪
- **错误容错**：处理器失败不中断流程

---

**文档版本**：v1.0
**更新时间**：2026-05-22
**维护模块**：ABase
**相关文档**：[Hook 使用指南](./Hook系统使用指南.md)