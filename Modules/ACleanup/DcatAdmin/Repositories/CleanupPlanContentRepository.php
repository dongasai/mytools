<?php

namespace Modules\AClean\DcatAdmin\Repositories;

use Dcat\Admin\Repositories\EloquentRepository;
use Modules\AClean\Models\CleanupPlanContent;

/**
 * 计划内容数据仓库
 *
 * 用于 Dcat Admin 后台管理的数据访问
 */
class CleanupPlanContentRepository extends EloquentRepository
{
    /**
     * 模型类名
     */
    protected $eloquentClass = CleanupPlanContent::class;

    /**
     * 构造函数 - 设置关联关系
     */
    public function __construct()
    {
        // 设置需要预加载的关联关系
        parent::__construct(['plan']);
    }
}
