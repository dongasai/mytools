<?php

declare(strict_types=1);

namespace Modules\Application\Tests\Unit;

use Modules\ABase\Hooks\Management\HookManager;
use Modules\Application\Hooks\Definitions\OrganizationTypeHookDefinition;
use Modules\Application\Hooks\Definitions\OrganizationMemberHookDefinition;
use Modules\Application\Hooks\Definitions\DepartmentLeaderHookDefinition;
use Modules\Application\Hooks\Parameters\OrganizationTypeHookParameter;
use Modules\Application\Hooks\Parameters\OrganizationMemberHookParameter;
use Modules\Application\Hooks\Parameters\DepartmentLeaderHookParameter;
use Modules\Application\Hooks\Subscribers\AdminOrganizationHookSubscriber;
use Modules\Workflow\Enums\USER_TYPE;
use PHPUnit\Framework\TestCase;

/**
 * 组织架构Hook系统测试
 *
 * 测试组织架构Hook系统的基本功能和数据流转
 */
class OrganizationHookSystemTest extends TestCase
{
    /**
     * 设置测试环境
     */
    protected function setUp(): void
    {
        parent::setUp();

        // 注册Hook定义
        HookManager::registerHook(OrganizationTypeHookDefinition::class);
        HookManager::registerHook(OrganizationMemberHookDefinition::class);
        HookManager::registerHook(DepartmentLeaderHookDefinition::class);

        // 注册订阅者
        $subscriber = new AdminOrganizationHookSubscriber();
        HookManager::registerSubscriber($subscriber);
    }

    /**
     * 清理测试环境
     */
    protected function tearDown(): void
    {
        // 清理Hook状态（如果需要）
        HookManager::clear();

        parent::tearDown();
    }

    /**
     * 测试组织类型Hook
     */
    public function testOrganizationTypeHook(): void
    {
        // 创建参数
        $parameter = new OrganizationTypeHookParameter(
            user_type: USER_TYPE::APPROVER
        );

        // 应用Hook
        $result = HookManager::apply(OrganizationTypeHookDefinition::class, $parameter);

        // 验证结果
        $this->assertTrue($result->isSuccess());
        $this->assertNotEmpty($result->getOrganizationTypes());
        $this->assertGreaterThanOrEqual(2, $result->getCount());

        // 验证是否包含Admin相关的组织类型
        $this->assertTrue($result->hasType('admin_role'));
        $this->assertTrue($result->hasType('admin_user'));

        // 验证组织类型数据结构
        $adminRoleType = $result->getOrganizationType('admin_role');
        $this->assertNotNull($adminRoleType);
        $this->assertEquals('admin_role', $adminRoleType->type_id);
        $this->assertEquals('后台角色', $adminRoleType->type_name);
    }

    /**
     * 测试组织成员Hook
     */
    public function testOrganizationMemberHook(): void
    {
        // 创建参数
        $parameter = new OrganizationMemberHookParameter(
            user_type: USER_TYPE::APPROVER,
            organization_type: 'admin_role',
            user_module_type: 'admin',
            search: '',
            limit: 10,
            offset: 0
        );

        // 应用Hook
        $result = HookManager::apply(OrganizationMemberHookDefinition::class, $parameter);

        // 验证结果
        $this->assertTrue($result->isSuccess());
        $this->assertNotEmpty($result->getMembers());
        $this->assertGreaterThanOrEqual(1, $result->getCount());

        // 验证成员数据结构
        $members = $result->getMembers();
        foreach ($members as $member) {
            $this->assertEquals('admin_role', $member->organization_type);
            $this->assertEquals('admin', $member->user_type);
            $this->assertNotNull($member->getDisplayName());
        }
    }

    /**
     * 测试部门负责人Hook
     */
    public function testDepartmentLeaderHook(): void
    {
        // 创建参数
        $parameter = new DepartmentLeaderHookParameter(
            user_type: USER_TYPE::APPROVER,
            department_id: 1, // 技术部
            leader_types: ['manager', 'director'],
            include_secondary: false
        );

        // 应用Hook
        $result = HookManager::apply(DepartmentLeaderHookDefinition::class, $parameter);

        // 验证结果
        $this->assertTrue($result->isSuccess());
        $this->assertNotEmpty($result->getDepartment());
        $this->assertEquals('技术部', $result->getDepartmentName());

        // 验证负责人信息
        if ($result->hasLeaders()) {
            $primaryLeader = $result->getPrimaryLeader();
            $this->assertNotNull($primaryLeader);
            $this->assertArrayHasKey('id', $primaryLeader);
            $this->assertArrayHasKey('name', $primaryLeader);
            $this->assertArrayHasKey('type', $primaryLeader);
        }
    }

    /**
     * 测试搜索功能
     */
    public function testSearchFunctionality(): void
    {
        // 测试无搜索条件
        $parameter1 = new OrganizationMemberHookParameter(
            user_type: USER_TYPE::APPROVER,
            organization_type: 'admin_user',
            limit: 10
        );

        $result1 = HookManager::apply(OrganizationMemberHookDefinition::class, $parameter1);
        $countWithoutSearch = $result1->getCount();

        // 测试有搜索条件
        $parameter2 = new OrganizationMemberHookParameter(
            user_type: USER_TYPE::APPROVER,
            organization_type: 'admin_user',
            search: '管理',
            limit: 10
        );

        $result2 = HookManager::apply(OrganizationMemberHookDefinition::class, $parameter2);
        $countWithSearch = $result2->getCount();

        // 验证搜索结果
        $this->assertTrue($result2->isSuccess());
        $this->assertLessThanOrEqual($countWithoutSearch, $countWithSearch);

        // 验证搜索结果都包含关键词
        if ($result2->isNotEmpty()) {
            $members = $result2->getMembers();
            foreach ($members as $member) {
                $name = $member->name ?? '';
                $this->assertStringContainsStringIgnoringCase('管理', $name);
            }
        }
    }

    /**
     * 测试分页功能
     */
    public function testPaginationFunctionality(): void
    {
        // 获取第一页
        $parameter1 = new OrganizationMemberHookParameter(
            user_type: USER_TYPE::APPROVER,
            organization_type: 'admin_user',
            limit: 2,
            offset: 0
        );

        $result1 = HookManager::apply(OrganizationMemberHookDefinition::class, $parameter1);
        $firstPageCount = $result1->getCount();

        // 获取第二页
        $parameter2 = new OrganizationMemberHookParameter(
            user_type: USER_TYPE::APPROVER,
            organization_type: 'admin_user',
            limit: 2,
            offset: 2
        );

        $result2 = HookManager::apply(OrganizationMemberHookDefinition::class, $parameter2);
        $secondPageCount = $result2->getCount();

        // 验证分页结果
        $this->assertTrue($result1->isSuccess());
        $this->assertTrue($result2->isSuccess());

        // 验证每页数量限制
        $this->assertLessThanOrEqual(2, $firstPageCount);
        $this->assertLessThanOrEqual(2, $secondPageCount);
    }

    /**
     * 测试数据结构完整性
     */
    public function testDataStructureIntegrity(): void
    {
        // 测试组织类型数据结构
        $typeParameter = new OrganizationTypeHookParameter(USER_TYPE::APPROVER);
        $typeResult = HookManager::apply(OrganizationTypeHookDefinition::class, $typeParameter);

        $this->assertTrue($typeResult->isSuccess());
        $this->assertIsArray($typeResult->toArray());
        $this->assertArrayHasKey('success', $typeResult->toArray());
        $this->assertArrayHasKey('organization_types', $typeResult->toArray());
        $this->assertArrayHasKey('count', $typeResult->toArray());

        // 测试组织成员数据结构
        $memberParameter = new OrganizationMemberHookParameter(
            user_type: USER_TYPE::APPROVER,
            organization_type: 'admin_user'
        );
        $memberResult = HookManager::apply(OrganizationMemberHookDefinition::class, $memberParameter);

        $this->assertTrue($memberResult->isSuccess());
        $this->assertIsArray($memberResult->toArray());
        $this->assertArrayHasKey('success', $memberResult->toArray());
        $this->assertArrayHasKey('members', $memberResult->toArray());
        $this->assertArrayHasKey('count', $memberResult->toArray());
        $this->assertArrayHasKey('pagination', $memberResult->toArray());
    }

    /**
     * 测试错误处理
     */
    public function testErrorHandling(): void
    {
        // 测试不存在的组织类型
        $parameter = new OrganizationMemberHookParameter(
            user_type: USER_TYPE::APPROVER,
            organization_type: 'non_existent_type',
            limit: 10
        );

        $result = HookManager::apply(OrganizationMemberHookDefinition::class, $parameter);

        // 应该返回成功但结果为空（因为不匹配的数据被过滤）
        $this->assertTrue($result->isSuccess());
        $this->assertEquals(0, $result->getCount());
        $this->assertTrue($result->isEmpty());
    }

    /**
     * 测试不同用户类型
     */
    public function testDifferentUserTypes(): void
    {
        foreach ([USER_TYPE::APPROVER, USER_TYPE::CC, USER_TYPE::NOTIFIER] as $userType) {
            $parameter = new OrganizationTypeHookParameter($userType);
            $result = HookManager::apply(OrganizationTypeHookDefinition::class, $parameter);

            $this->assertTrue($result->isSuccess(), "Failed for user type: {$userType->value}");
            $this->assertNotEmpty($result->getOrganizationTypes(), "No types found for user type: {$userType->value}");
        }
    }

    /**
     * 测试链式操作
     */
    public function testChainedOperations(): void
    {
        // 首先获取组织类型
        $typeParameter = new OrganizationTypeHookParameter(USER_TYPE::APPROVER);
        $typeResult = HookManager::apply(OrganizationTypeHookDefinition::class, $typeParameter);

        $this->assertTrue($typeResult->isSuccess());

        // 然后使用第一个组织类型获取成员
        $firstType = $typeResult->getFirst();
        $this->assertNotNull($firstType);

        $memberParameter = new OrganizationMemberHookParameter(
            user_type: USER_TYPE::APPROVER,
            organization_type: $firstType->type_id
        );
        $memberResult = HookManager::apply(OrganizationMemberHookDefinition::class, $memberParameter);

        $this->assertTrue($memberResult->isSuccess());
    }
}
