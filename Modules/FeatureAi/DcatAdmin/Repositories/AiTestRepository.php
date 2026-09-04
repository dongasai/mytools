<?php

declare(strict_types=1);

namespace Modules\FeatureAi\DcatAdmin\Repositories;

use Dcat\Admin\Repositories\EloquentRepository;
use Modules\FeatureAi\Models\AiTest;

/**
 * AI集成测试记录仓库
 *
 * 用于管理AI集成测试记录，包括连接测试、响应测试、成本测试、图片测试等
 */
class AiTestRepository extends EloquentRepository
{
    /**
     * 模型类名
     *
     * @var string
     */
    protected $eloquentClass = AiTest::class;
}
