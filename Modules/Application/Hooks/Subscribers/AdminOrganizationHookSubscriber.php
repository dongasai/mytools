<?php

declare(strict_types=1);

namespace Modules\Application\Hooks\Subscribers;

use Modules\ABase\Hooks\Core\AbstractHookSubscriber;
use Modules\Application\Hooks\Definitions\OrganizationTypeHookDefinition;
use Modules\Application\Hooks\Definitions\OrganizationMemberHookDefinition;
use Modules\Application\Hooks\Definitions\DepartmentLeaderHookDefinition;
use Modules\Application\Hooks\Definitions\DepartmentApproverHookDefinition;
use Modules\Application\Hooks\Definitions\SuperiorHookDefinition;
use Modules\Application\Hooks\Definitions\BubbleSuperiorHookDefinition;
use Modules\Application\Hooks\Parameters\OrganizationTypeHookParameter;
use Modules\Application\Hooks\Parameters\OrganizationMemberHookParameter;
use Modules\Application\Hooks\Parameters\DepartmentLeaderHookParameter;
use Modules\Application\Hooks\Parameters\DepartmentApproverHookParameter;
use Modules\Application\Hooks\Parameters\SuperiorHookParameter;
use Modules\Application\Hooks\Parameters\BubbleSuperiorHookParameter;
use Modules\Application\Hooks\Results\OrganizationTypeResult;
use Modules\Application\Hooks\Results\OrganizationMemberResult;
use Modules\Application\Hooks\Results\DepartmentLeaderResult;
use Modules\Application\Hooks\Results\DepartmentApproverResult;
use Modules\Application\Hooks\Results\SuperiorResult;
use Modules\Application\Hooks\Results\BubbleSuperiorResult;
use Modules\Application\Hooks\Data\OrganizationType;
use Modules\Application\Hooks\Data\OrganizationMember;

/**
 * Admin模块组织架构Hook订阅者
 *
 * 为Application模块提供Admin模块的组织架构数据
 * 演示如何实现组织架构Hook订阅者
 */
class AdminOrganizationHookSubscriber extends AbstractHookSubscriber
{
    /**
     * 订阅的Hook映射
     */
    public function subscribe(): array
    {
        return [
            OrganizationTypeHookDefinition::class => 'provideOrganizationTypes',
            OrganizationMemberHookDefinition::class => 'provideOrganizationMembers',
            DepartmentLeaderHookDefinition::class => 'provideDepartmentLeaders',
            DepartmentApproverHookDefinition::class => 'provideDepartmentApprovers',
            SuperiorHookDefinition::class => 'provideSuperiors',
            BubbleSuperiorHookDefinition::class => 'provideBubbleSuperiors',
        ];
    }

    /**
     * 提供组织类型
     */
    public function provideOrganizationTypes(
        OrganizationTypeHookParameter $parameter,
        OrganizationTypeResult $result
    ): OrganizationTypeResult {
        // 提供Admin模块的组织类型
        $adminRoleType = OrganizationType::create(
            'admin_role',
            '后台角色',
            'Laravel Admin后台管理系统的角色权限组',
            'user-shield',
            ['source' => 'admin_module', 'permission_based' => true]
        );

        $adminUserType = OrganizationType::create(
            'admin_user',
            '后台用户',
            'Laravel Admin后台管理系统用户',
            'users',
            ['source' => 'admin_module', 'direct_assignment' => true]
        );

        return $result
            ->addOrganizationType($adminRoleType)
            ->addOrganizationType($adminUserType);
    }

    /**
     * 提供组织成员
     */
    public function provideOrganizationMembers(
        OrganizationMemberHookParameter $parameter,
        OrganizationMemberResult $result
    ): OrganizationMemberResult {
        // 只处理Admin模块相关的组织类型
        if (!$this->isAdminOrganizationType($parameter->organization_type)) {
            return $result;
        }

        // 模拟获取Admin用户数据
        $adminUsers = $this->getAdminUsers($parameter);

        foreach ($adminUsers as $user) {
            $member = OrganizationMember::create(
                $user['id'],
                $parameter->organization_type,
                $user['user_type'] ?? 'admin',
                $user['name'],
                ['source' => 'admin_module', 'role' => $user['role'] ?? null]
            );

            $result->addMember($member);
        }

        return $result;
    }

    /**
     * 提供部门负责人
     */
    public function provideDepartmentLeaders(
        DepartmentLeaderHookParameter $parameter,
        DepartmentLeaderResult $result
    ): DepartmentLeaderResult {
        // 模拟Admin模块的部门负责人数据
        $department = $this->getDepartmentInfo($parameter->department_id);
        if (empty($department)) {
            return $result;
        }

        // 查找该部门的管理员
        $adminLeaders = $this->getDepartmentAdminLeaders($parameter->department_id, $parameter->leader_types);

        foreach ($adminLeaders as $leader) {
            $result->addLeader($leader);
        }

        return $result->setDepartment($department);
    }

    /**
     * 提供部门审批人
     */
    public function provideDepartmentApprovers(
        DepartmentApproverHookParameter $parameter,
        DepartmentApproverResult $result
    ): DepartmentApproverResult {
        // 模拟Admin模块的部门审批人数据
        $department = $this->getDepartmentInfo($parameter->department_id);
        if (empty($department)) {
            return $result;
        }

        // 查找该部门有审批权限的管理员
        $adminApprovers = $this->getDepartmentAdminApprovers(
            $parameter->department_id,
            $parameter->approval_levels,
            $parameter->include_active_only
        );

        foreach ($adminApprovers as $approver) {
            $result->addApprover($approver);
        }

        return $result->setDepartment($department);
    }

    /**
     * 提供上级关系
     */
    public function provideSuperiors(
        SuperiorHookParameter $parameter,
        SuperiorResult $result
    ): SuperiorResult {
        // 获取用户的上级关系（基于Admin模块的权限层级）
        $userSuperiors = $this->getAdminUserSuperiors(
            $parameter->user_id,
            $parameter->relationship_type,
            $parameter->max_levels,
            $parameter->include_active_only
        );

        foreach ($userSuperiors as $superior) {
            $result->addSuperior($superior);
        }

        return $result;
    }

    /**
     * 提供冒泡上级
     */
    public function provideBubbleSuperiors(
        BubbleSuperiorHookParameter $parameter,
        BubbleSuperiorResult $result
    ): BubbleSuperiorResult {
        // 获取多层级上级关系（直到找到有权限的人）
        $hierarchy = $this->getAdminUserHierarchy(
            $parameter->user_id,
            $parameter->start_level,
            $parameter->max_level
        );

        foreach ($hierarchy as $level) {
            $result->addSuperiorLevel($level);
        }

        // 查找有权限的审批人
        $approvers = $this->findAdminApproversInHierarchy(
            $hierarchy,
            $parameter->required_permissions
        );

        foreach ($approvers as $approver) {
            $result->addFoundApprover($approver);
        }

        return $result;
    }

    /**
     * 检查是否为Admin相关的组织类型
     */
    private function isAdminOrganizationType(string $orgType): bool
    {
        return in_array($orgType, ['admin_role', 'admin_user'], true);
    }

    /**
     * 获取Admin用户列表（模拟数据）
     */
    private function getAdminUsers(OrganizationMemberHookParameter $parameter): array
    {
        // 这里应该是实际的数据查询逻辑
        // 为了演示，返回模拟数据
        $users = [
            ['id' => 1, 'name' => '系统管理员', 'user_type' => 'admin', 'role' => 'super_admin'],
            ['id' => 2, 'name' => '内容管理员', 'user_type' => 'admin', 'role' => 'content_admin'],
            ['id' => 3, 'name' => '用户管理员', 'user_type' => 'admin', 'role' => 'user_admin'],
            ['id' => 4, 'name' => '数据管理员', 'user_type' => 'admin', 'role' => 'data_admin'],
        ];

        // 应用搜索过滤
        if (!empty($parameter->search)) {
            $users = array_filter($users, function ($user) use ($parameter) {
                return str_contains(strtolower($user['name']), strtolower($parameter->search));
            });
        }

        // 应用分页
        return array_slice($users, $parameter->offset, $parameter->limit);
    }

    /**
     * 获取部门信息（模拟数据）
     */
    private function getDepartmentInfo(int $departmentId): array
    {
        $departments = [
            1 => ['id' => 1, 'name' => '技术部', 'description' => '负责技术开发和维护'],
            2 => ['id' => 2, 'name' => '运营部', 'description' => '负责产品运营和推广'],
            3 => ['id' => 3, 'name' => '市场部', 'description' => '负责市场推广和营销'],
        ];

        return $departments[$departmentId] ?? [];
    }

    /**
     * 获取部门的管理员负责人（模拟数据）
     */
    private function getDepartmentAdminLeaders(int $departmentId, array $leaderTypes): array
    {
        $leaders = [
            1 => [ // 技术部
                ['id' => 1, 'name' => '技术总监', 'type' => 'director', 'level' => 1, 'user_type' => 'admin'],
                ['id' => 2, 'name' => '技术经理', 'type' => 'manager', 'level' => 2, 'user_type' => 'admin'],
            ],
            2 => [ // 运营部
                ['id' => 3, 'name' => '运营总监', 'type' => 'director', 'level' => 1, 'user_type' => 'admin'],
                ['id' => 4, 'name' => '运营经理', 'type' => 'manager', 'level' => 2, 'user_type' => 'admin'],
            ],
        ];

        $departmentLeaders = $leaders[$departmentId] ?? [];

        // 按负责人类型过滤
        if (!empty($leaderTypes)) {
            $departmentLeaders = array_filter($departmentLeaders, function ($leader) use ($leaderTypes) {
                return in_array($leader['type'], $leaderTypes, true);
            });
        }

        return array_values($departmentLeaders);
    }

    /**
     * 获取部门的管理员审批人（模拟数据）
     */
    private function getDepartmentAdminApprovers(int $departmentId, array $approvalLevels, bool $activeOnly): array
    {
        $approvers = [
            1 => [ // 技术部
                ['id' => 2, 'name' => '技术经理', 'approval_level' => 1, 'workflows' => ['technical', 'expense'], 'user_type' => 'admin'],
                ['id' => 1, 'name' => '技术总监', 'approval_level' => 2, 'workflows' => ['technical', 'hiring'], 'user_type' => 'admin'],
            ],
            2 => [ // 运营部
                ['id' => 4, 'name' => '运营经理', 'approval_level' => 1, 'workflows' => ['marketing', 'expense'], 'user_type' => 'admin'],
                ['id' => 3, 'name' => '运营总监', 'approval_level' => 2, 'workflows' => ['marketing', 'budget'], 'user_type' => 'admin'],
            ],
        ];

        $departmentApprovers = $approvers[$departmentId] ?? [];

        // 按审批级别过滤
        if (!empty($approvalLevels)) {
            $departmentApprovers = array_filter($departmentApprovers, function ($approver) use ($approvalLevels) {
                return in_array($approver['approval_level'], $approvalLevels, true);
            });
        }

        return array_values($departmentApprovers);
    }

    /**
     * 获取Admin用户的上级关系（模拟数据）
     */
    private function getAdminUserSuperiors(int $userId, string $relationshipType, int $maxLevels, bool $activeOnly): array
    {
        $superiors = [
            1 => [ // 用户1的上级
                ['id' => 5, 'name' => 'CTO', 'relationship' => 'direct', 'level' => 1, 'user_type' => 'admin'],
                ['id' => 6, 'name' => 'CEO', 'relationship' => 'direct', 'level' => 2, 'user_type' => 'admin'],
            ],
            2 => [ // 用户2的上级
                ['id' => 1, 'name' => '技术总监', 'relationship' => 'functional', 'level' => 1, 'user_type' => 'admin'],
                ['id' => 5, 'name' => 'CTO', 'relationship' => 'direct', 'level' => 2, 'user_type' => 'admin'],
            ],
        ];

        $userSuperiors = $superiors[$userId] ?? [];

        // 按关系类型过滤
        if (!empty($relationshipType)) {
            $userSuperiors = array_filter($userSuperiors, function ($superior) use ($relationshipType) {
                return $superior['relationship'] === $relationshipType;
            });
        }

        // 限制层级
        $userSuperiors = array_filter($userSuperiors, function ($superior) use ($maxLevels) {
            return ($superior['level'] ?? 0) <= $maxLevels;
        });

        return array_values($userSuperiors);
    }

    /**
     * 获取Admin用户的层级结构（模拟数据）
     */
    private function getAdminUserHierarchy(int $userId, int $startLevel, int $maxLevel): array
    {
        $hierarchy = [
            1 => [
                ['level' => 1, 'user' => ['id' => 5, 'name' => 'CTO', 'user_type' => 'admin'], 'permissions' => ['approve_technical', 'approve_large_expense']],
                ['level' => 2, 'user' => ['id' => 6, 'name' => 'CEO', 'user_type' => 'admin'], 'permissions' => ['approve_all']],
            ],
            2 => [
                ['level' => 1, 'user' => ['id' => 1, 'name' => '技术总监', 'user_type' => 'admin'], 'permissions' => ['approve_technical', 'approve_medium_expense']],
                ['level' => 2, 'user' => ['id' => 5, 'name' => 'CTO', 'user_type' => 'admin'], 'permissions' => ['approve_technical', 'approve_large_expense']],
            ],
        ];

        $userHierarchy = $hierarchy[$userId] ?? [];

        // 过滤层级范围
        return array_filter($userHierarchy, function ($level) use ($startLevel, $maxLevel) {
            $levelNum = $level['level'] ?? 0;
            return $levelNum >= $startLevel && $levelNum <= $maxLevel;
        });
    }

    /**
     * 在层级中查找有权限的审批人（模拟数据）
     */
    private function findAdminApproversInHierarchy(array $hierarchy, array $requiredPermissions): array
    {
        $approvers = [];

        foreach ($hierarchy as $level) {
            $user = $level['user'] ?? [];
            $permissions = $level['permissions'] ?? [];

            // 检查是否具备所需权限
            if (empty($requiredPermissions) || count(array_intersect($requiredPermissions, $permissions)) > 0) {
                $approvers[] = [
                    'level' => $level['level'],
                    'user' => $user,
                    'permissions' => $permissions,
                    'is_suitable' => true,
                ];
            }
        }

        return $approvers;
    }
}
