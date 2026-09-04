<?php

namespace Modules\DcatAdmin\DcatAdmin\Repositories;

use Dcat\Admin\Repositories\Repository;

/**
 * 开发管理Repository
 */
class DevRepository extends Repository
{
    /**
     * 获取开发列表
     *
     * @return mixed
     */
    public function get(\Dcat\Admin\Grid\Model $model)
    {
        // 这里可以添加具体的业务逻辑
        // 目前返回空数组，可以根据实际需求调整
        return [];
    }
}
