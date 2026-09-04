<?php

namespace Modules\DcatAdmin\DcatAdmin\Widgets;

use Dcat\Admin\Widgets\Table;
use Dcat\Admin\Widgets\Widget;

class Helper extends Widget
{
    /**
     * 数组 进行 table展示
     *
     * 支持键值对
     *
     * @return Table
     */
    public static function array2Table($list)
    {
        $title = [];
        $data = [];
        foreach ($list as $item) {
            $data[] = [$item];
        }

        //        dd($list);
        return Table::make($title, $data);

    }
}
