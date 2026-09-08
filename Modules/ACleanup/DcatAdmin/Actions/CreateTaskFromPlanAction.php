<?php

namespace Modules\AClean\DcatAdmin\Actions;

use Modules\AClean\Services\ACleanService;
use Modules\DcatAdmin\DcatAdmin\RowAction;
use Illuminate\Http\Request;

/**
 * 从计划创建任务Action
 *
 * 用于从清理计划创建执行任务
 */
class CreateTaskFromPlanAction extends RowAction
{
    /**
     * 按钮标题
     */
    protected $title = '创建任务';

    /**
     * 处理请求
     */
    public function handle(Request $request)
    {
        $planId = $this->getKey();
        $taskName = $request->input('task_name');

        if (empty($taskName)) {
            return $this->response()
                ->error('请输入任务名称');
        }

        // 调用服务创建任务
        $task = ACleanService::createCleanupTask($planId, ['task_name' => $taskName]);

        return $this->response()
            ->success('任务创建成功！')
            ->detail("
                任务ID：{$task->id}<br>
                任务名称：{$task->task_name}<br>
                包含表数：{$task->total_tables}<br>
                状态：待执行
            ")
            ->redirect('/admin/backup-clean-admin/tasks/' . $task->id);
    }

    /**
     * 确认对话框
     */
    public function confirm()
    {
        return [
            '确认创建任务？',
            '将基于此计划创建一个新的清理任务。',
            [
                'task_name' => [
                    'type' => 'text',
                    'label' => '任务名称',
                    'placeholder' => '请输入任务名称',
                    'required' => true,
                ],
            ],
        ];
    }
}
