<?php

namespace Modules\AClean\DcatAdmin\Actions;

use Dcat\Admin\Actions\Response;
use Modules\DcatAdmin\DcatAdmin\RowAction;
use Modules\AClean\Models\CleanupBackupRunTable;
use Modules\AClean\Enums\TABLE_STATUS;
use Modules\AClean\QueueJobs\ProcessTableBackupJob;

/**
 * 重试表备份操作
 *
 * 普通 AJAX 按钮，不覆盖 render2()
 */
class RetryTableAction extends RowAction
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
        return '确定要重试此表备份吗？';
    }

    /**
     * 显示条件
     */
    public function allowed(): bool
    {
        $table = CleanupBackupRunTable::find($this->getKey());

        if (!$table) {
            return false;
        }

        return $table->table_status === TABLE_STATUS::FAILED->value;
    }

    /**
     * AJAX 处理
     */
    public function handle(): Response
    {
        $id = $this->getKey();

        // ✅ 必须自己查询数据库
        $table = CleanupBackupRunTable::find($id);

        if (!$table) {
            return $this->response()
                ->error('表备份记录不存在或已被删除')
                ->refresh();
        }

        // 检查状态
        if ($table->table_status !== TABLE_STATUS::FAILED->value) {
            return $this->response()
                ->error('只有失败的表备份才能重试')
                ->refresh();
        }

        try {
            // 重置表状态
            $table->table_status = TABLE_STATUS::PENDING->value;
            $table->error_message = null;
            $table->started_at = null;
            $table->completed_at = null;
            $table->processed_records = 0;
            $table->progress_percent = 0;
            $table->completed_splits = 0;
            $table->save();

            // 重新派发Job
            ProcessTableBackupJob::dispatch($table->id);

            return $this->response()
                ->success('表备份已重新启动')
                ->refresh();
        } catch (\Exception $e) {
            return $this->response()
                ->error('重试失败: ' . $e->getMessage())
                ->refresh();
        }
    }
}