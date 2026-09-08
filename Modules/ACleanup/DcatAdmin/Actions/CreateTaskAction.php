<?php

namespace Modules\AClean\DcatAdmin\Actions;

use Modules\AClean\Models\CleanupPlan;
use Modules\AClean\Services\ACleanService;
use Dcat\Admin\Grid\Tools\AbstractTool;
use Illuminate\Http\Request;

/**
 * 创建任务Action
 *
 * 用于在任务管理页面创建新任务
 */
class CreateTaskAction extends AbstractTool
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
        $planId = $request->input('plan_id');
        $taskName = $request->input('task_name');

        if (empty($planId)) {
            return $this->response()
                ->error('请选择清理计划');
        }

        if (empty($taskName)) {
            return $this->response()
                ->error('请输入任务名称');
        }

        // 调用服务创建任务
        $result = ACleanService::createTaskFromPlan($planId, $taskName);

        if (! $result['success']) {
            return $this->response()
                ->error('创建失败：'.$result['message']);
        }

        $task = $result['data'];

        return $this->response()
            ->success('任务创建成功！')
            ->detail("
                任务ID：{$task['id']}<br>
                任务名称：{$task['task_name']}<br>
                关联计划：{$task['plan_name']}<br>
                包含表数：{$task['total_tables']}<br>
                状态：待执行
            ")
            ->refresh();

    }

    /**
     * 确认对话框
     */
    public function confirm()
    {
        $plans = CleanupPlan::where('is_enabled', 1)->pluck('plan_name', 'id')->toArray();

        return [
            '创建清理任务',
            '请选择清理计划并输入任务名称。',
            [
                'plan_id' => [
                    'type' => 'select',
                    'label' => '清理计划',
                    'options' => $plans,
                    'required' => true,
                ],
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
