<?php

namespace Modules\DcatAdmin\DcatAdmin\ActionLog;

use Dcat\Admin\Admin;
use Dcat\Admin\Grid\Exporters\ExcelExporter;

/**
 * 导出日至
 */
class Exporter extends ExcelExporter
{
    const TYPE = 'exporter';

    /**
     * 创建导出日志
     *
     * @return void
     */
    public static function create($Repository, $input)
    {
        $log = new self;
        $log->objectClass = get_class($Repository);
        $log->admin_id = Admin::user()->id;
        $log->url = \request()->url();
        $log->after = [];
        $log->before = $input;

    }
}
