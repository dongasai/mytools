<?php

namespace Modules\DcatAdmin\DcatAdmin\Repositories;

use Dcat\Admin\Grid;
use Dcat\Admin\Repositories\Repository;

/**
 * 日志读取 Cron
 */
class LogsCron extends Repository
{
    public function get(Grid\Model $model)
    {
        $list = [];
        $path = storage_path('logs/cron.log');

        if (file_exists($path)) {
            $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            foreach ($lines as $line) {
                $list[] = ['content' => $line];
            }
        }

        return array_reverse(array_slice($list, -100)); // 返回最近100行
    }
}
