<?php

namespace Modules\AClean\DcatAdmin\Metrics;

use Dcat\Admin\Widgets\Metrics\Card;
use Illuminate\Http\Request;
use Modules\AClean\Models\CleanupBackupRunBatch;

/**
 * 已执行时间 Metric
 */
class BatchElapsedTimeMetric extends Card
{
    protected $height = 150;
    protected $title = '已执行时间';
    protected int $batchId = 0;

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
            $this->withContent('-');
            return;
        }

        $batch = CleanupBackupRunBatch::find($this->batchId);

        if (!$batch || !$batch->started_at) {
            $this->withContent('-');
            return;
        }

        $end = $batch->completed_at ?? now();
        $seconds = $batch->started_at->diffInSeconds($end);

        $elapsed = $this->formatDuration($seconds);

        $this->withContent($elapsed);
    }

    /**
     * 格式化时长
     */
    protected function formatDuration(int $seconds): string
    {
        $hours = floor($seconds / 3600);
        $minutes = floor(($seconds % 3600) / 60);
        $secs = $seconds % 60;

        if ($hours > 0) {
            return sprintf('%d时%d分', $hours, $minutes);
        } elseif ($minutes > 0) {
            return sprintf('%d分%d秒', $minutes, $secs);
        } else {
            return sprintf('%d秒', $secs);
        }
    }

    /**
     * 卡片内容
     */
    public function withContent($content)
    {
        return $this->content(
            <<<HTML
<div class="d-flex flex-column flex-wrap text-center">
    <h1 class="font-lg-1 mt-2 mb-0">{$content}</h1>
</div>
HTML
        );
    }
}