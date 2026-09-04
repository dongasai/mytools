<?php

namespace Modules\DcatAdmin\DcatAdmin\ActionLog;

use Modules\DcatAdmin\Models\AdminActionlog;

abstract class BaseLog implements InterfaceLog
{
    public $objectClass;

    public $admin_id;

    public $before;

    public $after;

    public $url;

    public $unid = RUN_UNIQID;

    public $status = 0;

    public $p1 = null;

    public function __destruct()
    {

        $model = new AdminActionlog;
        $model->object_class = $this->objectClass;
        $model->admin_id = $this->admin_id;
        $model->before = $this->before ?? [];
        if (! $this->after) {
            $this->after = 'is null or empty ';
        }
        $model->after = $this->after;
        $model->url = $this->url;
        $model->unid = $this->unid;
        $model->type1 = static::TYPE;
        $model->status = $this->status;
        $model->p1 = serialize($this->p1);

        $model->save();

    }
}
