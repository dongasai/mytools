<?php

namespace Modules\DcatAdmin\DcatAdmin\Widgets;

use Dcat\Admin\Admin;
use Dcat\Admin\Http\JsonResponse;
use Dcat\Admin\Widgets\Form as BaseForm;
use Modules\DcatAdmin\DcatAdmin\ActionLog\ActionForm;
use Modules\DcatAdmin\DcatAdmin\Traits\Controller;

class Form extends BaseForm
{
    protected int $status = 0;

    use Controller;

    protected function status($status)
    {
        $this->status = $status;

        return $this;
    }

    public function getActionName()
    {
        return static::class;
    }

    final public function handle($input)
    {
        $log = new ActionForm;
        $log->objectClass = $this->getActionName();
        $log->admin_id = Admin::user()->id;

        $log->url = \request()->getRequestUri();

        $log->before = $input;
        $log->after = [];

        /**
         * @var JsonResponse $resp
         */
        $resp = $this->run($input);
        $log->status = $this->status;
        $log->after = $resp;

        return $resp;
    }

    public function resReturn($res)
    {
        if (is_string($res)) {
            return $this->error($res);
        }

        return $this->success('处理成功')->refresh();

    }

    /**
     * 返回错误信息
     *
     * @return \Dcat\Admin\Http\JsonResponse
     */
    public function error($message)
    {
        $this->status(-1);

        return $this->response()->error(__($message));
    }

    /**
     * 带有翻译的返回
     *
     * @return JsonResponse
     */
    public function _error($message)
    {
        return $this->response()->error($message);
    }

    /**
     * 带有翻译的返回
     *
     * @return JsonResponse
     */
    public function _success($message)
    {
        return $this->response()->success($message);
    }

    /**
     * 返回成功的消息
     *
     * @return \Dcat\Admin\Http\JsonResponse
     */
    public function success($message)
    {
        $this->status(1);

        return $this->response()->success($message);
    }

    protected function getAdminId()
    {
        return Admin::user()->id;

    }
}
