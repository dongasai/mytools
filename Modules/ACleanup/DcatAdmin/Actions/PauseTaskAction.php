<?php

namespace Modules\AClean\DcatAdmin\Actions;

use Modules\AClean\Services\ACleanService;
use Modules\DcatAdmin\DcatAdmin\RowAction;
use Illuminate\Http\Request;

/**
 * 暂停任务Action
 *
 * 用于暂停正在执行的清理任务
 */
class PauseTaskAction extends RowAction
{
    /**
     * 按钮标题
     */
    protected $title = '暂停任务';

    /**
     * 处理请求
     */
    public function handle(Request $request)
    {
        $taskId = $this->getKey();

        // 调用服务暂停任务
        $result = ACleanService::pauseTask($taskId);

        if (! $result['success']) {
            return $this->response()
                ->error('暂停失败：' . $result['message']);
        }

        return $this->response()
            ->success('任务暂停成功！')
            ->detail('任务已暂停，可以稍后恢复执行。')
            ->refresh();
    }

    /**
     * 确认对话框
     */
    public function confirm()
    {
        return [
            '确认暂停任务？',
            '任务将在当前批次完成后暂停，可以稍后恢复执行。',
        ];
    }

    /**
     * 权限检查
     */
    public function allowed()
    {
        $row = $this->row;

        return in_array($row->status, [2, 3]); // 备份中或执行中的任务可以暂停
    }
}
