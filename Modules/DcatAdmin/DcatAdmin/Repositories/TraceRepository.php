<?php

namespace Modules\DcatAdmin\DcatAdmin\Repositories;

use Dcat\Admin\Grid;
use Dcat\Admin\Repositories\Repository;

/**
 * 日志读取
 */
class TraceRepository extends Repository
{
    public function get(Grid\Model $model)
    {
        $unid = null;
        if ($model->getQueries()->first()) {
            $unid = $model->getQueries()->first()['arguments'][1];
        }
        // dump($unid);

        $list = $this->list1($unid);

        return $list;
    }

    public function list1($unid = null)
    {
        $list = [];
        if ($unid) {
            $d = \DLaravel\Trace::getData($unid);
            $arr = [
                'id' => $unid,
                'unid' => $d,
            ];
            $list[] = $arr;

            return $list;
        }

        $l = \DLaravel\Trace::getlist();

        foreach ($l as $k => $item) {
            if ($unid) {
                if ($item != $unid) {
                    continue;
                }
            }
            $arr = [
                'id' => $k,
                'unid' => $item,
            ];
            $list[] = $arr;
        }

        return $list;

    }
}
