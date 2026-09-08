<?php

namespace Modules\AClean\DcatAdmin\Actions;

use Dcat\Admin\Actions\Response;
use Modules\DcatAdmin\DcatAdmin\RowAction;
use Modules\AClean\Models\CleanupBackupRunTableSplit;
use Modules\AClean\Enums\SPLIT_STATUS;
use Modules\AClean\QueueJobs\ProcessTableSplitJob;

/**
 * 重试分片操作
 *
 * 普通 AJAX 按钮，不覆盖 render2()
 */
class RetrySplitAction extends RowAction
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
        return '确定要重试此分片备份吗？';
    }

    /**
     * 显示条件
     */
    public function allowed(): bool
    {
        $split = CleanupBackupRunTableSplit::find($this->getKey());

        if (!$split) {
            return false;
        }

        return $split->split_status === SPLIT_STATUS::FAILED->value;
    }

    /**
     * AJAX 处理
     */
    public function handle(): Response
    {
        $id = $this->getKey();

        // ✅ 必须自己查询数据库
        $split = CleanupBackupRunTableSplit::find($id);

        if (!$split) {
            return $this->response()
                ->error('分片记录不存在或已被删除')
                ->refresh();
        }

        // 检查状态
        if ($split->split_status !== SPLIT_STATUS::FAILED->value) {
            return $this->response()
                ->error('只有失败的分片才能重试')
                ->refresh();
        }

        try {
            // 重置分片状态
            $split->split_status = SPLIT_STATUS::PENDING->value;
            $split->error_message = null;
            $split->started_at = null;
            $split->completed_at = null;
            $split->records_count = 0;
            $split->save();

            // 重新派发Job
            ProcessTableSplitJob::dispatch($split->id);

            return $this->response()
                ->success('分片备份已重新启动')
                ->refresh();
        } catch (\Exception $e) {
            return $this->response()
                ->error('重试失败: ' . $e->getMessage())
                ->refresh();
        }
    }
}