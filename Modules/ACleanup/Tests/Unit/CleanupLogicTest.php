<?php

namespace Modules\AClean\Tests\Unit;

use Modules\AClean\Logics\CleanupExecutorLogic;
use PHPUnit\Framework\TestCase;

/**
 * 清理执行逻辑单元测试
 */
class CleanupLogicTest extends TestCase
{
    /**
     * 测试计算批量大小
     */
    public function test_calculate_batch_size(): void
    {
        $result = CleanupExecutorLogic::calculateBatchSize(1000000, 'large_table');

        $this->assertIsInt($result);
        $this->assertGreaterThan(0, $result);
        $this->assertLessThanOrEqual(10000, $result); // 最大批量限制
    }

    /**
     * 测试验证清理条件
     */
    public function test_validate_cleanup_conditions(): void
    {
        $validConditions = [
            'type' => 'by_date',
            'date_field' => 'created_at',
            'date_value' => '2024-01-01',
        ];

        $this->assertTrue(CleanupExecutorLogic::validateConditions($validConditions));

        $invalidConditions = [
            'type' => 'by_date',
            // 缺少必要字段
        ];

        $this->assertFalse(CleanupExecutorLogic::validateConditions($invalidConditions));
    }

    /**
     * 测试估算删除记录数
     */
    public function test_estimate_deleted_records(): void
    {
        $conditions = [
            'type' => 'by_date',
            'date_field' => 'created_at',
            'date_value' => '2024-01-01',
        ];

        $estimate = CleanupExecutorLogic::estimateDeletedRecords('test_table', $conditions);

        $this->assertIsInt($estimate);
        $this->assertGreaterThanOrEqual(0, $estimate);
    }
}