# Application 模块 Hook 系统 User

**版本**: 1.0.0
**最后更新**: 2025-11-12
**状态**: 文档设计阶段

## 概述

Application 模块提供应用级别的Hook定义，专注于用户管理和通用业务逻辑。这些Hook可以被其他模块订阅和扩展，为整个应用系统提供统一的数据获取接口。

## Hook 分类

### 1. 用户管理 Hooks

用户管理相关的Hook提供系统中用户数据的获取、过滤和扩展能力。

#### 1.1 用户类型获取Hook (UserTypeHook)

**Hook名称**: `application.user.type`

**用途**: 获取系统中可用的用户类型

**场景**:
- 用户管理界面选择用户类型
- 表单配置中选择用户来源
- 权限系统中的用户类型过滤

**参数类**: `UserTypeHookParameter`
```php
class UserTypeHookParameter extends HookParameter
{
    public function __construct(
        public readonly array $context = [],        // 上下文信息
        public readonly string $search = '',        // 搜索关键词
        public readonly bool $enabled_only = false  // 仅获取启用项
    ) {}
}
```

**结果类**: `UserTypeResult`
```php
class UserTypeResult extends HookResult
{
    public function __construct(
        public readonly array $user_types = [],       // 用户类型列表：[UserType实例]
        public readonly array $metadata = [],         // 元数据：['source_modules' => ['admin', 'shop']]
        bool $success = true,
        string $message = ''
    ) {}
}
```

**使用示例**:
```php
$userTypeResult = HookManager::apply(UserTypeHookDefinition::class,
    new UserTypeHookParameter(
        search: '管理员',
        enabled_only: true
    )
);

// 获取用户类型选项
$typeOptions = $userTypeResult->getTypeOptions();
// 返回: ['account' => '系统用户', 'admin' => '管理员', 'shop' => '商城用户']
```

#### 1.2 用户列表获取Hook (UserListHook)

**Hook名称**: `application.user.list`

**用途**: 根据用户类型获取具体的用户列表

**场景**:
- 工作流中选择审批人
- 用户管理中的用户搜索
- 消息通知中的收件人选择

**参数类**: `UserListHookParameter`
```php
class UserListHookParameter extends HookParameter
{
    public function __construct(
        public readonly USER_TYPE $user_type,       // 人员类型：cc/approver/notifier
        public readonly string $type = '',           // 用户类型：'account', 'admin', 'shop'
        public readonly string $search = '',         // 搜索关键词：'张三' 或 'admin'
        public readonly int $limit = 50,             // 限制返回数量
        public readonly int $offset = 0              // 分页偏移
    ) {}
}
```

**结果类**: `UserListResult`
```php
class UserListResult extends HookResult
{
    public function __construct(
        public readonly array $users = [],           // 用户列表：[User实例]
        public readonly int $total_count = 0,        // 总数量
        public readonly int $page_count = 0,         // 总页数
        public readonly array $metadata = [],        // 元数据
        bool $success = true,
        string $message = ''
    ) {}
}
```

**使用示例**:
```php
$userListResult = HookManager::apply(UserListHookDefinition::class,
    new UserListHookParameter(
        user_type: USER_TYPE::APPROVER,
        type: 'admin',
        search: '张三',
        limit: 20
    )
);

// 获取用户列表
$users = $userListResult->getUsers();
$totalCount = $userListResult->getCount();

// 根据类型过滤
$adminUsers = $userListResult->getUsersByType('admin');
```

#### 1.3 用户详情获取Hook (UserDetailHook)

**Hook名称**: `application.user.detail`

**用途**: 获取用户的详细信息

**场景**:
- 工作流中显示审批人信息
- 用户资料页面展示
- 用户权限验证

**参数类**: `UserDetailHookParameter`
```php
class UserDetailHookParameter extends HookParameter
{
    public function __construct(
        public readonly int $user_id = 0,                    // 用户ID
        public readonly array $fields = [],                  // 需要返回的字段：['name', 'email', 'avatar']
        public readonly bool $include_permissions = false    // 是否包含权限信息
    ) {}
}
```

**结果类**: `UserDetailResult`
```php
class UserDetailResult extends HookResult
{
    public function __construct(
        public readonly ?User $user = null,            // 用户详情：User对象
        public readonly array $metadata = [],          // 元数据
        bool $success = true,
        string $message = ''
    ) {}
}
```

**使用示例**:
```php
$userDetailResult = HookManager::apply(UserDetailHookDefinition::class,
    new UserDetailHookParameter(
        user_id: 123,
        fields: ['name', 'email', 'avatar'],
        include_permissions: true
    )
);

if ($userDetailResult->hasUser()) {
    $user = $userDetailResult->getUser();
    echo "用户姓名: " . $user->name;
    echo "邮箱: " . $user->email;
}
```

## 辅助类 (DTOs)

### UserType 类
位置：`Modules\Application\Dtos\UserType`

```php
class UserType
{
    public function __construct(
        public readonly string $type_id,     // 类型标识：'account', 'admin', 'shop'
        public readonly string $type_name,   // 类型中文：'系统用户', '管理员', '商城用户'
    ) {}

    public static function create(string $typeId, string $typeName): self;
    public function toArray(): array;
}
```

### User 类
位置：`Modules\Application\Dtos\User`

```php
class User
{
    public function __construct(
        public readonly int $id,                    // 用户ID
        public readonly string $name,               // 姓名
        public readonly string $type,               // 用户类型：'account', 'admin', 'shop'
        public readonly string $email = '',         // 邮箱
        public readonly string $avatar = ''         // 头像
    ) {}

    public static function create(int $id, string $name, string $type, string $email = '', string $avatar = ''): self;
    public function toArray(): array;
    public function toSimpleFormat(): array;
}
```

## 模块接入指南

### 1. 创建Hook订阅者

```php
<?php

namespace Modules\YourModule\Hooks\Subscribers;

use Modules\ABase\Hooks\Subscriber\HookSubscriber;
use Modules\Application\Hooks\Definitions\UserTypeHookDefinition;
use Modules\Application\Hooks\Parameters\UserTypeHookParameter;
use Modules\Application\Hooks\Results\UserTypeResult;
use Modules\Application\Dtos\UserType;

class ApplicationUserHookSubscriber extends HookSubscriber
{
    public function subscribe(): array
    {
        return [
            UserTypeHookDefinition::class => 'handleUserType',
        ];
    }

    public function handleUserType(UserTypeHookParameter $parameter): UserTypeResult
    {
        $result = new UserTypeResult();

        // 判断是否提供用户类型数据
        if ($this->shouldProvideUserTypes($parameter)) {
            $userType = UserType::create('shop', '商城用户');
            $result->addUserType($userType);
        }

        return $result;
    }

    private function shouldProvideUserTypes(UserTypeHookParameter $parameter): bool
    {
        // 根据业务逻辑判断是否提供数据
        if ($parameter->search && !str_contains('商城用户', $parameter->search)) {
            return false;
        }

        return true;
    }
}
```

### 2. 注册Hook订阅者

在模块的服务提供者中注册：

```php
<?php

namespace Modules\YourModule\Providers;

use Illuminate\Support\ServiceProvider;
use Modules\YourModule\Hooks\Subscribers\ApplicationUserHookSubscriber;

class YourModuleServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // 注册Hook订阅者
        $this->app->singleton(ApplicationUserHookSubscriber::class);

        // 注册到Hook管理器
        HookManager::subscribe($this->app->make(ApplicationUserHookSubscriber::class));
    }
}
```

## 目录结构

```
Modules/Application/
├── Dtos/                     # 数据传输对象
│   ├── User.php             # 用户DTO
│   └── UserType.php         # 用户类型DTO
└── Hooks/                   # Hook系统
    ├── Definitions/         # Hook定义
    │   ├── UserTypeHookDefinition.php
    │   ├── UserListHookDefinition.php
    │   └── UserDetailHookDefinition.php
    ├── Parameters/          # Hook参数类
    │   ├── UserTypeHookParameter.php
    │   ├── UserListHookParameter.php
    │   └── UserDetailHookParameter.php
    ├── Results/             # Hook结果类
    │   ├── UserTypeResult.php
    │   ├── UserListResult.php
    │   └── UserDetailResult.php
    ├── Subscribers/         # Hook订阅者目录
    └── README.md           # 本文档
```

## 最佳实践

### 1. 智能数据提供
- 根据搜索关键词智能过滤数据
- 考虑enabled_only参数的影响
- 避免返回重复的用户类型

### 2. 性能优化
- 对于大数据量，使用分页参数
- 实现高效的搜索逻辑
- 合理使用缓存机制

### 3. 类型安全
- 使用强类型的参数和返回值
- 正确处理空值和异常情况
- 遵循PHPDoc注释规范

### 4. 数据一致性
- 确保用户ID在整个系统中的一致性
- 统一用户类型的命名规范
- 维护用户信息的准确性

## 与Workflow模块的关系

Application模块的User相关Hook为Workflow模块提供基础的用户数据支持：

- **Workflow模块** 使用Application的用户Hook来获取审批人、抄送人等
- **Application模块** 专注于基础用户管理，不涉及工作流业务逻辑
- **数据流向**: Application → Workflow → 其他业务模块

这种分层设计确保了系统的模块化和可扩展性。
