<?php

declare(strict_types=1);

namespace Modules\FeatureAi\DcatAdmin\Repositories;

use Dcat\Admin\Repositories\EloquentRepository;
use Modules\FeatureAi\Models\AiProviderModel;

/**
 * AI模型配置仓库
 *
 * 用于管理AI供应商下的具体模型配置，包括模型名称、类型、token限制、成本定价等
 */
class AiProviderModelRepository extends EloquentRepository
{
    /**
     * 模型类名
     *
     * @var string
     */
    protected $eloquentClass = AiProviderModel::class;
}