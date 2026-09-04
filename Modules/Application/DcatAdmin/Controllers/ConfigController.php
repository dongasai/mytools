<?php

namespace Modules\Application\DcatAdmin\Controllers;

use Dcat\Admin\Grid;
use Dcat\Admin\Http\Controllers\AdminController;
use Modules\Application\DcatAdmin\Actions\ConfigEmbedsEditAction;
use Modules\Application\DcatAdmin\Actions\ConfigFloatEditAction;
use Modules\Application\DcatAdmin\Actions\ConfigImgEditAction;
use Modules\Application\DcatAdmin\Actions\ConfigIntEditAction;
use Modules\Application\DcatAdmin\Actions\ConfigJsonEditAction;
use Modules\Application\DcatAdmin\Actions\ConfigStringEditAction;
use Modules\Application\DcatAdmin\Actions\ConfigSwitchEditAction;
use Modules\Application\DcatAdmin\Actions\ConfigTimeEditAction;
use Modules\Application\DcatAdmin\Actions\ToConfigAdmin;
use Modules\Application\Services\ConfigService;
use Modules\DcatAdmin\DcatAdmin\FilterHelper;
use Modules\DcatAdmin\DcatAdmin\GridHelper;
use Modules\Application\DcatAdmin\Repositories\AppConfig;

/**
 * 配置项操作
 */
class ConfigController extends AdminController
{
    protected $title = '系统配置';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {

        $grid = Grid::make(new AppConfig, function (Grid $grid) {
            $helper = new GridHelper($grid, $this);

            $grid->column('id');
            $grid->column('group', '分组');
            $grid->column('group2', '子分组');
            $grid->column('title', '配置标题')->width(200);
            $grid->column('desc', '描述')->width(300);

            $helper->columnView('value', 'admin_core.config.value', '当前值');
            $grid->disableBatchActions();
            $grid->disableRowSelector();

            //            $helper->fieldUseing('group',AppConfig::$ENMU);

            //            $grid->dia
            $grid->filter(function (Grid\Filter $filter) {
                $filter->panel();
                $filter->expand();
                $helper = new FilterHelper($filter, $this);
                $helper->equalRadioVk('group', ConfigService::getGroupKv(), '分组')->default('应用配置');
                $group = request('group');
                $helper->equalRadioVk('group2', ConfigService::getGroupKv2($group), '子分组');

                // 移除可能导致无限循环的重定向逻辑
            });
            $grid->disableCreateButton();
            $grid->actions(function (Grid\Displayers\Actions $actions) {
                $actions->disableDelete();
                $actions->disableEdit();
                $actions->disableView();
                $actions->disableQuickEdit();
                $actions->append((new ConfigIntEditAction)->setController($this));
                $actions->append((new ConfigImgEditAction)->setController($this));
                $actions->append((new ConfigFloatEditAction)->setController($this));
                $actions->append((new ConfigStringEditAction)->setController($this));
                $actions->append((new ConfigSwitchEditAction)->setController($this));
                $actions->append((new ConfigTimeEditAction)->setController($this));
                $actions->append((new ConfigJsonEditAction)->setController($this));
                $actions->append((new ConfigEmbedsEditAction)->setController($this));

            });
            $grid->disableBatchActions();
            $grid->disableQuickEditButton();
            $grid->disableDeleteButton();
            $grid->disablePagination();
            $grid->tools(function (\Dcat\Admin\Grid\Tools $tools) {
                $tools->append(new ToConfigAdmin);
                //                $tools
            });
        });

        return $grid;
    }
}
