<?php

namespace Modules\FeatureAi\DcatAdmin\Repositories;

use Dcat\Admin\Repositories\EloquentRepository;
use Modules\FeatureAi\Models\AiProvider;

/**
 * AI供应商配置仓库
 */
class AiProviderRepository extends EloquentRepository
{
    /**
     * 模型类名
     *
     * @var string
     */
    protected $eloquentClass = AiProvider::class;
}
