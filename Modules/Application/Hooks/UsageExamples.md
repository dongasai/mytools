# 组织架构Hook系统使用示例

本文档提供组织架构Hook系统的详细使用示例和最佳实践。

## 1. 基本使用模式

### 1.1 获取组织类型

```php
use Modules\Application\Hooks\Definitions\OrganizationTypeHookDefinition;
use Modules\Application\Hooks\Parameters\OrganizationTypeHookParameter;
use Modules\ABase\Hooks\Management\HookManager;
use Modules\Workflow\Enums\USER_TYPE;

// 创建参数
$parameter = new OrganizationTypeHookParameter(
    user_type: USER_TYPE::APPROVER
);

// 应用Hook
$result = HookManager::apply(OrganizationTypeHookDefinition::class, $parameter);

// 检查结果
if ($result->isSuccess()) {
    // 获取组织类型选项（用于表单下拉选择）
    $typeOptions = $result->getTypeOptions();
    /*
    [
        'department' => '部门',
        'role' => '角色',
        'position' => '职位',
        'admin_role' => '后台角色',
        'admin_user' => '后台用户'
    ]
    */

    // 获取详细选择器数据
    $selectOptions = $result->getSelectOptions();
    /*
    [
        ['value' => 'department', 'label' => '部门', 'description' => '...'],
        ['value' => 'admin_role', 'label' => '后台角色', 'description' => '...'],
        ...
    ]
    */

    // 检查是否包含特定类型
    if ($result->hasType('admin_role')) {
        $adminRoleType = $result->getOrganizationType('admin_role');
        echo $adminRoleType->type_name; // 输出: 后台角色
    }
} else {
    // 处理错误
    $errors = $result->getErrors();
    foreach ($errors as $error) {
        echo "Error: {$error}\n";
    }
}
```

### 1.2 获取组织成员

```php
use Modules\Application\Hooks\Definitions\OrganizationMemberHookDefinition;
use Modules\Application\Hooks\Parameters\OrganizationMemberHookParameter;

// 创建参数 - 基本用法
$parameter = new OrganizationMemberHookParameter(
    user_type: USER_TYPE::APPROVER,
    organization_type: 'admin_role',
    user_module_type: 'admin',
    limit: 20
);

$result = HookManager::apply(OrganizationMemberHookDefinition::class, $parameter);

if ($result->isSuccess()) {
    // 获取成员列表
    $members = $result->getMembers();

    // 获取简化格式成员数组
    $simpleMembers = $result->getMembersSimple();

    // 获取选择器格式（用于人员选择组件）
    $selectOptions = $result->getSelectOptions();
    /*
    [
        ['value' => 1, 'label' => '系统管理员', 'description' => 'admin_role - admin'],
        ['value' => 2, 'label' => '内容管理员', 'description' => 'admin_role - admin'],
        ...
    ]
    */

    // 按组织类型过滤
    $adminRoleMembers = $result->getMembersByOrgType('admin_role');
    $adminUserMembers = $result->getMembersByOrgType('admin_user');

    // 按用户类型过滤
    $adminUsers = $result->getMembersByUserType('admin');
    $accountUsers = $result->getMembersByUserType('account');

    // 分页信息
    $pagination = $result->getPaginationInfo();
    /*
    [
        'total' => 50,
        'count' => 20,
        'has_more' => true
    ]
    */
}
```

### 1.3 搜索和分页

```php
// 带搜索条件的参数
$parameter = new OrganizationMemberHookParameter(
    user_type: USER_TYPE::APPROVER,
    organization_type: 'admin_user',
    search: '管理',  // 搜索包含"管理"的成员
    limit: 10,
    offset: 0
);

$result = HookManager::apply(OrganizationMemberHookDefinition::class, $parameter);

// 后续分页请求
$nextPageParameter = new OrganizationMemberHookParameter(
    user_type: USER_TYPE::APPROVER,
    organization_type: 'admin_user',
    search: '管理',
    limit: 10,
    offset: 10  // 第二页
);
```

## 2. 部门相关Hook

### 2.1 获取部门负责人

```php
use Modules\Application\Hooks\Definitions\DepartmentLeaderHookDefinition;
use Modules\Application\Hooks\Parameters\DepartmentLeaderHookParameter;

$parameter = new DepartmentLeaderHookParameter(
    user_type: USER_TYPE::APPROVER,
    department_id: 1,  // 技术部
    leader_types: ['manager', 'director'],  // 只要经理和总监
    include_secondary: false  // 不包含副职
);

$result = HookManager::apply(DepartmentLeaderHookDefinition::class, $parameter);

if ($result->hasLeaders()) {
    // 获取主要负责人
    $primaryLeader = $result->getPrimaryLeader();
    echo "主要负责人: {$primaryLeader['name']}\n";

    // 获取所有经理
    $managers = $result->getManagers();
    foreach ($managers as $manager) {
        echo "经理: {$manager['name']} (级别: {$manager['level']})\n";
    }

    // 按级别分组
    $leadersByLevel = $result->groupByLevel();
    foreach ($leadersByLevel as $level => $leaders) {
        echo "级别 {$level}:\n";
        foreach ($leaders as $leader) {
            echo "  - {$leader['name']} ({$leader['type']})\n";
        }
    }

    // 部门信息
    echo "部门: {$result->getDepartmentName()}\n";
}
```

### 2.2 获取部门审批人

```php
use Modules\Application\Hooks\Definitions\DepartmentApproverHookDefinition;
use Modules\Application\Hooks\Parameters\DepartmentApproverHookParameter;

$parameter = new DepartmentApproverHookParameter(
    user_type: USER_TYPE::APPROVER,
    department_id: 1,
    approval_levels: [1, 2],  // 一级和二级审批人
    include_active_only: true
);

$result = HookManager::apply(DepartmentApproverHookDefinition::class, $parameter);

if ($result->hasApprovers()) {
    // 获取一级审批人
    $level1Approvers = $result->getLevel1Approvers();

    // 获取费用审批人
    $expenseApprovers = $result->getExpenseApprovers();

    // 按审批级别分组
    $approversByLevel = $result->groupByLevel();

    // 获取支持的工作流类型
    $workflows = $result->getSupportedWorkflows();
    echo "支持的工作流: " . implode(', ', $workflows) . "\n";

    // 按工作流分组审批人
    $approversByWorkflow = $result->groupByWorkflow();
}
```

## 3. 上级关系Hook

### 3.1 获取直接上级

```php
use Modules\Application\Hooks\Definitions\SuperiorHookDefinition;
use Modules\Application\Hooks\Parameters\SuperiorHookParameter;

$parameter = new SuperiorHookParameter(
    user_type: USER_TYPE::APPROVER,
    user_id: 1,
    relationship_type: 'direct',  // 直接汇报关系
    max_levels: 2,  // 最多2级
    include_active_only: true
);

$result = HookManager::apply(SuperiorHookDefinition::class, $parameter);

if ($result->hasSuperiors()) {
    // 获取直接上级
    $directSuperiors = $result->getDirectSuperiors();

    // 获取上级关系链
    $relationshipChain = $result->getRelationshipChain();

    // 按层级排序
    $sortedResult = $result->sortByLevel();
    $superiors = $sortedResult->getSuperiors();

    foreach ($superiors as $superior) {
        echo "级别 {$superior['level']}: {$superior['name']} ({$superior['relationship']})\n";
    }
}
```

### 3.2 冒泡查找审批人

```php
use Modules\Application\Hooks\Definitions\BubbleSuperiorHookDefinition;
use Modules\Application\Hooks\Parameters\BubbleSuperiorHookParameter;

$parameter = new BubbleSuperiorHookParameter(
    user_type: USER_TYPE::APPROVER,
    user_id: 1,
    start_level: 1,
    max_level: 5,
    required_permissions: ['approve_expense', 'manage_team']
);

$result = HookManager::apply(BubbleSuperiorHookDefinition::class, $parameter);

// 查找结果
echo "查找层级: {$result->getHierarchyCount()}\n";
echo "找到审批人: {$result->getApproverCount()}\n";

if ($result->hasSuitableApprovers()) {
    // 获取最佳审批人
    $bestApprover = $result->getBestApprover();
    echo "推荐审批人: {$bestApprover['user']['name']}\n";

    // 获取所有合适的审批人
    $suitableApprovers = $result->getSuitableApprovers();

    // 查找路径
    $hierarchySummary = $result->getHierarchySummary();
    foreach ($hierarchySummary as $level) {
        echo "层级 {$level['level']}: {$level['user_name']}\n";
    }

    // 性能数据
    $duration = $result->getSearchDuration();
    echo "查找耗时: {$duration}ms\n";
}
```

## 4. 工作流集成示例

### 4.1 工作流审批人配置

```php
class WorkflowApproverConfig
{
    public function getAvailableApprovers(string $workflowType): array
    {
        $approvers = [];

        // 1. 获取组织类型
        $typeResult = HookManager::apply(OrganizationTypeHookDefinition::class,
            new OrganizationTypeHookParameter(USER_TYPE::APPROVER)
        );

        if (!$typeResult->isSuccess()) {
            return $approvers;
        }

        // 2. 遍历每个组织类型获取成员
        foreach ($typeResult->getOrganizationTypes() as $orgType) {
            $memberResult = HookManager::apply(OrganizationMemberHookDefinition::class,
                new OrganizationMemberHookParameter(
                    user_type: USER_TYPE::APPROVER,
                    organization_type: $orgType->type_id,
                    limit: 50
                )
            );

            if ($memberResult->isSuccess()) {
                foreach ($memberResult->getMembers() as $member) {
                    $approvers[] = [
                        'id' => $member->id,
                        'name' => $member->getDisplayName(),
                        'organization_type' => $member->organization_type,
                        'user_type' => $member->user_type,
                        'organization_name' => $orgType->type_name
                    ];
                }
            }
        }

        return $approvers;
    }

    public function findNextApprover(int $currentUserId, array $requiredPermissions): ?array
    {
        // 使用冒泡上级查找合适的审批人
        $result = HookManager::apply(BubbleSuperiorHookDefinition::class,
            new BubbleSuperiorHookParameter(
                user_type: USER_TYPE::APPROVER,
                user_id: $currentUserId,
                max_level: 5,
                required_permissions: $requiredPermissions
            )
        );

        if ($result->hasSuitableApprovers()) {
            $bestApprover = $result->getBestApprover();
            return $bestApprover['user'];
        }

        return null;
    }
}
```

### 4.2 部门审批流

```php
class DepartmentApprovalFlow
{
    public function createApprovalChain(int $departmentId, int $initiatorId): array
    {
        $approvalChain = [];

        // 1. 获取部门负责人作为第一级审批
        $leaderResult = HookManager::apply(DepartmentLeaderHookDefinition::class,
            new DepartmentLeaderHookParameter(
                user_type: USER_TYPE::APPROVER,
                department_id: $departmentId,
                leader_types: ['manager', 'director']
            )
        );

        if ($leaderResult->hasLeaders()) {
            $primaryLeader = $leaderResult->getPrimaryLeader();
            $approvalChain[] = [
                'level' => 1,
                'approver' => $primaryLeader,
                'type' => 'department_leader'
            ];
        }

        // 2. 获取部门审批人作为第二级审批
        $approverResult = HookManager::apply(DepartmentApproverHookDefinition::class,
            new DepartmentApproverHookParameter(
                user_type: USER_TYPE::APPROVER,
                department_id: $departmentId,
                approval_levels: [2],
                include_active_only: true
            )
        );

        if ($approverResult->hasApproversAtLevel(2)) {
            $level2Approvers = $approverResult->getApproversByLevel(2);
            foreach ($level2Approvers as $approver) {
                $approvalChain[] = [
                    'level' => 2,
                    'approver' => $approver,
                    'type' => 'department_approver'
                ];
            }
        }

        // 3. 如果没有找到合适的审批人，向上冒泡查找
        if (empty($approvalChain)) {
            $bubbleResult = HookManager::apply(BubbleSuperiorHookDefinition::class,
                new BubbleSuperiorHookParameter(
                    user_type: USER_TYPE::APPROVER,
                    user_id: $initiatorId,
                    max_level: 3,
                    required_permissions: ['approve_department_workflow']
                )
            );

            if ($bubbleResult->hasSuitableApprovers()) {
                $superior = $bubbleResult->getBestApprover();
                $approvalChain[] = [
                    'level' => 3,
                    'approver' => $superior['user'],
                    'type' => 'superior_approver'
                ];
            }
        }

        return $approvalChain;
    }
}
```

## 5. 错误处理和调试

### 5.1 错误处理最佳实践

```php
function getOrganizationDataSafely(USER_TYPE $userType): array
{
    try {
        // 启用Hook调试模式
        HookManager::enableDebug();

        // 获取组织类型
        $typeResult = HookManager::apply(OrganizationTypeHookDefinition::class,
            new OrganizationTypeHookParameter($userType)
        );

        if ($typeResult->isFailure()) {
            // 记录错误
            logger()->error('获取组织类型失败', [
                'user_type' => $userType->value,
                'errors' => $typeResult->getErrors(),
                'message' => $typeResult->getMessage()
            ]);

            return [];
        }

        return $typeResult->getOrganizationTypesSimple();

    } catch (\Exception $e) {
        // 记录异常
        logger()->error('组织架构Hook系统异常', [
            'user_type' => $userType->value,
            'exception' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);

        return [];
    }
}
```

### 5.2 调试和监控

```php
// 设置Hook日志记录器
HookManager::setLogger(function ($message, $context = []) {
    logger()->debug("[Organization Hook] {$message}", $context);
});

// 获取Hook统计信息
$stats = [
    'registered_hooks' => count(HookManager::getRegisteredHooks()),
    'active_subscribers' => count(HookManager::getSubscribers()),
    'execution_time' => microtime(true) - $startTime,
];

logger()->info('Hook系统执行统计', $stats);
```

## 6. 性能优化建议

### 6.1 缓存策略

```php
class CachedOrganizationData
{
    private static array $cache = [];
    private static int $cacheTtl = 3600; // 1小时

    public static function getOrganizationTypes(USER_TYPE $userType): array
    {
        $cacheKey = "org_types_{$userType->value}";

        if (isset(self::$cache[$cacheKey]) &&
            (time() - self::$cache[$cacheKey]['timestamp']) < self::$cacheTtl) {
            return self::$cache[$cacheKey]['data'];
        }

        $result = HookManager::apply(OrganizationTypeHookDefinition::class,
            new OrganizationTypeHookParameter($userType)
        );

        $data = $result->isSuccess() ? $result->getOrganizationTypesSimple() : [];

        self::$cache[$cacheKey] = [
            'data' => $data,
            'timestamp' => time()
        ];

        return $data;
    }
}
```

### 6.2 批量操作

```php
class BatchOrganizationOperations
{
    public function getBatchMembers(array $orgTypes): array
    {
        $allMembers = [];

        foreach ($orgTypes as $orgType) {
            $result = HookManager::apply(OrganizationMemberHookDefinition::class,
                new OrganizationMemberHookParameter(
                    user_type: USER_TYPE::APPROVER,
                    organization_type: $orgType,
                    limit: 100
                )
            );

            if ($result->isSuccess()) {
                $allMembers = array_merge($allMembers, $result->getMembersSimple());
            }
        }

        return $allMembers;
    }
}
```

通过这些示例，你可以看到组织架构Hook系统的强大功能和灵活性。它提供了统一的数据访问接口，支持多种查询方式，并且能够很好地与工作流等业务系统集成。
