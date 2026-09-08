<?php

namespace Modules\AClean\Dtos;

/**
 * 清理任务数据传输对象
 */
class CleanupTaskDto
{
    /**
     * 任务名称
     */
    public readonly string $taskName;

    /**
     * 计划ID
     */
    public readonly int $planId;

    /**
     * 备份ID
     */
    public readonly ?int $backupId;

    /**
     * 任务状态
     */
    public readonly int $status;

    /**
     * 执行进度
     */
    public readonly float $progress;

    /**
     * 当前步骤描述
     */
    public readonly ?string $currentStep;

    /**
     * 总表数
     */
    public readonly int $totalTables;

    /**
     * 已处理表数
     */
    public readonly int $processedTables;

    /**
     * 总记录数
     */
    public readonly int $totalRecords;

    /**
     * 已删除记录数
     */
    public readonly int $deletedRecords;

    /**
     * 备份大小（字节）
     */
    public readonly int $backupSize;

    /**
     * 执行时间（秒）
     */
    public readonly float $executionTime;

    /**
     * 备份时间（秒）
     */
    public readonly float $backupTime;

    /**
     * 创建者ID
     */
    public readonly ?int $createdBy;

    /**
     * @param string $taskName 任务名称
     * @param int $planId 计划ID
     * @param int|null $backupId 备份ID
     * @param int $status 任务状态
     * @param float $progress 执行进度
     * @param string|null $currentStep 当前步骤描述
     * @param int $totalTables 总表数
     * @param int $processedTables 已处理表数
     * @param int $totalRecords 总记录数
     * @param int $deletedRecords 已删除记录数
     * @param int $backupSize 备份大小（字节）
     * @param float $executionTime 执行时间（秒）
     * @param float $backupTime 备份时间（秒）
     * @param int|null $createdBy 创建者ID
     */
    public function __construct(
        string $taskName,
        int $planId,
        ?int $backupId = null,
        int $status = 1,
        float $progress = 0.00,
        ?string $currentStep = null,
        int $totalTables = 0,
        int $processedTables = 0,
        int $totalRecords = 0,
        int $deletedRecords = 0,
        int $backupSize = 0,
        float $executionTime = 0.000,
        float $backupTime = 0.000,
        ?int $createdBy = null
    ) {
        $this->taskName = $taskName;
        $this->planId = $planId;
        $this->backupId = $backupId;
        $this->status = $status;
        $this->progress = $progress;
        $this->currentStep = $currentStep;
        $this->totalTables = $totalTables;
        $this->processedTables = $processedTables;
        $this->totalRecords = $totalRecords;
        $this->deletedRecords = $deletedRecords;
        $this->backupSize = $backupSize;
        $this->executionTime = $executionTime;
        $this->backupTime = $backupTime;
        $this->createdBy = $createdBy;
    }
}