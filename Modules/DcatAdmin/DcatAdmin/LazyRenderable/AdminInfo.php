<?php

namespace Modules\DcatAdmin\DcatAdmin\LazyRenderable;

use Dcat\Admin\Layout\Row;
use Dcat\Admin\Widgets\Table;
use Modules\DcatAdmin\DcatAdmin\Support\LazyRenderable;
use Modules\DcatAdmin\Models\AdminUser;

class AdminInfo extends LazyRenderable
{
    public function index($admin_id)
    {

        $content = '';
        $row = new Row;

        $content .= $row->render();
        $admin = AdminUser::query()->find($admin_id);

        $infoArray = [];
        $infoArray['username'] = $admin->username;

        $content .= Table::make($infoArray);

        return view('admin_core.admin_info', [
            'admin_id' => $admin_id,
            'content' => $content,
        ]);

    }

    public function render()
    {
        $admin_id = $this->admin_id;

        return $this->index($admin_id);
    }
}