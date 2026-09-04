<?php

namespace Modules\FeatureAi\DcatAdmin\Repositories;

use Dcat\Admin\Repositories\EloquentRepository;
use Modules\FeatureAi\Models\AiServiceMapping;

/**
 * AI服务映射仓库
 */
class AiServiceMappingRepository extends EloquentRepository
{
    /**
     * 模型类名
     *
     * @var string
     */
    protected $eloquentClass = AiServiceMapping::class;
}
