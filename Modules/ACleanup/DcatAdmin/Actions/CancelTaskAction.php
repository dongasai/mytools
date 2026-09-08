<?php

namespace Modules\AClean\DcatAdmin\Actions;

use Modules\AClean\Services\ACleanService;
use Modules\DcatAdmin\DcatAdmin\RowAction;
use Illuminate\Http\Request;

/**
 * 取消任务Action
 *
 * 用于取消正在执行或已暂停的清理任务
 */
class CancelTaskAction extends RowAction
{
    /**
     * 按钮标题
     */
    protected $title = '取消任务';

    /**
     * 处理请求
     */
    public function handle(Request $request)
    {
        $taskId = $this->getKey();
        $reason = $request->input('reason', '用户手动取消');

        // 调用服务取消任务
        $result = ACleanService::cancelTask($taskId, $reason);

        if (! $result['success']) {
            return $this->response()
                ->error('取消失败：' . $result['message']);
        }

        return $this->response()
            ->success('任务取消成功！')
            ->detail('任务已取消，已完成的操作不会回滚。')
            ->refresh();
    }

    /**
     * 确认对话框
     */
    public function confirm()
    {
        return [
            '确认取消任务？',
            '⚠️ 任务取消后无法恢复，已完成的清理操作不会回滚！',
            [
                'reason' => [
                    'type' => 'textarea',
                    'label' => '取消原因',
                    'placeholder' => '请输入取消原因（可选）',
                    'rows' => 3,
                ],
            ],
        ];
    }

    /**
     * 权限检查
     */
    public function allowed()
    {
        $row = $this->row;

        return in_array($row->status, [1, 2, 3, 7]); // 待执行、备份中、执行中、已暂停的任务可以取消
    }
}
