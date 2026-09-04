<?php

namespace Modules\Application\DcatAdmin\Actions;

use Modules\DcatAdmin\DcatAdmin\Grid\Tool\HrefAbstractTool;

class ToConfig extends HrefAbstractTool
{
    protected $title = '配置';

    public function href(): string
    {
        // HrefAbstractTool
        // 使用URL而不是路由名称来避免前缀问题
    $url = admin_url('module_application/config') . '?group=' . urlencode(request('group'));
    return $url;

    }
}
