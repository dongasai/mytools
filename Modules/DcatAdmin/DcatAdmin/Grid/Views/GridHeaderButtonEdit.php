<?php

namespace Modules\DcatAdmin\DcatAdmin\Grid\Views;

use Dcat\Admin\Grid\Tools\AbstractTool;

/**
 * 保存为视图按钮
 */
class GridHeaderButtonEdit extends AbstractTool
{
    public $title = '修改视图';

    public function render(): string
    {

        $url = admin_route('admin_view.edit',
            [
                'admin_view' => request('_viewid'),
            ]
        );
        //        dump($url);
        $title = $this->title;

        return "<a href='{$url}' class='btn btn-primary'>
    <i class='fa fa-edit '></i><span class='d-none d-sm-inline'>&nbsp;&nbsp;{$title}</span>
</a>";

    }
}
