<?php

namespace Modules\DcatAdmin\DcatAdmin\Repositories;

use Dcat\Admin\Grid;
use Dcat\Admin\Repositories\Repository;

/**
 * S3存储管理Repository
 */
class S3Repository extends Repository
{
    public function get(Grid\Model $model)
    {
        // 这里可以添加S3相关的业务逻辑
        // 目前返回空数组，可以根据实际需求调整
        return [];
    }
}
