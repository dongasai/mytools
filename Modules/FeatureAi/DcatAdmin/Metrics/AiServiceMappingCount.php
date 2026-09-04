<?php

namespace Modules\FeatureAi\DcatAdmin\Metrics;

use Dcat\Admin\Widgets\Metrics\Card;
use Modules\FeatureAi\Models\AiServiceMapping;

/**
 * AI服务映射数量统计卡片
 */
class AiServiceMappingCount extends Card
{
    /**
     * 初始化卡片
     */
    protected function init()
    {
        parent::init();

        $this->title('启用服务映射数量');
        $this->height(150);
    }

    /**
     * 处理统计数据
     *
     * @return void
     */
    public function handle(): void
    {
        // 统计启用的服务映射数量 (is_active=1表示启用)
        $count = AiServiceMapping::where('is_active', 1)->count();

        $this->content($count);
    }
}
