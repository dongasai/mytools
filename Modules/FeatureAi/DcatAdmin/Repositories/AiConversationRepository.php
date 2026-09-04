<?php

namespace Modules\FeatureAi\DcatAdmin\Repositories;

use Dcat\Admin\Repositories\EloquentRepository;
use Modules\FeatureAi\Models\AiConversation;

/**
 * AI对话日志仓库
 */
class AiConversationRepository extends EloquentRepository
{
    /**
     * 模型类名
     *
     * @var string
     */
    protected $eloquentClass = AiConversation::class;

    /**
     * 获取Grid数据（预加载关联模型）
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function getGridQuery()
    {
        return AiConversation::with(['provider', 'model']);
    }
}
