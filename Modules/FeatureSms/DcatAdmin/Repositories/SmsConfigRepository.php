<?php

namespace Modules\FeatureSms\DcatAdmin\Repositories;

use Dcat\Admin\Repositories\EloquentRepository;
use Modules\FeatureSms\Models\SmsConfig;

/**
 * 短信配置仓库
 */
class SmsConfigRepository extends EloquentRepository
{
    /**
     * 模型类名
     *
     * @var string
     */
    protected $eloquentClass = SmsConfig::class;
}
