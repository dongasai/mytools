<?php

namespace Modules\AClean\DcatAdmin\Actions;

use Modules\AClean\Services\ACleanService;
use Modules\DcatAdmin\DcatAdmin\RowAction;
use Illuminate\Http\Request;

/**
 * 启动任务Action
 *
 * 用于启动待执行的清理任务
 */
class StartTaskAction extends RowAction
{
    /**
     * 按钮标题
     */
    protected $title = '启动任务';

    /**
     * 处理请求
     */
    public function handle(Request $request)
    {
        $taskId = $this->getKey();

        // 调用服务启动任务
        $result = ACleanService::startTask($taskId);

        if (! $result['success']) {
            return $this->response()
                ->error('启动失败：' . $result['message']);
        }

        return $this->response()
            ->success('任务启动成功！')
            ->detail('任务已开始执行，请在任务列表中查看进度。')
            ->refresh();
    }

    /**
     * 确认对话框
     */
    public function confirm()
    {
        return [
            '确认启动任务？',
            '⚠️ 任务启动后将开始执行数据清理操作，请确保已做好备份！',
        ];
    }

    /**
     * 权限检查
     */
    public function allowed()
    {
        $row = $this->row;

        return $row->status == 1; // 只有待执行状态的任务可以启动
    }
}
