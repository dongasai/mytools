<?php

namespace Modules\DcatAdmin\DcatAdmin\Controllers;

use Dcat\Admin\Grid;
use Dcat\Admin\Http\Controllers\AdminController;
use Dcat\Admin\Layout\Content;
use Modules\DcatAdmin\DcatAdmin\Widgets\Iframe;

/**
 * 开发常用
 */
class DevController extends AdminController
{
    public function index(Content $content)
    {
        return $content
            ->title('日志查看')
            ->body($this->info());
    }

    public function index2()
    {

        dump('config-wechat', config('wechat'));
        dump('config-wechat-pay', config('wechat_pay'));

        dump('SERVER', $_SERVER);
        dump('ENV', $_ENV);
        dump('REQUEST', $_REQUEST);
        //        dump('REQUEST',$);

    }

    public function info()
    {
        $content = '';

        $iframe = new Iframe(admin_url('dev/dev2'));
        $content .= $iframe->render();

        return view('module_dcatadmin::dev.info', [
            'content' => $content,
        ]);
    }

    public function pinfo()
    {
        $content = '';

        $iframe = new Iframe(admin_url('dev/pinfo2'), '100%');
        $iframe->height = '900px';
        $content .= $iframe->render();

        return view('module_dcatadmin::dev.infoall', [
            'content' => $content,
        ]);
    }

    public function pinfo2()
    {
        $content = '';

        $iframe = new Iframe(admin_url('dev/dev2'));
        $content .= $iframe->render();

        return view('module_dcatadmin::dev.pinfo');
    }

    public function trace(Content $content)
    {
        return $content->row($this->trace_fom())->row(view('module_dcatadmin::dev.info'));
    }

    /**
     * 路由列表.name
     *
     * @return Content
     */
    public function router(Content $content)
    {
        return $content
            ->title('路由名称')
            ->body($this->router_names());
    }

    public function router_names()
    {
        return Grid::make(new \Modules\DcatAdmin\DcatAdmin\Repositories\RouteName, function (Grid $grid) {
            $grid->column('as', '路由名称');
            $grid->column('prefix', '前缀');
            $grid->column('namespace', '命名空间');
            $grid->column('controller', '控制器');
            $grid->column('uses', '使用方法');

            $grid->disableActions(true);
            $grid->disableBatchActions(true);
            $grid->disableToolbar(true);
            $grid->disablePagination();
        });
    }
}
