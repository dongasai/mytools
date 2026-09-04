---
name: dev-hook
description: 进行 Hook 开发，应用就能
---
# Hook系统 - LLM使用指南

> 为大语言模型（AI编程助手）提供的Hook系统快速参考指南

## 概述

这份文档专为LLM（如Claude、GPT等）设计，提供Hook系统的核心概念和使用模式，帮助AI助手更好地理解和使用ABase模块的Hook系统。

## 核心概念速览

### Hook系统的本质
- **数据过滤器**：接收数据 → 处理数据 → 返回处理后的数据
- **链式处理**：多个处理器按优先级顺序处理同一份数据
- **强类型**：参数和返回值都必须继承特定的基类
- **静态方法**：所有处理器方法都是静态的，无状态

### 三大核心组件
1. **HookDefinition** - 定义Hook的规范（参数类型、返回值类型）
2. **HookParameter** - 封装输入数据
3. **HookResult** - 封装输出数据

## 快速语法参考

### 1. 创建Hook定义
```php
use Modules\ABase\Hooks\Core\HookDefinition;

class MyHook extends HookDefinition
{
    public readonly string $parameter_class;
    public readonly string $return_class;
    public string $description = '我的Hook处理器';

    public function __construct()
    {
        $this->parameter_class = MyParameter::class;
        $this->return_class = MyResult::class;
        parent::__construct();
    }
}
```

### 2. 创建参数类
```php
use Modules\ABase\Hooks\Core\HookParameter;

class MyParameter extends HookParameter
{
    public function __construct(
        public readonly string $name = '',
        public readonly mixed $value = null,
        public readonly array $options = []
    ) {
        parent::__construct();
    }

    protected function validate(): void
    {
        if (empty($this->name)) {
            throw new \InvalidArgumentException('名称不能为空');
        }
    }

    public static function create(string $name, mixed $value = null, array $options = []): self
    {
        return new self($name, $value, $options);
    }
}
```

### 3. 创建结果类
```php
use Modules\ABase\Hooks\Core\HookResult;

class MyResult extends HookResult
{
    public function __construct(
        public readonly string $processedName = '',
        public readonly mixed $processedValue = null,
        public readonly array $metadata = []
    ) {
        parent::__construct(true);
    }

    public static function success(string $name, mixed $value, array $metadata = []): static
    {
        return new static($name, $value, $metadata);
    }

    public static function failure(array $errors): static
    {
        $result = new static('', null, []);
        foreach ($errors as $error) {
            $result->addError($error);
        }
        return $result;
    }
}
```

### 4. 创建处理器
```php
use Modules\ABase\Hooks\Core\HookHandlerInterface;

class MyHandler implements HookHandlerInterface
{
    public static function handle(HookParameterInterface $parameter, HookResultInterface $result): HookResultInterface
    {
        $data = $parameter->toArray();

        // 处理逻辑
        $processedName = $data['name'] . '_processed';
        $processedValue = $data['value'] * 2;

        return MyResult::success($processedName, $processedValue, [
            'handler' => static::class,
            'processed_at' => time()
        ]);
    }

    public static function shouldExecute(HookParameterInterface $parameter, HookResultInterface $result): bool
    {
        $data = $parameter->toArray();
        return !empty($data['name']);
    }

    public static function getPriority(): int
    {
        return 10; // 1-100，数值越小越早执行
    }
}
```

## 常用使用模式

### 模式1：内容过滤
```php
// 适用场景：HTML清理、XSS防护、内容格式化
class ContentFilterHook extends HookDefinition
{
    public readonly string $parameter_class;
    public readonly string $return_class;
    public string $description = '内容过滤器';

    public function __construct()
    {
        $this->parameter_class = ContentParameter::class;
        $this->return_class = ContentResult::class;
        parent::__construct();
    }
}

class ContentCleanerHandler implements HookHandlerInterface
{
    public static function handle(HookParameterInterface $parameter, HookResultInterface $result): HookResultInterface
    {
        $content = $parameter->getContent();
        $cleanContent = strip_tags($content, '<p><br><strong><em>');

        return ContentResult::success($cleanContent, '内容清理完成');
    }

    public static function shouldExecute(HookParameterInterface $parameter, HookResultInterface $result): bool
    {
        return !empty($parameter->getContent());
    }

    public static function getPriority(): int
    {
        return 5; // 高优先级，先执行清理
    }
}
```

### 模式2：数据验证
```php
// 适用场景：输入验证、数据完整性检查、业务规则验证
class DataValidatorHook extends HookDefinition
{
    public readonly string $parameter_class;
    public readonly string $return_class;
    public string $description = '数据验证器';

    public function __construct()
    {
        $this->parameter_class = ValidationParameter::class;
        $this->return_class = ValidationResult::class;
        parent::__construct();
    }
}

class EmailValidatorHandler implements HookHandlerInterface
{
    public static function handle(HookParameterInterface $parameter, HookResultInterface $result): HookResultInterface
    {
        $data = $parameter->toArray();
        $errors = [];

        if (!filter_var($data['email'] ?? '', FILTER_VALIDATE_EMAIL)) {
            $errors[] = '邮箱格式无效';
        }

        if (empty($errors)) {
            return ValidationResult::success($data, '验证通过');
        } else {
            return ValidationResult::failure($errors, '验证失败');
        }
    }

    public static function shouldExecute(HookParameterInterface $parameter, HookResultInterface $result): bool
    {
        $data = $parameter->toArray();
        return isset($data['email']);
    }

    public static function getPriority(): int
    {
        return 1; // 最高优先级
    }
}
```

### 模式3：数据转换
```php
// 适用场景：格式转换、数据标准化、计算处理
class DataTransformerHook extends HookDefinition
{
    public readonly string $parameter_class;
    public readonly string $return_class;
    public string $description = '数据转换器';

    public function __construct()
    {
        $this->parameter_class = TransformParameter::class;
        $this->return_class = TransformResult::class;
        parent::__construct();
    }
}

class PriceCalculatorHandler implements HookHandlerInterface
{
    public static function handle(HookParameterInterface $parameter, HookResultInterface $result): HookResultInterface
    {
        $data = $parameter->toArray();
        $price = $data['price'] ?? 0;
        $quantity = $data['quantity'] ?? 1;

        // 计算总价
        $total = $price * $quantity;

        // 应用折扣
        if (isset($data['discount'])) {
            $total = $total * (1 - $data['discount'] / 100);
        }

        return TransformResult::success([
            'subtotal' => $price * $quantity,
            'discount_amount' => ($price * $quantity) * ($data['discount'] / 100),
            'total' => $total
        ], '价格计算完成');
    }

    public static function shouldExecute(HookParameterInterface $parameter, HookResultInterface $result): bool
    {
        $data = $parameter->toArray();
        return isset($data['price']) && isset($data['quantity']);
    }

    public static function getPriority(): int
    {
        return 20;
    }
}
```

## 实际项目中的Hook示例

### ABase模块 - DemoHook

系统中已经实现的DemoHook是一个完整的Hook系统示例：

#### Hook定义
```php
// Modules/ABase/Hooks/Definitions/DemoHook.php
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
```

#### 参数类
```php
// Modules/ABase/Hooks/Parameters/DemoHookParameter.php
class DemoHookParameter extends HookParameter
{
    public function __construct(
        public readonly string $name = '',
        public readonly mixed $value = null,
        public readonly array $options = []
    ) {
        parent::__construct();
    }

    protected function validate(): void
    {
        if (empty($this->name)) {
            throw new \InvalidArgumentException('名称不能为空');
        }
    }

    public static function create(string $name, mixed $value = null, array $options = []): self
    {
        return new self($name, $value, $options);
    }
}
```

#### 结果类
```php
// Modules/ABase/Hooks/Results/DemoHookResult.php
class DemoHookResult extends HookResult
{
    public function __construct(
        public readonly string $processedName = '',
        public readonly mixed $processedValue = null,
        public readonly array $metadata = []
    ) {
        parent::__construct(true);
    }

    public static function success(string $name, mixed $value, array $metadata = []): static
    {
        return new static($name, $value, $metadata);
    }
}
```

#### 处理器
```php
// Modules/ABase/Hooks/Handlers/DemoHookHandler.php
class DemoHookHandler implements HookHandlerInterface
{
    private static string $defaultPrefix = 'demo';
    private static int $multiplier = 2;
    private static bool $enableLogging = true;

    public static function handle(HookParameterInterface $parameter, HookResultInterface $result): HookResultInterface
    {
        $data = $parameter->toArray();

        $processedName = self::$defaultPrefix . '_' . $data['name'];
        $processedValue = is_numeric($data['value']) ? $data['value'] * self::$multiplier : $data['value'];

        $metadata = [
            'handler' => static::class,
            'original_name' => $data['name'],
            'original_value' => $data['value'],
            'multiplier' => self::$multiplier,
            'processed_at' => now()->toISOString(),
        ];

        if (self::$enableLogging) {
            logger()->info("DemoHook processed: {$data['name']} -> {$processedName}");
        }

        return DemoHookResult::success($processedName, $processedValue, $metadata);
    }

    public static function shouldExecute(HookParameterInterface $parameter, HookResultInterface $result): bool
    {
        $data = $parameter->toArray();
        return !empty($data['name']);
    }

    public static function getPriority(): int
    {
        return 10;
    }

    public static function configure(string $defaultPrefix = 'demo', int $multiplier = 2, bool $enableLogging = true): void
    {
        self::$defaultPrefix = $defaultPrefix;
        self::$multiplier = $multiplier;
        self::$enableLogging = $enableLogging;
    }
}
```

#### 使用示例
```php
use Modules\ABase\Hooks\Management\Hooks;
use Modules\ABase\Hooks\Parameters\DemoHookParameter;

// 创建参数
$parameter = DemoHookParameter::create(
    name: 'test_value',
    value: 42,
    options: ['source' => 'example']
);

// 执行Hook
$result = Hooks::apply(\Modules\ABase\Hooks\Definitions\DemoHook::class, $parameter);

if ($result->isSuccess()) {
    echo "处理后的名称: " . $result->processedName . "\n";     // demo_test_value
    echo "处理后的值: " . $result->processedValue . "\n";       // 84
    echo "元数据: " . json_encode($result->metadata) . "\n";
}
```

## Hook系统API速查

### HookManager 静态方法
```php
use Modules\ABase\Hooks\Management\HookManager;

// 添加处理器
HookManager::add(HookClass::class, HandlerClass::class, $priority = 10);

// 执行Hook
$result = HookManager::apply(HookClass::class, $parameter);

// 批量添加
HookManager::addHandlers(HookClass::class, [
    Handler1::class,
    ['class' => Handler2::class, 'priority' => 5]
]);

// 调试功能
HookManager::enableDebug();
HookManager::disableDebug();
$debugInfo = HookManager::debug();

// 订阅者管理
HookManager::registerSubscriber(SubscriberClass::class);
```

### Hooks 助手类
```php
use Modules\ABase\Hooks\Management\Hooks;

// 基本操作
Hooks::add(HookClass::class, HandlerClass::class, $priority);
Hooks::remove(HookClass::class, HandlerClass::class);
$result = Hooks::apply(HookClass::class, $parameter);

// 便捷方法
Hooks::addFirst(HookClass::class, HandlerClass::class);  // 优先级1
Hooks::addLast(HookClass::class, HandlerClass::class);   // 优先级50
Hooks::addMany(HookClass::class, $handlersArray);

// 检查方法
Hooks::hasHandlers(HookClass::class);
Hooks::countHandlers(HookClass::class);
$handlers = Hooks::getHandlers(HookClass::class);

// 调试功能
Hooks::enableDebug();
$debugInfo = Hooks::debug();
$executionLog = Hooks::getLog();
Hooks::clearLog();

// 订阅者管理
Hooks::subscribe(SubscriberClass::class);
Hooks::unsubscribe(SubscriberClass::class);
$subscribers = Hooks::getSubscribers();
```

## 订阅者模式

### 订阅者类模板
```php
use Modules\ABase\Hooks\Core\AbstractHookSubscriber;

class MySubscriber extends AbstractHookSubscriber
{
    public function subscribe(): array
    {
        return [
            HookClass1::class => 'handleHook1',
            HookClass2::class => 'handleHook2',
        ];
    }

    public function handleHook1(HookParameterInterface $parameter, HookResultInterface $result): HookResultInterface
    {
        // 处理逻辑
        return $result;
    }

    public function handleHook2(HookParameterInterface $parameter, HookResultInterface $result): HookResultInterface
    {
        // 处理逻辑
        return $result;
    }
}
```

### 注册订阅者
```php
// 方式1：注册类名（自动依赖注入）
Hooks::subscribe(MySubscriber::class);

// 方式2：注册实例
$subscriber = new MySubscriber($dependency);
Hooks::subscribe($subscriber);

// 批量注册
Hooks::subscribeMany([
    Subscriber1::class,
    Subscriber2::class,
]);
```

## 命令行工具

### Hook管理命令
```bash
# 列出所有可用的Hook接口
php artisan hook:list

# 输出示例：
# 🔗 可用Hook列表
# ==========================================
# +-----------------------+----------------+-------+----------------------+-------------------+
# | Hook名称              | 描述           | 分组  | 参数类               | 返回类            |
# +-----------------------+----------------+-------+----------------------+-------------------+
# | DemoHook              | 演示Hook系统   | ABase | DemoHookParameter    | DemoHookResult    |
# | PostContentFilterHook | 文章内容过滤器 | Demo5 | PostContentParameter | PostContentResult |
# +-----------------------+----------------+-------+----------------------+-------------------+
#
# 📊 统计:
#    总计: 2 个Hook
#    ABase: 1 个
#    Demo5: 1 个
```

### 测试和调试

### 调试代码模式
```php
// 启用调试
Hooks::enableDebug();

// 执行Hook
$result = Hooks::apply(MyHook::class, $parameter);

// 查看调试信息
$debugInfo = Hooks::debug();
echo "执行日志:\n";
foreach ($debugInfo['execution_log'] as $log) {
    echo "- {$log}\n";
}

// 清空日志
Hooks::clearLog();
```

## 常见错误和解决方案

### 错误1：No handlers registered for hook
```php
// 问题：没有注册处理器
// 解决：注册处理器
Hooks::add(MyHook::class, MyHandler::class);
```

### 错误2：Parameter class does not exist
```php
// 问题：参数类不存在或路径错误
// 解决：确保参数类存在且继承HookParameter
class MyParameter extends HookParameter { }
```

### 错误3：Handler must implement HookHandlerInterface
```php
// 问题：处理器没有实现正确接口
// 解决：实现接口并使用静态方法
class MyHandler implements HookHandlerInterface
{
    public static function handle(HookParameterInterface $parameter, HookResultInterface $result): HookResultInterface
    {
        // 处理逻辑
    }
}
```

## 单处理器模式

### 单处理器的特点

单处理器模式是Hook系统的一种简化使用方式，适用于只需要一个处理器的场景。相比于多处理器的链式处理，单处理器具有以下特点：

- **简洁高效**：只有一个处理器，直接执行，无需排序
- **直接控制**：完全控制处理逻辑，不受其他处理器影响
- **易调试**：问题定位更容易，输出结果可预测
- **性能更好**：没有链式调用的开销

### 创建单处理器Hook

#### 1. 定义单处理器Hook
```php
use Modules\ABase\Hooks\Core\HookDefinition;

class UserRegistrationHook extends HookDefinition
{
    public readonly string $parameter_class;
    public readonly string $return_class;
    public string $description = '用户注册单处理器Hook';

    public function __construct()
    {
        $this->parameter_class = UserRegistrationParameter::class;
        $this->return_class = UserRegistrationResult::class;
        parent::__construct();
    }
}
```

#### 2. 创建参数类
```php
use Modules\ABase\Hooks\Core\HookParameter;

class UserRegistrationParameter extends HookParameter
{
    public function __construct(
        public readonly string $username = '',
        public readonly string $email = '',
        public readonly string $password = '',
        public readonly array $profile = []
    ) {
        parent::__construct();
    }

    protected function validate(): void
    {
        if (empty($this->username)) {
            throw new \InvalidArgumentException('用户名不能为空');
        }
        if (empty($this->email)) {
            throw new \InvalidArgumentException('邮箱不能为空');
        }
        if (empty($this->password)) {
            throw new \InvalidArgumentException('密码不能为空');
        }
    }

    public static function create(array $userData): self
    {
        return new self(
            username: $userData['username'] ?? '',
            email: $userData['email'] ?? '',
            password: $userData['password'] ?? '',
            profile: $userData['profile'] ?? []
        );
    }
}
```

#### 3. 创建结果类
```php
use Modules\ABase\Hooks\Core\HookResult;

class UserRegistrationResult extends HookResult
{
    public function __construct(
        public readonly int $userId = 0,
        public readonly string $username = '',
        public readonly array $profile = [],
        public readonly array $errors = []
    ) {
        parent::__construct(true);
    }

    public static function success(int $userId, string $username, array $profile = []): static
    {
        return new static($userId, $username, $profile, []);
    }

    public static function failure(array $errors): static
    {
        return new static(0, '', [], $errors);
    }

    public function getUserId(): int
    {
        return $this->userId;
    }

    public function getUsername(): string
    {
        return $this->username;
    }

    public function getProfile(): array
    {
        return $this->profile;
    }

    public function hasErrors(): bool
    {
        return !empty($this->errors);
    }
}
```

#### 4. 创建单处理器
```php
use Modules\ABase\Hooks\Core\HookHandlerInterface;

class UserRegistrationHandler implements HookHandlerInterface
{
    public static function handle(HookParameterInterface $parameter, HookResultInterface $result): HookResultInterface
    {
        $data = $parameter->toArray();

        try {
            // 检查用户名是否已存在
            if (self::usernameExists($data['username'])) {
                return UserRegistrationResult::failure(['用户名已存在']);
            }

            // 检查邮箱是否已存在
            if (self::emailExists($data['email'])) {
                return UserRegistrationResult::failure(['邮箱已被注册']);
            }

            // 创建用户
            $user = \App\Models\User::create([
                'username' => $data['username'],
                'email' => $data['email'],
                'password' => bcrypt($data['password']),
                'profile' => json_encode($data['profile'] ?? []),
            ]);

            // 触发相关事件
            event(new UserRegisteredEvent($user));

            return UserRegistrationResult::success(
                userId: $user->id,
                username: $user->username,
                profile: $data['profile'] ?? []
            );

        } catch (\Exception $e) {
            return UserRegistrationResult::failure(['注册失败: ' . $e->getMessage()]);
        }
    }

    public static function shouldExecute(HookParameterInterface $parameter, HookResultInterface $result): bool
    {
        $data = $parameter->toArray();
        return !empty($data['username']) && !empty($data['email']) && !empty($data['password']);
    }

    public static function getPriority(): int
    {
        return 1; // 单处理器通常使用优先级1
    }

    private static function usernameExists(string $username): bool
    {
        return \App\Models\User::where('username', $username)->exists();
    }

    private static function emailExists(string $email): bool
    {
        return \App\Models\User::where('email', $email)->exists();
    }
}
```

### 单处理器使用示例

#### 基本使用
```php
use Modules\ABase\Hooks\Management\Hooks;
use Modules\ABase\Hooks\Parameters\UserRegistrationParameter;

// 创建参数
$userData = [
    'username' => 'john_doe',
    'email' => 'john@example.com',
    'password' => 'secure_password',
    'profile' => ['first_name' => 'John', 'last_name' => 'Doe']
];

$parameter = UserRegistrationParameter::create($userData);

// 执行单处理器Hook
$result = Hooks::apply(UserRegistrationHook::class, $parameter);

// 检查结果
if ($result->isSuccess() && !$result->hasErrors()) {
    echo "用户注册成功！\n";
    echo "用户ID: " . $result->getUserId() . "\n";
    echo "用户名: " . $result->getUsername() . "\n";
    echo "用户资料: " . json_encode($result->getProfile()) . "\n";
} else {
    echo "注册失败：\n";
    foreach ($result->getErrors() as $error) {
        echo "- " . $error . "\n";
    }
}
```

#### 在控制器中使用
```php
use Modules\ABase\Hooks\Management\Hooks;
use App\Http\Controllers\Controller;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        // 验证输入
        $validated = $request->validate([
            'username' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'password' => 'required|string|min:8',
            'first_name' => 'nullable|string|max:255',
            'last_name' => 'nullable|string|max:255',
        ]);

        // 准备Hook参数
        $userData = [
            'username' => $validated['username'],
            'email' => $validated['email'],
            'password' => $validated['password'],
            'profile' => [
                'first_name' => $validated['first_name'] ?? '',
                'last_name' => $validated['last_name'] ?? '',
            ]
        ];

        // 创建并执行单处理器Hook
        $parameter = UserRegistrationParameter::create($userData);
        $result = Hooks::apply(UserRegistrationHook::class, $parameter);

        // 处理结果
        if ($result->isSuccess() && !$result->hasErrors()) {
            // 注册成功，返回用户信息
            return response()->json([
                'success' => true,
                'message' => '用户注册成功',
                'user' => [
                    'id' => $result->getUserId(),
                    'username' => $result->getUsername(),
                    'profile' => $result->getProfile(),
                ]
            ], 201);
        } else {
            // 注册失败，返回错误信息
            return response()->json([
                'success' => false,
                'message' => '用户注册失败',
                'errors' => $result->getErrors()
            ], 422);
        }
    }
}
```

### 单处理器注册

#### 使用HookServiceProvider
```php
use Modules\ABase\Support\HookServiceProvider;

class AuthModuleServiceProvider extends HookServiceProvider
{
    /**
     * 定义单处理器Hook接口
     * @var array<string>
     */
    protected array $hooks = [
        UserRegistrationHook::class,
        UserLoginHook::class,
        UserPasswordResetHook::class,
    ];

    /**
     * 单处理器Hook映射
     * @var array<string, array<string>>
     */
    protected array $hookHandlers = [
        UserRegistrationHook::class => [
            UserRegistrationHandler::class,
        ],
        UserLoginHook::class => [
            UserLoginHandler::class,
        ],
        UserPasswordResetHook::class => [
            UserPasswordResetHandler::class,
        ],
    ];
}
```

#### 手动注册单处理器
```php
use Modules\ABase\Hooks\Management\Hooks;

// 注册单处理器（推荐在服务提供者的boot方法中）
Hooks::add(UserRegistrationHook::class, UserRegistrationHandler::class, 1);

// 或者使用便捷方法
Hooks::addFirst(UserRegistrationHook::class, UserRegistrationHandler::class);
```

### 单处理器最佳实践

#### ✅ 单处理器推荐做法
1. **保持简单**：单个处理器专注于一个明确的任务
2. **完整处理**：在单个处理器中完成所有必要的逻辑
3. **错误处理**：妥善处理所有可能的异常情况
4. **事务处理**：涉及数据库操作时使用事务
5. **事件触发**：适当触发相关事件，便于其他模块响应
6. **详细日志**：记录关键操作和错误信息

#### ❌ 单处理器避免做法
1. **功能混杂**：避免在一个处理器中处理多个不相关的任务
2. **忽略验证**：不要忘记参数验证和数据校验
3. **异常泄漏**：避免将异常直接抛给调用方
4. **重复代码**：避免在多个单处理器中重复相同逻辑
5. **硬编码值**：避免在处理器中硬编码配置值

### 单处理器vs多处理器选择指南

#### 选择单处理器的场景
- **单一职责**：功能明确，只需要一个处理步骤
- **简单逻辑**：业务逻辑相对简单，不需要链式处理
- **性能要求**：对性能有较高要求，避免额外开销
- **结果可控**：需要完全控制处理结果，不受其他处理器影响

#### 选择多处理器的场景
- **复杂流程**：业务逻辑复杂，需要多个处理步骤
- **可扩展性**：需要支持第三方扩展或插件
- **模块化设计**：不同功能由不同模块提供
- **管道处理**：数据需要经过多层过滤和转换

### 单处理器示例场景

#### 1. 文件上传处理
```php
class FileUploadHook extends HookDefinition
{
    public readonly string $parameter_class;
    public readonly string $return_class;
    public string $description = '文件上传单处理器';

    public function __construct()
    {
        $this->parameter_class = FileUploadParameter::class;
        $this->return_class = FileUploadResult::class;
        parent::__construct();
    }
}

class FileUploadHandler implements HookHandlerInterface
{
    public static function handle(HookParameterInterface $parameter, HookResultInterface $result): HookResultInterface
    {
        $data = $parameter->toArray();
        $file = $data['file'];

        try {
            // 验证文件类型和大小
            if (!self::validateFile($file)) {
                return FileUploadResult::failure(['文件格式不支持或大小超出限制']);
            }

            // 生成唯一文件名
            $filename = self::generateUniqueFilename($file);

            // 保存文件
            $path = $file->storeAs('uploads', $filename, 'public');

            // 生成访问URL
            $url = asset('storage/' . $path);

            return FileUploadResult::success($filename, $url, $file->getSize());

        } catch (\Exception $e) {
            return FileUploadResult::failure(['文件上传失败: ' . $e->getMessage()]);
        }
    }

    // ... 其他方法
}
```

#### 2. 密码重置处理
```php
class PasswordResetHook extends HookDefinition
{
    public readonly string $parameter_class;
    public readonly string $return_class;
    public string $description = '密码重置单处理器';

    public function __construct()
    {
        $this->parameter_class = PasswordResetParameter::class;
        $this->return_class = PasswordResetResult::class;
        parent::__construct();
    }
}

class PasswordResetHandler implements HookHandlerInterface
{
    public static function handle(HookParameterInterface $parameter, HookResultInterface $result): HookResultInterface
    {
        $data = $parameter->toArray();

        try {
            // 验证重置令牌
            $user = self::validateResetToken($data['token']);
            if (!$user) {
                return PasswordResetResult::failure(['重置令牌无效或已过期']);
            }

            // 更新密码
            $user->password = bcrypt($data['new_password']);
            $user->password_reset_token = null;
            $user->password_reset_expires_at = null;
            $user->save();

            // 发送密码重置成功通知
            $user->notify(new PasswordResetSuccessNotification());

            return PasswordResetResult::success($user->email);

        } catch (\Exception $e) {
            return PasswordResetResult::failure(['密码重置失败: ' . $e->getMessage()]);
        }
    }

    // ... 其他方法
}
```

## 最佳实践清单

### ✅ 推荐做法
- 使用`readonly`属性定义参数和返回值类
- 处理器方法全部使用`static`关键字
- 实现`shouldExecute`方法进行条件判断
- 使用合理的优先级（1-100）
- 添加参数验证逻辑
- 使用助手类`Hooks`而不是直接使用`HookManager`
- 启用调试模式进行问题排查
- **单处理器场景**：对于简单明确的功能，优先选择单处理器模式
- **完整处理**：单处理器中完成所有必要逻辑，避免依赖其他处理器
- **事务安全**：涉及数据库操作时使用事务保证数据一致性
- **Hook定义默认值**：Hook定义通过createSuccessResult()提供默认结果
- **Logic层封装**：Hook作为Logic层内部实现，对Service/Controller透明（已有Logic封装的场景）
- **Trait组合**：使用HookServiceProviderTrait替代继承式架构
- **极简结果**：结果类只返回核心数据（如feeRate），不计算金额
- **只处理有效字段**：结果类只返回真正有用、会被外部使用的字段，删除冗余字段

### ❌ 避免做法
- 在处理器中使用实例方法
- 忘记实现`shouldExecute`方法
- 使用超出范围的优先级
- 在Hook中触发相同的Hook（避免无限循环）
- 直接操作`null`值而不检查
- 忽略类型提示和返回值类型
- **单处理器中混杂多个不相关功能**
- **在单处理器中留有"未完成"的处理逻辑**
- **忽略异常处理，让异常直接泄漏**
- **过度设计结果类**：不要返回多个字段（如7个字段），保持极简
- **Hook计算金额**：Hook只返回比率，金额在业务层计算
- **使用继承式ServiceProvider**：改用Trait组合

## 服务提供者架构

### 使用Trait组合（推荐）

模块应该使用`HookServiceProviderTrait` trait来管理Hook注册：

```php
use Modules\ABase\Support\Traits\HookServiceProviderTrait;
use Illuminate\Support\ServiceProvider;

class MyModuleServiceProvider extends ServiceProvider
{
    use HookServiceProviderTrait;  // ✅ Trait组合

    /**
     * Hook处理器映射
     * @var array<string, array<string>>
     */
    protected array $hookHandlers = [
        ContentFilterHook::class => [
            ContentCleanerHandler::class,
            ContentFormatterHandler::class,
        ],
    ];
}
```

**优势**:
- ✅ Trait组合：可与Event、Route等特性组合
- ✅ 低耦合：不强制继承基类
- ✅ 灵活复用：可用于任何ServiceProvider
- ✅ 符合Laravel惯例：类似RouteServiceProviderTrait

### 使用HookServiceProvider基类（旧方式）

模块应该继承`Modules\ABase\Support\HookServiceProvider`来管理Hook注册：

```php
use Modules\ABase\Support\HookServiceProvider;

class MyModuleHookServiceProvider extends HookServiceProvider
{
    /**
     * 定义Hook接口
     * @var array<string>
     */
    protected array $hooks = [
        ContentFilterHook::class,
        DataValidatorHook::class,
    ];

    /**
     * Hook处理器映射
     * @var array<string, array<string>>
     */
    protected array $hookHandlers = [
        ContentFilterHook::class => [
            ContentCleanerHandler::class,
            ContentFormatterHandler::class,
        ],
        DataValidatorHook::class => [
            EmailValidatorHandler::class,
            PhoneValidatorHandler::class,
        ],
    ];

    /**
     * Hook订阅者
     * @var array<string>
     */
    protected array $hookSubscribers = [
        ContentSubscriber::class,
        ValidationSubscriber::class,
    ];
}
```

### 手动注册（不推荐）

```php
use Modules\ABase\Hooks\Management\Hooks;

class MyModuleServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        // 注册处理器（推荐使用HookServiceProviderTrait）
        Hooks::add(ContentFilterHook::class, ContentCleanerHandler::class, 5);
        Hooks::add(ContentFilterHook::class, ContentFormatterHandler::class, 15);

        // 注册订阅者
        Hooks::subscribe(ContentSubscriber::class);
    }
}
```

### 在控制器中使用
```php
use Modules\ABase\Hooks\Management\Hooks;

class PostController extends Controller
{
    public function store(Request $request)
    {
        // 创建参数
        $parameter = ContentParameter::create(
            name: 'post_content',
            value: $request->content,
            options: ['user_id' => auth()->id()]
        );

        // 应用Hook处理内容
        $result = Hooks::apply(ContentFilterHook::class, $parameter);

        if ($result->isSuccess()) {
            $processedContent = $result->getProcessedContent();
            // 保存处理后的内容
        }
    }
}
```

## 性能优化提示

1. **优先级设置**：简单快速的处理使用高优先级（1-10）
2. **条件执行**：使用`shouldExecute`避免不必要的处理
3. **调试模式**：生产环境关闭调试模式
4. **批量处理**：使用`addMany`批量注册处理器
5. **订阅者模式**：相关处理器集中到订阅者类中

## 实践案例：RewardFeeHook简化

### 背景

原设计有7个字段，过度复杂：
```php
// ❌ 7个字段，过度设计
class RewardFeeResult extends HookResult
{
    public readonly float $baseFeeAmount;      // 基础手续费金额
    public readonly float $finalFeeAmount;     // 最终手续费金额
    public readonly float $feePercentage;      // 手续费百分比
    public readonly float $actualAmount;       // 创作者实际收益
    public readonly array $processingDetails;  // 处理明细
    public readonly float $discountAmount;     // 折扣金额
    public readonly float $additionalFee;      // 附加手续费
}
```

### 简化改进

回归本质：手续费是一个比率（0.5 = 50%）

```php
// ✅ 1个字段，回归本质
class RewardFeeResult extends HookResult
{
    /**
     * 手续费率（0.0-1.0之间）
     * - 0.05 表示 5%手续费
     * - 0.6 表示 60%手续费
     * - 1.0 表示 100%手续费
     */
    public readonly float $feeRate;

    public function __construct(float $feeRate = 0.05)
    {
        $this->feeRate = $feeRate;
        parent::__construct(true);
    }

    public static function success(float $feeRate = 0.05): static
    {
        return new static($feeRate);
    }
}
```

### Hook定义提供默认值

```php
class RewardFeeHook extends HookDefinition
{
    public function createSuccessResult(mixed $data = [], string $message = ''): RewardFeeResult
    {
        $rewardType = $data['reward_type'] ?? 'normal';
        $feeRate = $this->getFeeRate($rewardType);
        return RewardFeeResult::success(feeRate: $feeRate);
    }

    private function getFeeRate(string $rewardType): float
    {
        // 普通打赏：5%
        // 超级打赏：8%
        // 月票打赏：3%
        return match($rewardType) {
            'super' => 0.08,
            'monthly' => 0.03,
            default => 0.05,
        };
    }
}
```

### Logic层封装Hook调用

```php
class RewardLogic
{
    public static function calculateFee(
        float $amount,
        string $rewardType = 'normal',
        int $userId = 0,
        int $targetUserId = 0,
        ...
    ): array {
        // Hook 是内部实现（对外透明）
        $parameter = RewardFeeParameter::create(...);
        $result = Hooks::apply(RewardFeeHook::class, $parameter);

        // Logic 层计算金额
        $feeRate = $result->feeRate;
        $feeAmount = $amount * $feeRate;
        $creatorAmount = $amount - $feeAmount;

        // 返回完整结果
        return [
            'fee_rate' => $feeRate,
            'fee_amount' => $feeAmount,
            'creator_amount' => $creatorAmount,
        ];
    }
}
```

### Service层简洁调用

```php
// ✅ Service层不知道Hook的存在
class RewardService
{
    public static function create(...): RewardRecord
    {
        // 一行代码完成计算
        $feeResult = RewardLogic::calculateFee($amount, 'normal', $userId, $targetUserId);

        $feeAmount = $feeResult['fee_amount'];
        $actualAmount = $feeResult['creator_amount'];
    }
}
```

### 效果对比

| 方面 | 原设计 | 简化后 |
|------|--------|--------|
| 结果字段 | 7个（含冗余） | 1个（只有效字段） |
| 调用复杂度 | 7行代码 | 1行代码 |
| Hook暴露 | Service层可见 | Logic内部可见（有封装场景） |
| 理解难度 | 需理解7字段 | 只理解1比率 |

**核心原则**：只返回真正有用、会被外部使用的字段，删除冗余字段（如processingDetails、discountAmount等）

## 文档索引

### 完整实践经验总结
- [Hook实践经验总结.md](../../../Modules/ABase/docs/Hook实践经验总结.md) - 完整架构改进历程、设计原则、最佳实践

### 设计文档
- [22-2200-手续费计算Hook系统设计.md](../../../AiWork/22-2200-手续费计算Hook系统设计.md)
- [22-2215-打赏手续费Hook系统设计（修正版）.md](../../../AiWork/22-2215-打赏手续费Hook系统设计（修正版）.md)
- [22-2250-Referral个人等级手续费Hook设计.md](../../../AiWork/22-2250-Referral个人等级手续费Hook设计.md)

### 实施文档
- [22-2230-打赏手续费Hook实施总结.md](../../../AiWork/22-2230-打赏手续费Hook实施总结.md)
- [23-0036-Referral个人等级手续费Hook实施完成.md](../../../AiWork/23-0036-Referral个人等级手续费Hook实施完成.md)
- [23-0040-HookServiceProvider架构改进-Trait替代基类.md](../../../AiWork/23-0040-HookServiceProvider架构改进-Trait替代基类.md)

### 简化改进文档
- [23-0045-RewardFeeHook简化-手续费只是比率.md](../../../AiWork/23-0045-RewardFeeHook简化-手续费只是比率.md)
- [23-0050-Hook是Logic层内部实现细节.md](../../../AiWork/23-0050-Hook是Logic层内部实现细节.md)

---

*这份文档为LLM优化设计，提供快速参考和常用模式。完整实践经验请参考 Hook实践经验总结.md*