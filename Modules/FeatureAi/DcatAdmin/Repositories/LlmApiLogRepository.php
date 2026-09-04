<?php

namespace Modules\FeatureAi\DcatAdmin\Repositories;

use Dcat\Admin\Repositories\EloquentRepository;
use Modules\FeatureAi\Models\LlmApiLog;

/**
 * LLM API调用日志仓库
 */
class LlmApiLogRepository extends EloquentRepository
{
    /**
     * 模型类名
     *
     * @var string
     */
    protected $eloquentClass = LlmApiLog::class;

    /**
     * 获取Grid数据（预加载关联模型）
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function getGridQuery()
    {
        return LlmApiLog::with(['provider', 'model']);
    }
}
