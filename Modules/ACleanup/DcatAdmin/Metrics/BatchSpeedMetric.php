<?php

namespace Modules\AClean\DcatAdmin\Metrics;

use Dcat\Admin\Widgets\Metrics\Card;
use Illuminate\Http\Request;
use Modules\AClean\Models\CleanupBackupRunBatch;

/**
 * 处理速度 Metric
 */
class BatchSpeedMetric extends Card
{
    protected $height = 150;
    protected $title = '处理速度';
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
            return;
        }

        $batch = CleanupBackupRunBatch::find($this->batchId);

        if (!$batch) {
            return;
        }

        $speed = $batch->avg_speed ?? 0;

        $this->withContent(number_format($speed) . ' <small>条/秒</small>');
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