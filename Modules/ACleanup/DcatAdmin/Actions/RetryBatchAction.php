<?php

namespace Modules\AClean\DcatAdmin\Actions;

use Dcat\Admin\Actions\Response;
use Modules\DcatAdmin\DcatAdmin\RowAction;
use Modules\AClean\Models\CleanupBackupRunBatch;
use Modules\AClean\Enums\BATCH_STATUS;
use Modules\AClean\QueueJobs\ProcessBackupBatchJob;

/**
 * 重试批次操作
 *
 * 普通 AJAX 按钮，不覆盖 render2()
 */
class RetryBatchAction extends RowAction
{
    /**
     * 样式类
     */
    protected $htmlClasses = ['action-warning'];

    /**
     * 按钮标题
     */
    public function title()
    {
        return '<i class="fa fa-redo"></i> 重试';
    }

    /**
     * 确认弹窗
     */
    public function confirm()
    {
        return '确定要重试此备份批次吗？将重新派发队列任务。';
    }

    /**
     * 显示条件
     */
    public function allowed(): bool
    {
        $batch = CleanupBackupRunBatch::find($this->getKey());

        if (!$batch) {
            return false;
        }

        return $batch->batch_status === BATCH_STATUS::FAILED->value;
    }

    /**
     * AJAX 处理
     */
    public function handle(): Response
    {
        $id = $this->getKey();

        // ✅ 必须自己查询数据库
        $batch = CleanupBackupRunBatch::find($id);

        if (!$batch) {
            return $this->response()
                ->error('批次不存在或已被删除')
                ->refresh();
        }

        // 检查状态
        if ($batch->batch_status !== BATCH_STATUS::FAILED->value) {
            return $this->response()
                ->error('只有失败的批次才能重试')
                ->refresh();
        }

        try {
            // 重置批次状态
            $batch->batch_status = BATCH_STATUS::PENDING->value;
            $batch->error_message = null;
            $batch->started_at = null;
            $batch->completed_at = null;
            $batch->save();

            // 重新派发Job
            ProcessBackupBatchJob::dispatch($batch->id);

            return $this->response()
                ->success('批次已重新启动')
                ->refresh();
        } catch (\Exception $e) {
            return $this->response()
                ->error('重试失败: ' . $e->getMessage())
                ->refresh();
        }
    }
}