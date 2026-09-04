<?php

namespace Modules\FeatureAi\DcatAdmin\Metrics;

use Dcat\Admin\Widgets\Metrics\Card;
use Modules\FeatureAi\Models\AiProvider;

/**
 * AI服务提供商数量统计卡片
 */
class AiProviderCount extends Card
{
    /**
     * 初始化卡片
     */
    protected function init()
    {
        parent::init();

        $this->title('启用供应商数量');
        $this->height(150);
    }

    /**
     * 处理统计数据
     *
     * @return void
     */
    public function handle(): void
    {
        // 统计启用的供应商数量 (is_active=1表示启用)
        $count = AiProvider::where('is_active', 1)->count();

        $this->content($count);
    }
}
