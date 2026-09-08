<?php

namespace Modules\MyToolsMain\DcatAdmin\Controllers;

use Dcat\Admin\Layout\Content;
use Dcat\Admin\Layout\Row;
use Dcat\Admin\Widgets\Card;
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
                    '<i class="feather icon-cpu"></i> AI功能',
                    '<a href="/admin/featureai" class="btn btn-primary">智能AI工具</a>'
                ));
                $row->column(3, new Card(
                    '<i class="feather icon-file-text"></i> Excel处理',
                    '<a href="/admin/featureexcel" class="btn btn-success">表格工具</a>'
                ));
                $row->column(3, new Card(
                    '<i class="feather icon-folder"></i> 文件管理',
                    '<a href="/admin/module_afile/files" class="btn btn-warning">文件存储</a>'
                ));
                $row->column(3, new Card(
                    '<i class="feather icon-grid"></i> 演示模块',
                    '<a href="/admin/module_demo5" class="btn btn-secondary">功能演示</a>'
                ));
            });
    }
}
