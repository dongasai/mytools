<?php

namespace Modules\DcatAdmin\DcatAdmin\Grid\Views;

/**
 * 保存为视图按钮
 */
class GridHeaderButtonUpdate2 extends GridHeaderButtonPrivate
{
    public $title = '更新视图';

    public function render(): string
    {

        $get = request()->query();
        $filteredArray = array_filter($get, '\DLaravel\Helper\Helper::not_null');
        unset($filteredArray['_pjax']);
        unset($filteredArray['pjax']);

        $url = admin_route('admin_view_add',
            $filteredArray
        );

        $title = $this->title;
        //        dump($filteredArray,$this->adminGridView->p1);
        if ($filteredArray == $this->adminGridView->p1) {
            return '';
        }

        return "<a href='{$url}' class='btn btn-primary'>
    <i class='fa fa-cloud-upload '></i><span class='d-none d-sm-inline'>&nbsp;&nbsp;{$title}</span>
</a>";

    }
}
