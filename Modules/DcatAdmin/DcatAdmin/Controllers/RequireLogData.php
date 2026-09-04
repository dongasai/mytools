<?php

namespace Modules\DcatAdmin\DcatAdmin\Controllers;

use Dcat\Admin\Support\LazyRenderable;
use DLaravel\Model\RequestLog;

class RequireLogData extends LazyRenderable
{
    public function render()
    {
        // 获取ID
        $id = $this->key;
        $model = RequestLog::query()->find($id);
        $data = [];
        if ($model->headers) {
            $data['headers'] = unserialize($model->headers);
        }
        if ($model->query) {
            //                    dump($model->query);
            $data['query'] = unserialize($model->query);
        }
        if ($model->headers) {
            $data['post'] = unserialize($model->post);
        }

        if ($model->error) {
            $data['error'] = unserialize($model->error);
        }
        $resp = (string) $model->response;
        strlen($resp);
        //                dump($resp);
        if ($resp) {
            $data['response'] = json_decode($resp);
        }

        dump($data);

    }
}
