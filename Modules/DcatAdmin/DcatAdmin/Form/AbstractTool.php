<?php

namespace Modules\DcatAdmin\DcatAdmin\Form;

use Dcat\Admin\Grid\Tools\AbstractTool as BaseAbstractTool;
use Modules\DcatAdmin\DcatAdmin\Traits\ReturnRes;

/**
 * 工具基础类
 */
class AbstractTool extends BaseAbstractTool
{
    use ReturnRes;

    /**
     * 获取标题
     *
     * @return string|null
     */
    public function getTitle()
    {
        return $this->title;
    }
}
