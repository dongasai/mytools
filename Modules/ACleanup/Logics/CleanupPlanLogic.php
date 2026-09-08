<?php

namespace Modules\AClean\Logics;

use Modules\AClean\Enums\CLEANUP_TYPE;
use Modules\AClean\Enums\PLAN_TYPE;
use Modules\AClean\Models\CleanupConfig;
use Modules\AClean\Models\CleanupPlan;
use Modules\AClean\Models\CleanupPlanContent;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * 清理计划管理逻辑类
 *
 * 负责清理计划的创建、管理和内容生成
 */
class CleanupPlanLogic
{
    /**
     * 创建清理计划
     *
     * @param  array  $planData  计划数据
     * @return CleanupPlan 创建的计划 Model
     */
    public static function createPlan(array $planData): CleanupPlan
    {
        $validatedData = static::validatePlanData($planData);

        return DB::transaction(function () use ($validatedData, $planData) {
            $plan = CleanupPlan::create($validatedData);

            // 如果需要自动生成内容，则生成计划内容
            if ($planData['auto_generate_contents'] ?? true) {
                static::generateContents($plan->id, true);
            }

            return $plan;
        });
    }

    /**
     * 为计划生成内容配置
     *
     * @param  int  $planId  计划ID
     * @param  bool  $autoGenerate  是否自动生成
     * @return array 生成统计 ['generated_count' => int, 'skipped_count' => int, 'total_tables' => int, 'errors' => array]
     */
    public static function generateContents(int $planId, bool $autoGenerate = true): array
    {
        $plan = CleanupPlan::findOrFail($planId);

        // 根据计划类型获取目标表
        $targetTables = static::getTargetTables($plan);

        if (empty($targetTables)) {
            return [
                'generated_count' => 0,
                'skipped_count' => 0,
                'total_tables' => 0,
                'errors' => ['未找到符合条件的目标表'],
            ];
        }

        $generatedCount = 0;
        $skippedCount = 0;
        $errors = [];

        foreach ($targetTables as $tableName) {
            try {
                // 检查是否已存在内容配置
                $existingContent = CleanupPlanContent::where('plan_id', $planId)
                    ->where('table_name', $tableName)
                    ->first();

                if ($existingContent && ! $autoGenerate) {
                    $skippedCount++;
                    continue;
                }

                // 获取表的配置信息
                $tableConfig = CleanupConfig::where('table_name', $tableName)->first();

                // 生成内容配置
                $contentData = static::generateTableContent($plan, $tableName, $tableConfig);

                if ($existingContent) {
                    $existingContent->update($contentData);
                } else {
                    $contentData['plan_id'] = $planId;
                    $contentData['table_name'] = $tableName;
                    CleanupPlanContent::create($contentData);
                }

                $generatedCount++;
            } catch (\Exception $e) {
                $errors[] = "表 {$tableName}: ".$e->getMessage();
                Log::error('生成表内容配置失败', [
                    'plan_id' => $planId,
                    'table_name' => $tableName,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return [
            'generated_count' => $generatedCount,
            'skipped_count' => $skippedCount,
            'total_tables' => count($targetTables),
            'errors' => $errors,
        ];
    }

    /**
     * 验证计划数据
     *
     * @param  array  $planData  计划数据
     * @return array 验证后的数据
     *
     * @throws \Exception
     */
    private static function validatePlanData(array $planData): array
    {
        // 必填字段验证
        $required = ['plan_name', 'plan_type'];
        foreach ($required as $field) {
            if (empty($planData[$field])) {
                throw new \Exception("字段 {$field} 不能为空");
            }
        }

        // 验证计划类型
        $planType = PLAN_TYPE::tryFrom($planData['plan_type']);
        if (! $planType) {
            throw new \Exception('无效的计划类型');
        }

        // 根据计划类型验证目标选择
        if ($planType !== PLAN_TYPE::CUSTOM && empty($planData['target_selection'])) {
            throw new \Exception('目标选择不能为空');
        }

        return [
            'plan_name' => $planData['plan_name'],
            'plan_type' => $planData['plan_type'],
            'target_selection' => $planData['target_selection'] ?? [],
            'global_conditions' => $planData['global_conditions'] ?? [],
            'backup_config' => $planData['backup_config'] ?? [],
            'is_template' => $planData['is_template'] ?? false,
            'is_enabled' => $planData['is_enabled'] ?? true,
            'description' => $planData['description'] ?? '',
            'created_by' => $planData['created_by'] ?? 0,
        ];
    }

    /**
     * 根据计划获取目标表列表
     *
     * @param  CleanupPlan  $plan  清理计划
     * @return array 目标表列表
     */
    private static function getTargetTables(CleanupPlan $plan): array
    {
        $tables = [];
        $selectedModels = $plan->selected_tables ?? [];

        foreach ($selectedModels as $modelClass) {
            if (class_exists($modelClass) && is_subclass_of($modelClass, \Illuminate\Database\Eloquent\Model::class)) {
                try {
                    // 通过模型实例获取表名
                    $model = new $modelClass;
                    $tables[] = $model->getTable();
                } catch (\Exception $e) {
                    // 如果模型实例化失败，记录错误但继续处理其他模型
                    Log::warning("Failed to get table name for model: {$modelClass}", ['error' => $e->getMessage()]);
                }
            }
        }

        return array_unique($tables);
    }

    /**
     * 为表生成内容配置
     *
     * @param  CleanupPlan  $plan  清理计划
     * @param  string  $tableName  表名
     * @param  CleanupConfig|null  $tableConfig  表配置
     * @return array 内容配置数据
     */
    private static function generateTableContent(CleanupPlan $plan, string $tableName, ?CleanupConfig $tableConfig): array
    {
        // 基础配置
        $contentData = [
            'cleanup_type' => $tableConfig?->default_cleanup_type ?? CLEANUP_TYPE::DELETE_ALL->value,
            'conditions' => $tableConfig?->default_conditions ?? [],
            'priority' => $tableConfig?->priority ?? 100,
            'batch_size' => $tableConfig?->batch_size ?? 1000,
            'backup_enabled' => true,
            'is_enabled' => true,
            'notes' => $tableConfig?->description ?? "自动生成的 {$tableName} 表清理配置",
        ];

        // 合并计划的全局条件
        if (! empty($plan->global_conditions)) {
            $contentData['conditions'] = array_merge(
                $contentData['conditions'],
                $plan->global_conditions
            );
        }

        // 合并计划的备份配置
        if (! empty($plan->backup_config)) {
            $contentData['backup_config'] = $plan->backup_config;
        }

        return $contentData;
    }

    /**
     * 获取计划详情
     *
     * @param  int  $planId  计划ID
     * @return array 计划详情数据（plan + contents + statistics）
     */
    public static function getPlanDetails(int $planId): array
    {
        $plan = CleanupPlan::with(['contents.config'])->findOrFail($planId);

        $contents = $plan->contents->map(function ($content) {
            return [
                'id' => $content->id,
                'table_name' => $content->table_name,
                'cleanup_type' => $content->cleanup_type,
                'cleanup_type_name' => CLEANUP_TYPE::from($content->cleanup_type)->getDescription(),
                'conditions' => $content->conditions,
                'priority' => $content->priority,
                'batch_size' => $content->batch_size,
                'backup_enabled' => $content->backup_enabled,
                'is_enabled' => $content->is_enabled,
                'notes' => $content->notes,
                'module_name' => $content->config?->module_name,
                'data_category' => $content->config?->data_category,
            ];
        });

        return [
            'plan' => [
                'id' => $plan->id,
                'plan_name' => $plan->plan_name,
                'selected_tables' => $plan->selected_tables,
                'global_conditions' => $plan->global_conditions,
                'backup_config' => $plan->backup_config,
                'is_template' => $plan->is_template,
                'is_enabled' => $plan->is_enabled,
                'description' => $plan->description,
                'created_at' => $plan->created_at,
                'updated_at' => $plan->updated_at,
            ],
            'contents' => $contents,
            'statistics' => [
                'total_tables' => $contents->count(),
                'enabled_tables' => $contents->where('is_enabled', true)->count(),
                'backup_enabled_tables' => $contents->where('backup_enabled', true)->count(),
            ],
        ];
    }

    /**
     * 更新计划
     *
     * @param  int  $planId  计划ID
     * @param  array  $planData  计划数据
     * @return CleanupPlan 更新后的计划 Model
     */
    public static function updatePlan(int $planId, array $planData): CleanupPlan
    {
        $plan = CleanupPlan::findOrFail($planId);

        // 验证数据
        $validatedData = static::validatePlanData($planData);

        // 更新计划
        $plan->update($validatedData);

        return $plan->fresh();
    }

    /**
     * 删除计划
     *
     * @param  int  $planId  计划ID
     * @return bool 删除成功与否
     *
     * @throws \Exception 如果存在关联任务
     */
    public static function deletePlan(int $planId): bool
    {
        return DB::transaction(function () use ($planId) {
            $plan = CleanupPlan::findOrFail($planId);

            // 检查是否有关联的任务
            if ($plan->tasks()->exists()) {
                throw new \Exception('该计划存在关联的任务，无法删除');
            }

            // 删除计划内容
            $plan->contents()->delete();

            // 删除计划
            return (bool) $plan->delete();
        });
    }
}
