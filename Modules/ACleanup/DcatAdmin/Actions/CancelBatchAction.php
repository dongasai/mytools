<?php

namespace Modules\AClean\DcatAdmin\Actions;

use Dcat\Admin\Actions\Response;
use Modules\DcatAdmin\DcatAdmin\RowAction;
use Modules\AClean\Models\CleanupBackupRunBatch;
use Modules\AClean\Enums\BATCH_STATUS;

/**
 * 取消批次操作
 *
 * 普通 AJAX 按钮，不覆盖 render2()
 */
class CancelBatchAction extends RowAction
{
    /**
     * 样式类
     */
    protected $htmlClasses = ['action-danger'];

    /**
     * 按钮标题
     */
    public function title()
    {
        return '<i class="fa fa-times"></i> 取消';
    }

    /**
     * 确认弹窗
     */
    public function confirm()
    {
        return '确定要取消此备份批次吗？正在执行的表备份将继续完成。';
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

        return $batch->batch_status === BATCH_STATUS::IN_PROGRESS->value;
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
        if ($batch->batch_status !== BATCH_STATUS::IN_PROGRESS->value) {
            return $this->response()
                ->error('只有执行中的批次才能取消')
                ->refresh();
        }

        try {
            // 更新批次状态
            $batch->batch_status = BATCH_STATUS::CANCELLED->value;
            $batch->completed_at = now();
            $batch->save();

            return $this->response()
                ->success('批次已取消')
                ->refresh();
        } catch (\Exception $e) {
            return $this->response()
                ->error('取消失败: ' . $e->getMessage())
                ->refresh();
        }
    }
}