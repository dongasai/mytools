<?php

namespace Modules\FeatureSsh\DcatAdmin\Controllers;

use Dcat\Admin\Layout\Content;
use Dcat\Admin\Widgets\Card;
use Illuminate\Routing\Controller;

/**
 * FeatureSsh 仪表盘控制器
 *
 * 仪表盘控制器
 */
class DashboardController extends Controller
{
    /**
     * 仪表盘首页
     *
     * @param Content $content
     * @return Content
     */
    public function index(Content $content): Content
    {
        return $content
            ->title('FeatureSsh 模块')
            ->description('模块管理面板')
            ->body(new Card('欢迎', '这是 FeatureSsh 模块的管理面板。请根据实际需求修改此内容。'));
    }
}