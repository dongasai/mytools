<?php

namespace Modules\AClean\DcatAdmin\Metrics;

use Dcat\Admin\Widgets\Metrics\Card;
use Illuminate\Http\Request;
use Modules\AClean\Models\CleanupBackupRunBatch;

/**
 * 批次统计 Metric 基类
 */
abstract class BatchStatisticCard extends Card
{
    protected $height = 150;
    protected int $batchId;

    /**
     * 设置批次ID
     */
    public function batchId(int $id): self
    {
        $this->batchId = $id;
        return $this;
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