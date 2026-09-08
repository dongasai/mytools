<?php

namespace Modules\AClean\DcatAdmin\Actions;

use Modules\AClean\Services\ACleanService;
use Modules\DcatAdmin\DcatAdmin\RowAction;
use Illuminate\Http\Request;

/**
 * 恢复任务Action
 *
 * 用于恢复已暂停的清理任务
 */
class ResumeTaskAction extends RowAction
{
    /**
     * 按钮标题
     */
    protected $title = '恢复任务';

    /**
     * 处理请求
     */
    public function handle(Request $request)
    {
        $taskId = $this->getKey();

        // 调用服务恢复任务
        $result = ACleanService::resumeTask($taskId);

        if (! $result['success']) {
            return $this->response()
                ->error('恢复失败：' . $result['message']);
        }

        return $this->response()
            ->success('任务恢复成功！')
            ->detail('任务已恢复执行，将从暂停点继续。')
            ->refresh();
    }

    /**
     * 确认对话框
     */
    public function confirm()
    {
        return [
            '确认恢复任务？',
            '任务将从暂停点继续执行。',
        ];
    }

    /**
     * 权限检查
     */
    public function allowed()
    {
        $row = $this->row;

        return $row->status == 7; // 只有已暂停的任务可以恢复
    }
}
