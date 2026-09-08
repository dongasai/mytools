<?php

namespace Modules\AClean\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\AClean\Models\CleanupPlan;
use Tests\TestCase;

/**
 * 清理计划功能测试
 */
class CleanupPlanTest extends TestCase
{
    use RefreshDatabase;

    /**
     * 测试创建清理计划
     */
    public function test_can_create_cleanup_plan(): void
    {
        $planData = [
            'plan_name' => '测试清理计划',
            'plan_type' => 1,
            'global_conditions' => json_encode(['days' => 30]),
            'is_template' => false,
            'is_enabled' => true,
            'description' => '这是一个测试清理计划',
        ];

        $plan = CleanupPlan::create($planData);

        $this->assertInstanceOf(CleanupPlan::class, $plan);
        $this->assertEquals('测试清理计划', $plan->plan_name);
        $this->assertEquals(1, $plan->plan_type);
        $this->assertTrue($plan->is_enabled);
        $this->assertDatabaseHas('cleanup_plans', [
            'plan_name' => '测试清理计划',
        ]);
    }

    /**
     * 测试更新清理计划状态
     */
    public function test_can_update_cleanup_plan_status(): void
    {
        $plan = CleanupPlan::factory()->create([
            'is_enabled' => true,
        ]);

        $plan->update(['is_enabled' => false]);

        $this->assertFalse($plan->fresh()->is_enabled);
    }

    /**
     * 测试删除清理计划
     */
    public function test_can_delete_cleanup_plan(): void
    {
        $plan = CleanupPlan::factory()->create();

        $plan->delete();

        $this->assertSoftDeleted('cleanup_plans', [
            'id' => $plan->id,
        ]);
    }
}