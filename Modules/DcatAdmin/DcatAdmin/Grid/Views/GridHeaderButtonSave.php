<?php

namespace Modules\DcatAdmin\DcatAdmin\Grid\Views;

use Dcat\Admin\Grid\Tools\AbstractTool;
use Illuminate\Support\Facades\Route;

/**
 * 保存为视图按钮
 */
class GridHeaderButtonSave extends AbstractTool
{
    public $title = '保存为新视图';

    public function render(): string
    {
        $get = request()->query();
        $filteredArray = array_filter($get, '\DLaravel\Helper\Helper::not_null');
        unset($filteredArray['_pjax']);
        unset($filteredArray['pjax']);
        unset($filteredArray['_viewid']);
        if (empty($filteredArray)) {
            return '';
        }

        $id = $this->getKey();
        $router = Route::getCurrentRoute();

        $filteredArray['_router_name'] = $router->getName();
        $url = admin_route('admin_view_add',
            $filteredArray
        );

        $title = $this->title;

        return "<a href='{$url}' class='btn btn-primary'>
    <i class='feather icon-plus'></i><span class='d-none d-sm-inline'>&nbsp;&nbsp;{$title}</span>
</a>";

    }
}
