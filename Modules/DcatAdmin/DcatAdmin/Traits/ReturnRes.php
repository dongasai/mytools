<?php

namespace Modules\DcatAdmin\DcatAdmin\Traits;

use Dcat\Admin\Actions\Response;

trait ReturnRes
{
    protected $response;

    /**
     * @return Response
     */
    public function response()
    {
        if (is_null($this->response)) {
            $this->response = new Response;
        }

        return $this->response;
    }

    public function resResponse($res = null, $msg = '')
    {
        if (is_string($res)) {
            return $this->response()->error("处理失败: $res ")->refresh();
        }

        return $this->response()->success("处理成功 $msg")->refresh();

    }
}
