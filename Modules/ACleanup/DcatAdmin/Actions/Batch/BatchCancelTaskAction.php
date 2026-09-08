<?php

namespace Modules\AClean\DcatAdmin\Actions\Batch;

use Modules\AClean\Services\ACleanService;
use Dcat\Admin\Grid\BatchAction;
use Illuminate\Http\Request;

/**
 * 批量取消任务Action
 *
 * 用于批量取消清理任务
 */
class BatchCancelTaskAction extends BatchAction
{
    /**
     * 按钮标题
     */
    protected $title = '批量取消';

    /**
     * 处理请求
     */
    public function handle(Request $request)
    {
        // 获取选中的ID
        $ids = $this->getKey();

        if (empty($ids)) {
            return $this->response()->error('请选择要取消的任务');
        }

        $reason = $request->input('reason', '批量取消操作');
        $successCount = 0;
        $failCount = 0;
        $errors = [];

        foreach ($ids as $taskId) {
            try {
                $result = ACleanService::cancelTask($taskId, $reason);
                if ($result['success']) {
                    $successCount++;
                } else {
                    $failCount++;
                    $errors[] = "任务 {$taskId}: ".$result['message'];
                }
            } catch (\Exception $e) {
                $failCount++;
                $errors[] = "任务 {$taskId}: ".$e->getMessage();
            }
        }

        $message = "成功取消 {$successCount} 个任务";
        if ($failCount > 0) {
            $message .= "，失败 {$failCount} 个";
        }

        $response = $this->response()->success($message)->refresh();

        if (! empty($errors)) {
            $errorDetail = implode('<br>', array_slice($errors, 0, 5));
            if (count($errors) > 5) {
                $errorDetail .= '<br>...还有 '.(count($errors) - 5).' 个错误';
            }
            $response->detail($errorDetail);
        }

        return $response;

    }

    /**
     * 确认对话框
     */
    public function confirm()
    {
        return [
            '确认批量取消任务？',
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
}
