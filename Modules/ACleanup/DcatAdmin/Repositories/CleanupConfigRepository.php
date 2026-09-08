<?php

namespace Modules\AClean\DcatAdmin\Repositories;

use Dcat\Admin\Repositories\EloquentRepository;
use Modules\AClean\Models\CleanupConfig;

/**
 * 清理配置数据仓库
 *
 * 用于 Dcat Admin 后台管理的数据访问
 */
class CleanupConfigRepository extends EloquentRepository
{
    /**
     * 模型类名
     */
    protected $eloquentClass = CleanupConfig::class;
}
