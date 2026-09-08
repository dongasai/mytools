<?php

namespace Modules\AClean\DcatAdmin\Metrics;

use Dcat\Admin\Widgets\Metrics\RadialBar;
use Illuminate\Http\Request;
use Modules\AClean\Models\CleanupBackupRunBatch;
use Modules\AClean\Enums\BATCH_STATUS;

/**
 * 批次进度 Metric
 *
 * 显示批次执行进度（环形图 + 统计信息）
 */
class BatchProgressMetric extends RadialBar
{
    /**
     * 批次ID
     */
    protected int $batchId = 0;

    /**
     * 初始化卡片
     */
    protected function init()
    {
        parent::init();

        $this->title('备份进度');
        $this->height(400);
        $this->chartHeight(300);
        $this->chartLabels('进度');
    }

    /**
     * 设置批次ID
     */
    public function batchId(int $id): self
    {
        $this->batchId = $id;
        return $this;
    }

    /**
     * 处理请求
     */
    public function handle(Request $request)
    {
        if ($this->batchId === 0) {
            return;
        }

        $batch = CleanupBackupRunBatch::find($this->batchId);

        if (!$batch) {
            return;
        }

        // 计算进度
        $processed = $batch->processed_tables;
        $total = $batch->total_tables;
        $failed = $batch->failed_tables;
        $progress = $total > 0 ? round(($processed / $total) * 100, 2) : 0;

        // 卡片内容：已处理/总表数
        $this->withContent("{$processed}/{$total}", $batch->batch_status);

        // 卡片底部：成功、失败、当前表
        $success = $processed - $failed;
        $this->withFooter($success, $failed, $batch->current_table ?? '-');

        // 图表数据：进度百分比
        $this->withChart($progress);
    }

    /**
     * 设置图表数据
     */
    public function withChart(float $progress)
    {
        return $this->chart([
            'series' => [$progress],
        ]);
    }

    /**
     * 卡片内容
     */
    public function withContent(string $tables, int $status)
    {
        $statusEnum = BATCH_STATUS::tryFrom($status);
        $statusLabel = $statusEnum ? $statusEnum->icon() . ' ' . $statusEnum->label() : '-';
        $statusClass = match($status) {
            BATCH_STATUS::COMPLETED->value => 'text-success',
            BATCH_STATUS::FAILED->value => 'text-danger',
            BATCH_STATUS::IN_PROGRESS->value => 'text-primary',
            default => 'text-muted',
        };

        return $this->content(
            <<<HTML
<div class="d-flex flex-column flex-wrap text-center">
    <h1 class="font-lg-2 mt-3 mb-0">{$tables}</h1>
    <small>已处理/总表数</small>
    <div class="mt-2">
        <span class="{$statusClass}">{$statusLabel}</span>
    </div>
</div>
HTML
        );
    }

    /**
     * 卡片底部内容
     */
    public function withFooter(int $success, int $failed, string $current)
    {
        return $this->footer(
            <<<HTML
<div class="d-flex justify-content-between p-1" style="padding-top: 0!important;">
    <div class="text-center">
        <p class="mb-0">成功</p>
        <span class="font-lg-1 text-success">{$success}</span>
    </div>
    <div class="text-center">
        <p class="mb-0">失败</p>
        <span class="font-lg-1 text-danger">{$failed}</span>
    </div>
    <div class="text-center">
        <p class="mb-0">当前表</p>
        <span class="font-lg-1 text-muted">{$current}</span>
    </div>
</div>
HTML
        );
    }
}