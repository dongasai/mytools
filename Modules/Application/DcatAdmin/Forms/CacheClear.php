<?php

namespace Modules\Application\DcatAdmin\Forms;

use Dcat\Admin\Actions\Action;
use Dcat\Admin\Actions\Response;
use Dcat\Admin\Traits\LazyWidget;
use Illuminate\Http\Request;
use Modules\Application\Services\ConfigService;

/**
 * 清楚缓存
 */
class CacheClear extends Action
{
    use LazyWidget;

    public $title = '清空缓存';

    protected $htmlClasses = [
        'btn', 'btn-info',
    ];

    /**
     * 处理当前动作的请求接口，如果不需要请直接删除
     *
     *
     * @return Response
     */
    public function handle(Request $request)
    {
        ConfigService::clear_cache();

        return $this->response()
            ->success('清楚缓存成功')->refresh();
    }

    public function confirm()
    {
        return ['确定要清空缓存么?', '这个清空缓存将清空: <br> Config缓存 和  其他缓存'];
    }
}
