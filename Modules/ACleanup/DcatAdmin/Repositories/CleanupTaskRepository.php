<?php

namespace Modules\AClean\DcatAdmin\Repositories;

use Dcat\Admin\Repositories\EloquentRepository;
use Modules\AClean\Models\CleanupTask;

/**
 * 清理任务数据仓库
 *
 * 用于 Dcat Admin 后台管理的数据访问
 */
class CleanupTaskRepository extends EloquentRepository
{
    /**
     * 模型类名
     */
    protected $eloquentClass = CleanupTask::class;
}
