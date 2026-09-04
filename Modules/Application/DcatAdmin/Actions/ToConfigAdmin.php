<?php

namespace Modules\Application\DcatAdmin\Actions;

use Modules\DcatAdmin\DcatAdmin\Grid\Tool\HrefAbstractTool;

class ToConfigAdmin extends HrefAbstractTool
{
    protected $title = '管理';

    public function href(): string
    {
        // HrefAbstractTool
        // 使用URL而不是路由名称来避免前缀问题
    $url = admin_url('module_application/config-admin') . '?group=' . urlencode(request('group'));
    return $url;
    }
}
