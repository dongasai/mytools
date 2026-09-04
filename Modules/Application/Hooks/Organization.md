# Application 模块 Hook 系统 - 组织架构

> 提供统一的组织架构Hook系统，支持多模块组织架构数据聚合和工作流集成

**版本**: 1.0.0
**最后更新**: 2025-11-12
**状态**: 文档设计阶段

## 概述

组织架构Hook系统为Application模块提供统一的组织架构数据获取和过滤能力，支持：

- **多模块数据源**: 支持Admin、组织架构、权限等模块提供组织数据
- **统一接口**: 为Workflow等消费模块提供一致的数据访问接口
- **链式处理**: 支持多模块数据的逐步聚合和过滤
- **类型安全**: 严格的参数和返回值类型约束

## 组织架构Hook链

### 1. 组织类型获取Hook (OrganizationTypeHook)

**用途**: 获取系统中可用的组织架构类型

**场景**: 工作流配置时选择组织类型（如部门、角色、职位）

**数据流向**: 各模块提供组织类型 → Application模块汇总 → 消费模块使用

**示例数据**:
```php
[
    'department' => '部门',
    'role' => '角色',
    'position' => '职位',
    'team' => '团队',
    'project' => '项目组'
]
```

**使用示例**:
```php
use Modules\Application\Hooks\Definitions\OrganizationTypeHookDefinition;

$typeResult = HookManager::apply(OrganizationTypeHookDefinition::class,
    new OrganizationTypeHookParameter(
        user_type: USER_TYPE::APPROVER
    )
);

if ($typeResult->isSuccess()) {
    $orgTypes = $typeResult->getOrganizationTypesSimple();
    // ['department' => '部门', 'role' => '角色', ...]
}
```

### 2. 组织成员列表Hook (OrganizationMemberHook)

**用途**: 根据组织类型获取成员列表

**场景**: 选择特定组织类型下的成员作为审批人或通知对象

**数据流向**: 传入组织类型和过滤条件 → 各模块提供对应成员 → 汇总返回

**示例数据**:
```php
[
    ['id' => 1, 'organization_type' => 'department', 'user_type' => 'admin'],
    ['id' => 2, 'organization_type' => 'role', 'user_type' => 'account'],
    ['id' => 3, 'organization_type' => 'position', 'user_type' => 'shop']
]
```

**使用示例**:
```php
use Modules\Application\Hooks\Definitions\OrganizationMemberHookDefinition;

$memberResult = HookManager::apply(OrganizationMemberHookDefinition::class,
    new OrganizationMemberHookParameter(
        user_type: USER_TYPE::APPROVER,
        organization_type: 'department',
        user_module_type: 'admin',
        search: '技术',
        limit: 20
    )
);

if ($memberResult->isSuccess()) {
    $members = $memberResult->getMembersSimple();
    // 过滤后的成员列表
}
```

### 3. 部门负责人Hook (DepartmentLeaderHook)

**用途**: 获取部门的负责人信息

**场景**: 自动指定部门负责人作为审批人

**数据流向**: 传入部门ID → 返回负责人信息

**示例数据**:
```php
[
    'id' => 10,
    'name' => '技术部经理',
    'level' => 1,
    'department' => '技术部',
    'user_type' => 'admin'
]
```

### 4. 部门审批人Hook (DepartmentApproverHook)

**用途**: 获取具有审批权限的部门成员

**场景**: 选择部门内具有审批权限的人员

**示例数据**:
```php
[
    [
        'id' => 1,
        'name' => '审批员1',
        'approval_level' => 1,
        'workflows' => ['expense', 'leave'],
        'user_type' => 'admin'
    ]
]
```

### 5. 上级关系Hook (SuperiorHook)

**用途**: 获取用户的上级领导

**场景**: 自动将审批流转给上级

**示例数据**:
```php
[
    'id' => 3,
    'name' => '上级领导',
    'relationship' => 'direct',
    'level' => 1,
    'user_type' => 'admin'
]
```

### 6. 冒泡上级Hook (BubbleSuperiorHook)

**用途**: 获取多层级的上级领导（直到找到可审批的人）

**场景**: 多级审批流程中的上级查找

**示例数据**:
```php
[
    [
        'level' => 1,
        'user' => ['id' => 3, 'name' => '直接上级'],
        'permissions' => ['approve_expense']
    ],
    [
        'level' => 2,
        'user' => ['id' => 5, 'name' => '部门总监'],
        'permissions' => ['approve_large_expense']
    ]
]
```

## Hook结构定义

### 参数类设计原则
- **readonly属性**: 确保参数不可变
- **类型安全**: 严格类型定义
- **默认值**: 提供合理的默认值
- **验证机制**: 构造函数中验证参数有效性

### 结果类设计原则
- **链式累积**: 支持多模块结果合并
- **静态工厂**: 提供`success()`和`failure()`静态方法
- **元数据支持**: 包含执行过程和调试信息
- **错误处理**: 详细的错误信息和上下文

### 标准数据格式

**组织成员格式**:
```php
$organization_member_format = [
    'id' => int,                    // 用户ID
    'organization_type' => string,  // 组织类型：'department', 'role', 'position'
    'user_type' => string,          // 用户类型：'account', 'admin', 'shop'
];
```

**组织类型格式**:
```php
$organization_type_format = [
    'type_id' => string,      // 组织类型标识：'department', 'role'
    'type_name' => string,    // 组织类型名称：'部门', '角色'
];
```

## 模块集成

### Admin模块集成
- **OrganizationTypeHook**: 提供 'admin_role' → '后台角色'
- **OrganizationMemberHook**: 返回后台角色成员列表
- **DepartmentLeaderHook**: 返回部门管理员信息
- **DepartmentApproverHook**: 返回具有审批权限的后台用户

### 组织架构模块集成
- **OrganizationTypeHook**: 提供 'department', 'position', 'team' 等类型
- **OrganizationMemberHook**: 返回部门成员、职位成员
- **SuperiorHook**: 提供组织层级关系
- **BubbleSuperiorHook**: 支持多级上级查找

### 权限模块集成
- **DepartmentApproverHook**: 基于权限配置返回审批人
- **SuperiorHook**: 基于权限层级返回上级关系

## 使用场景

### 场景1: 工作流审批人配置
```php
// 获取组织类型
$typeResult = HookManager::apply(OrganizationTypeHookDefinition::class,
    new OrganizationTypeHookParameter(USER_TYPE::APPROVER)
);

// 获取特定类型的成员
$memberResult = HookManager::apply(OrganizationMemberHookDefinition::class,
    new OrganizationMemberHookParameter(
        user_type: USER_TYPE::APPROVER,
        organization_type: 'department',
        user_module_type: 'admin'
    )
);
```

### 场景2: 自动审批流转
```php
// 获取部门负责人
$leaderResult = HookManager::apply(DepartmentLeaderHookDefinition::class,
    new DepartmentLeaderHookParameter(
        user_type: USER_TYPE::APPROVER,
        department_id: $deptId,
        leader_types: ['manager', 'director']
    )
);

// 获取多级上级
$superiorResult = HookManager::apply(BubbleSuperiorHookDefinition::class,
    new BubbleSuperiorHookParameter(
        user_type: USER_TYPE::APPROVER,
        user_id: $userId,
        required_permissions: ['approve_expense']
    )
);
```

## Hook订阅者示例

```php
<?php

namespace Modules\Admin\Hooks\Subscribers;

use Modules\ABase\Hooks\Subscriber\HookSubscriber;
use Modules\Application\Hooks\Definitions\OrganizationTypeHookDefinition;
use Modules\Application\Hooks\Definitions\OrganizationMemberHookDefinition;
use Modules\Application\Hooks\Parameters\OrganizationTypeHookParameter;
use Modules\Application\Hooks\Parameters\OrganizationMemberHookParameter;
use Modules\Application\Hooks\Results\OrganizationTypeResult;
use Modules\Application\Hooks\Results\OrganizationMemberResult;
use Modules\Application\Hooks\Data\OrganizationType;
use Modules\Application\Hooks\Data\OrganizationMember;

class AdminOrganizationHookSubscriber extends HookSubscriber
{
    public function subscribe(): array
    {
        return [
            OrganizationTypeHookDefinition::class => 'provideOrganizationTypes',
            OrganizationMemberHookDefinition::class => 'provideOrganizationMembers',
        ];
    }

    public function provideOrganizationTypes(
        OrganizationTypeHookParameter $parameter,
        OrganizationTypeResult $result
    ): OrganizationTypeResult {
        // 提供后台角色组织类型
        $adminRole = OrganizationType::create('admin_role', '后台角色');
        return $result->addOrganizationType($adminRole);
    }

    public function provideOrganizationMembers(
        OrganizationMemberHookParameter $parameter,
        OrganizationMemberResult $result
    ): OrganizationMemberResult {
        // 只处理admin_role类型
        if ($parameter->organization_type !== 'admin_role') {
            return $result;
        }

        // 获取后台用户
        $adminUsers = $this->getAdminUsers([
            'search' => $parameter->search,
            'limit' => $parameter->limit
        ]);

        foreach ($adminUsers as $user) {
            $member = OrganizationMember::create(
                $user['id'],
                'admin_role',
                'admin'
            );
            $result->addMember($member);
        }

        return $result;
    }

    private function getAdminUsers(array $filters): array
    {
        // 实际的后台用户查询逻辑
        return [];
    }
}
```

## 最佳实践

### 1. 数据提供原则
- **智能过滤**: 根据参数智能判断是否提供数据
- **避免重复**: 多个模块避免提供相同数据
- **性能考虑**: 支持分页和搜索，大数据量优化

### 2. 错误处理
- **非阻塞**: 单个模块失败不影响其他模块执行
- **优雅降级**: 数据源不可用时返回空结果
- **详细日志**: 记录数据获取过程和错误信息

### 3. 类型安全
- **严格类型**: 使用强类型参数和返回值
- **空值处理**: 正确处理可选参数
- **数据验证**: 验证返回数据的完整性

## 性能优化

1. **缓存机制**: 缓存组织架构和成员数据
2. **延迟加载**: 按需加载详细信息
3. **批量查询**: 批量获取数据减少查询次数
4. **索引优化**: 为常用查询字段建立索引

这个组织架构Hook系统为Application模块提供了强大的组织架构数据聚合能力，支持Workflow等模块的无缝集成。

## 详细Hook结构定义

### 2.1 组织类型获取Hook (OrganizationTypeHook)

**参数类: OrganizationTypeParameter**
```php
class OrganizationTypeParameter extends HookParameter
{
    public function __construct(
        public readonly USER_TYPE $user_type  // 人员类型：cc/approver/notifier
    ) {}
}
```

**辅助类: OrganizationType**
```php
class OrganizationType
{
    public function __construct(
        public readonly string $type_id,        // 组织类型标识：'department', 'role', 'position'
        public readonly string $type_name,      // 组织类型名称：'部门', '角色', '职位'
    ) {}

    // 创建组织类型实例
    public static function create(string $typeId, string $typeName): self
    {
        return new self($typeId, $typeName);
    }

    // 转换为数组
    public function toArray(): array
    {
        return [
            'type_id' => $this->type_id,
            'type_name' => $this->type_name
        ];
    }

    // 转换为简化格式
    public function toSimpleFormat(): array
    {
        return [
            $this->type_id => $this->type_name
        ];
    }
}
```

**结果类: OrganizationTypeResult**
```php
class OrganizationTypeResult extends HookResult
{
    public function __construct(
        public readonly array $organization_types = [], // 组织类型数组：[OrganizationType实例]
        bool $success = true,
        string $message = ''
    ) {}

    public static function success(array $orgTypes): static;
    public static function failure(array $errors = []): static;

    // 添加组织类型（链式累积）
    public function addOrganizationType(OrganizationType $orgType): self;

    // 添加简化格式组织类型 - 向后兼容
    public function addOrganizationTypeSimple(string $typeId, string $typeName): self;

    // 合并组织类型数组
    public function mergeOrganizationTypes(array $additionalOrgTypes): self;

    // 获取所有OrganizationType对象
    public function getOrganizationTypes(): array;

    // 获取简化格式组织类型数组（向后兼容）
    public function getOrganizationTypesSimple(): array;

    // 检查是否包含特定类型
    public function hasType(string $typeId): bool;

    // 获取特定类型
    public function getOrganizationType(string $typeId): ?OrganizationType;

    // 获取类型总数
    public function getCount(): int;
}
```

### 2.2 组织成员列表Hook (OrganizationMemberHook)

**参数类: OrganizationMemberParameter**
```php
class OrganizationMemberParameter extends HookParameter
{
    public function __construct(
        public readonly USER_TYPE $user_type,           // 人员类型：cc/approver/notifier
        public readonly string $organization_type = '', // 组织类型：'department', 'role', 'position'
        public readonly int $organization_id = 0,       // 组织ID
        public readonly string $search = '',            // 搜索关键词：'张三' 或 '技术部'
        public readonly int $limit = 50,                // 限制数量
        public readonly int $offset = 0                 // 分页
    ) {}
}
```

**辅助类: OrganizationMember**
```php
class OrganizationMember
{
    public function __construct(
        public readonly int $id,                    // 用户ID
        public readonly string $organization_type,   // 组织类型：'department', 'role', 'position'
        public readonly string $user_type            // 用户类型：'account', 'admin', 'shop'
    ) {}

    // 创建成员实例
    public static function create(int $id, string $organizationType, string $userType): self
    {
        return new self(
            id: $id,
            organization_type: $organizationType,
            user_type: $userType
        );
    }

    // 转换为数组
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'organization_type' => $this->organization_type,
            'user_type' => $this->user_type
        ];
    }
}
```

**结果类: OrganizationMemberResult**
```php
class OrganizationMemberResult extends HookResult
{
    public function __construct(
        public readonly array $members = [],       // 成员列表：[OrganizationMember实例]
        public readonly int $total_count = 0,      // 总数量
        public readonly array $metadata = [],      // 元数据：['organization_info' => [...]]
        bool $success = true,
        string $message = ''
    ) {}

    public static function success(array $members, int $totalCount = 0, array $metadata = []): static;
    public static function failure(array $errors = []): static;

    // 添加成员（链式累积）- 接受OrganizationMember对象
    public function addMember(OrganizationMember $member): self;

    // 添加成员数组（链式累积）
    public function addMembers(array $newMembers): self;

    // 添加简化格式成员
    public function addMemberSimple(int $id, string $orgType, string $userType): self;

    // 合并成员列表
    public function mergeMemberLists(OrganizationMemberResult $other): self;

    // 获取所有OrganizationMember对象
    public function getMembers(): array;

    // 获取简化格式成员数组
    public function getMembersSimple(): array;

    // 根据组织类型过滤成员
    public function getMembersByOrgType(string $orgType): array;

    // 检查是否包含特定成员
    public function hasMember(int $memberId): bool;

    // 获取特定成员
    public function getMember(int $memberId): ?OrganizationMember;

    // 获取成员总数
    public function getCount(): int;
}
```

### 2.3 部门负责人Hook (DepartmentLeaderHook)

**参数类: DepartmentLeaderParameter**
```php
class DepartmentLeaderParameter extends HookParameter
{
    public function __construct(
        public readonly USER_TYPE $user_type,           // 人员类型：cc/approver/notifier
        public readonly int $department_id = 0,        // 部门ID
        public readonly array $leader_types = [],      // 负责人类型：['manager', 'director', 'head']
        public readonly bool $include_secondary = false // 包含副负责人
    ) {}
}
```

**结果类: DepartmentLeaderResult**
```php
class DepartmentLeaderResult extends HookResult
{
    public function __construct(
        public readonly array $leaders = [],       // 负责人列表：[{id: 1, name: '部门经理', type: 'manager', level: 1}]
        public readonly array $department = [],    // 部门信息
        public readonly array $metadata = [],      // 元数据
        bool $success = true,
        string $message = ''
    ) {}

    public static function success(array $leaders, array $department = [], array $metadata = []): static;
    public static function failure(array $errors = []): static;
}
```

### 2.4 部门审批人Hook (DepartmentApproverHook)

**参数类: DepartmentApproverParameter**
```php
class DepartmentApproverParameter extends HookParameter
{
    public function __construct(
        public readonly USER_TYPE $user_type,           // 人员类型：cc/approver/notifier
        public readonly int $department_id = 0,        // 部门ID
        public readonly array $approval_levels = [],   // 审批级别：[1, 2, 3]
        public readonly bool $include_active_only = true // 仅包含活跃审批人
    ) {}
}
```

**结果类: DepartmentApproverResult**
```php
class DepartmentApproverResult extends HookResult
{
    public function __construct(
        public readonly array $approvers = [],     // 审批人列表：[{id: 1, name: '审批员1', approval_level: 1, workflows: ['expense', 'leave']}]
        public readonly array $department = [],    // 部门信息
        public readonly array $metadata = [],      // 元数据
        bool $success = true,
        string $message = ''
    ) {}

    public static function success(array $approvers, array $department = [], array $metadata = []): static;
    public static function failure(array $errors = []): static;
}
```

### 2.5 上级关系Hook (SuperiorHook)

**参数类: SuperiorParameter**
```php
class SuperiorParameter extends HookParameter
{
    public function __construct(
        public readonly USER_TYPE $user_type,            // 人员类型：cc/approver/notifier
        public readonly int $user_id = 0,                // 用户ID
        public readonly string $relationship_type = '',  // 关系类型：'direct', 'functional', 'reporting'
        public readonly int $max_levels = 1,             // 最大层级
        public readonly bool $include_active_only = true  // 仅包含活跃上级
    ) {}
}
```

**结果类: SuperiorResult**
```php
class SuperiorResult extends HookResult
{
    public function __construct(
        public readonly array $superiors = [],      // 上级列表：[{id: 3, name: '上级领导', relationship: 'direct', level: 1}]
        public readonly array $metadata = [],       // 元数据：['org_hierarchy' => [...]]
        bool $success = true,
        string $message = ''
    ) {}

    public static function success(array $superiors, array $metadata = []): static;
    public static function failure(array $errors = []): static;
}
```

### 2.6 冒泡上级Hook (BubbleSuperiorHook)

**参数类: BubbleSuperiorParameter**
```php
class BubbleSuperiorParameter extends HookParameter
{
    public function __construct(
        public readonly USER_TYPE $user_type,            // 人员类型：cc/approver/notifier
        public readonly int $user_id = 0,                // 用户ID
        public readonly int $start_level = 1,            // 起始层级
        public readonly int $max_level = 5,              // 最大层级
        public readonly array $required_permissions = [] // 必需权限：['approve_expense', 'manage_team']
    ) {}
}
```

**结果类: BubbleSuperiorResult**
```php
class BubbleSuperiorResult extends HookResult
{
    public function __construct(
        public readonly array $superior_hierarchy = [], // 上级层级：[{level: 1, user: {...}, permissions: [...]}]
        public readonly array $found_approvers = [],   // 找到的审批人：[{level: 2, user: {...}, is_suitable: true}]
        public readonly array $metadata = [],          // 元数据
        bool $success = true,
        string $message = ''
    ) {}

    public static function success(array $hierarchy, array $approvers = [], array $metadata = []): static;
    public static function failure(array $errors = []): static;
}
```

## 执行流程示例

### 组织架构审批流程

```
1. DepartmentLeaderHook::apply(department_id: 5)
   └── 返回: {id: 10, name: '技术部经理', department: '技术部'}

2. BubbleSuperiorHook::apply(user_id: 10, level: 2)
   ├── Level 1: {id: 15, name: '技术总监'}
   └── Level 2: {id: 20, name: 'CTO'}
```

## 与现有模块的集成示例

**Admin模块订阅者示例：**

```php
class AdminOrganizationHookSubscriber extends AbstractHookSubscriber
{
    public function subscribe(): array
    {
        return [
            OrganizationTypeHook::class => 'provideOrganizationTypes',
            OrganizationMemberHook::class => 'provideOrganizationMembers',
        ];
    }

    public function provideOrganizationTypes(OrganizationTypeParameter $parameter, OrganizationTypeResult $result): OrganizationTypeResult
    {
        // 添加后台角色组织类型
        $orgType = OrganizationType::create('admin_role', '后台角色');
        return $result->addOrganizationType($orgType);
    }

    public function provideOrganizationMembers(OrganizationMemberHookParameter $parameter, OrganizationMemberResult $result): OrganizationMemberResult
    {
        // 只处理admin_role类型
        if ($parameter->organization_type !== 'admin_role') {
            return $result;
        }

        // 获取后台用户并添加到结果
        $adminUsers = $this->getAdminUsers($parameter);
        foreach ($adminUsers as $user) {
            $member = OrganizationMember::create($user['id'], 'admin_role', 'admin');
            $result->addMember($member);
        }

        return $result;
    }
}
```

