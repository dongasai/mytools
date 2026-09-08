<?php

namespace Modules\AClean\DcatAdmin\Actions;

use Modules\AClean\Models\CleanupPlanContent;
use Modules\DcatAdmin\DcatAdmin\RowAction;
use Dcat\Admin\Widgets\Form;

/**
 * 编辑计划内容Action
 */
class EditPlanContentAction extends RowAction
{
    /**
     * 标题
     */
    protected $title = '编辑内容';

    /**
     * 处理请求
     */
    public function handle()
    {
        $id = $this->getKey();
        $data = $this->form;

        $content = CleanupPlanContent::findOrFail($id);

        // 更新计划内容
        $content->update([
            'cleanup_type' => $data['cleanup_type'],
            'conditions' => $data['conditions'] ?? [],
            'priority' => $data['priority'],
            'batch_size' => $data['batch_size'],
            'is_enabled' => $data['is_enabled'] ?? 0,
            'backup_enabled' => $data['backup_enabled'] ?? 0,
            'notes' => $data['notes'] ?? '',
        ]);

        return $this->response()
            ->success('编辑成功')
            ->refresh();
    }

    /**
     * 表单配置
     */
    public function form()
    {
        $content = CleanupPlanContent::findOrFail($this->getKey());

        return Form::make()->action($this->getHandleRoute())->body([
            // 基础信息（只读）
            Form::display('plan_name', '所属计划')->value($content->plan->plan_name ?? ''),
            Form::display('table_name', '表名')->value($content->table_name),

            // 清理配置
            Form::select('cleanup_type', '清理类型')
                ->options([
                    1 => '清空表',
                    2 => '删除所有',
                    3 => '按时间删除',
                    4 => '按用户删除',
                    5 => '按条件删除',
                ])
                ->value($content->cleanup_type)
                ->required(),

            Form::keyValue('conditions', '清理条件')
                ->value($content->conditions ?? [])
                ->help('JSON格式的清理条件配置'),

            // 执行配置
            Form::number('priority', '优先级')
                ->value($content->priority)
                ->min(1)
                ->max(999)
                ->help('数字越小优先级越高'),

            Form::number('batch_size', '批处理大小')
                ->value($content->batch_size)
                ->min(100)
                ->max(10000)
                ->help('每批处理的记录数量'),

            // 状态配置
            Form::switch('is_enabled', '启用状态')->value($content->is_enabled),
            Form::switch('backup_enabled', '备份启用')->value($content->backup_enabled),

            // 备注
            Form::textarea('notes', '备注说明')
                ->value($content->notes)
                ->help('对此清理配置的说明'),
        ]);
    }

    /**
     * 权限检查
     */
    public function allowed()
    {
        return true; // 根据实际需求设置权限
    }
}
