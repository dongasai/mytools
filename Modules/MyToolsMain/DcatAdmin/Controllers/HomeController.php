<?php

namespace Modules\MyToolsMain\DcatAdmin\Controllers;

use Dcat\Admin\Layout\Content;
use Dcat\Admin\Layout\Row;
use Dcat\Admin\Widgets\Card;
use Dcat\Admin\Widgets\InfoBox;
use Illuminate\Routing\Controller;

/**
 * 后台首页控制器
 * 个人工具后台首页
 */
class HomeController extends Controller
{
    protected $title = '个人工具';

    /**
     * 后台首页
     *
     * @param Content $content
     * @return Content
     */
    public function index(Content $content)
    {
        return $content
            ->title($this->title)
            ->description('欢迎使用个人工具集')
            ->body(function (Row $row) {
                $row->column(3, new Card(
                    new InfoBox('AI功能', 'robot', 'blue', '/admin/feature-ai', '智能AI')
                ));
                $row->column(3, new Card(
                    new InfoBox('Excel处理', 'file-excel-o', 'green', '/admin/feature-excel', '表格工具')
                ));
                $row->column(3, new Card(
                    new InfoBox('文件管理', 'folder-o', 'yellow', '/admin/a-file', '文件存储')
                ));
                $row->column(3, new Card(
                    new InfoBox('演示模块', 'desktop', 'purple', '/admin/demo5', '功能演示')
                ));
            });
    }
}
