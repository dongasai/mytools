<?php

namespace Modules\AClean\DcatAdmin\Actions;

use Modules\AClean\Models\CleanupConfig;
use Modules\AClean\Models\CleanupPlan;
use Modules\AClean\Models\CleanupPlanContent;
use Modules\DcatAdmin\DcatAdmin\RowAction;
use Dcat\Admin\Widgets\Form;

/**
 * 添加表到计划Action
 */
class AddTableToPlanAction extends RowAction
{
    /**
     * 标题
     */
    protected $title = '添加表';

    /**
     * 处理请求
     */
    public function handle()
    {
        $planId = $this->getKey();
        $data = request()->all();

        $plan = CleanupPlan::findOrFail($planId);

        // 检查表是否已存在
        $exists = CleanupPlanContent::where('plan_id', $planId)
            ->where('table_name', $data['table_name'])
            ->exists();

        if ($exists) {
            return $this->response()
                ->error('该表已存在于计划中');
        }

        // 获取表的默认配置
        $config = CleanupConfig::where('table_name', $data['table_name'])->first();

        // 创建计划内容
        CleanupPlanContent::create([
            'plan_id' => $planId,
            'table_name' => $data['table_name'],
            'cleanup_type' => $data['cleanup_type'] ?? ($config->default_cleanup_type ?? 2),
            'conditions' => $data['conditions'] ?? ($config->default_conditions ?? []),
            'priority' => $data['priority'] ?? ($config->priority ?? 100),
            'batch_size' => $data['batch_size'] ?? ($config->batch_size ?? 1000),
            'is_enabled' => $data['is_enabled'] ?? 1,
            'backup_enabled' => $data['backup_enabled'] ?? 1,
            'notes' => $data['notes'] ?? '',
            'conditions_description' => $this->generateConditionsDescription($data['cleanup_type'] ?? 2, $data['conditions'] ?? []),
        ]);

        return $this->response()
            ->success('添加成功')
            ->refresh();
    }

    /**
     * 表单配置
     */
    public function form()
    {
        $plan = CleanupPlan::findOrFail($this->getKey());

        // 获取可用的表（排除已添加的）
        $existingTables = CleanupPlanContent::where('plan_id', $this->getKey())
            ->pluck('table_name')
            ->toArray();

        $availableTables = CleanupConfig::whereNotIn('table_name', $existingTables)
            ->pluck('table_name', 'table_name')
            ->toArray();

        return Form::make()->action($this->getHandleRoute())->body([
            // 基础信息
            Form::display('plan_name', '所属计划')->value($plan->plan_name),

            Form::select('table_name', '选择表')
                ->options($availableTables)
                ->required()
                ->help('选择要添加到计划中的数据表'),

            // 清理配置
            Form::select('cleanup_type', '清理类型')
                ->options([
                    1 => '清空表',
                    2 => '删除所有',
                    3 => '按时间删除',
                    4 => '按用户删除',
                    5 => '按条件删除',
                ])
                ->default(2)
                ->required(),

            Form::keyValue('conditions', '清理条件')
                ->help('JSON格式的清理条件配置'),

            // 执行配置
            Form::number('priority', '优先级')
                ->default(100)
                ->min(1)
                ->max(999)
                ->help('数字越小优先级越高'),

            Form::number('batch_size', '批处理大小')
                ->default(1000)
                ->min(100)
                ->max(10000)
                ->help('每批处理的记录数量'),

            // 状态配置
            Form::switch('is_enabled', '启用状态')->default(1),
            Form::switch('backup_enabled', '备份启用')->default(1),

            // 备注
            Form::textarea('notes', '备注说明')
                ->help('对此清理配置的说明'),
        ]);
    }

    /**
     * 生成条件描述
     */
    private function generateConditionsDescription(int $cleanupType, array $conditions): string
    {
        switch ($cleanupType) {
            case 1:
                return '清空表（TRUNCATE）';
            case 2:
                return '删除所有记录（DELETE）';
            case 3:
                $days = $conditions['days'] ?? 30;

                return "删除{$days}天前的记录";
            case 4:
                $userIds = $conditions['user_ids'] ?? [];

                return '删除指定用户的记录：' . implode(',', $userIds);
            case 5:
                return '按自定义条件删除';
            default:
                return '未知清理类型';
        }
    }

    /**
     * 权限检查
     */
    public function allowed()
    {
        return true; // 根据实际需求设置权限
    }
}
