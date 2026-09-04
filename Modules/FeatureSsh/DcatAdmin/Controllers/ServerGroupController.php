<?php

declare(strict_types=1);

namespace Modules\FeatureSsh\DcatAdmin\Controllers;

use Dcat\Admin\Form;
use Dcat\Admin\Grid;
use Dcat\Admin\Show;
use Modules\DcatAdmin\DcatAdmin\AdminController;
use Modules\FeatureSsh\Models\ServerGroup;

/**
 * SSH服务器分组管理控制器
 *
 * @author AI开发团队
 * @date 2026-09-02
 */
class ServerGroupController extends AdminController
{
    /**
     * 页面标题
     */
    protected $title = '服务器分组管理';

    /**
     * 页面描述
     */
    protected $description = '管理SSH服务器的分组，支持层级分组结构';

    /**
     * Make a grid builder.
     */
    protected function grid(): Grid
    {
        return Grid::make(ServerGroup::with('parent'), function (Grid $grid) {
            $grid->column('id', 'ID')->sortable()->width('60px');
            $grid->column('name', '分组名称')->width('200px');
            $grid->column('parent.name', '父分组')
                ->width('150px')
                ->display(function ($value) {
                    return $value ?: '无';
                });
            $grid->column('servers_count', '服务器数')
                ->width('100px')
                ->display(function () {
                    return $this->servers()->count();
                });
            $grid->column('description', '描述')
                ->width('250px')
                ->display(function ($value) {
                    return $value ?: '-';
                });

            $grid->enableBatchActions();
            $grid->disableViewButton();

            $grid->filter(function (Grid\Filter $filter) {
                $filter->panel();
                $filter->expand(false);

                $filter->like('name', '分组名称');
                $filter->equal('parent_id', '父分组')
                    ->select(function () {
                        return ServerGroup::pluck('name', 'id')->toArray();
                    });
            });
        });
    }

    /**
     * Make a show builder.
     *
     * @param mixed $id
     */
    protected function detail($id): Show
    {
        return Show::make($id, ServerGroup::with('parent', 'children'), function (Show $show) {
            $show->field('id', 'ID');
            $show->field('name', '分组名称');
            $show->field('parent.name', '父分组');
            $show->field('description', '描述');
            $show->field('created_at', '创建时间');
            $show->field('updated_at', '更新时间');

            $show->relation('children', '子分组', function ($model) {
                $grid = new \Dcat\Admin\Grid();
                $grid->column('id', 'ID');
                $grid->column('name', '分组名称');
                $grid->column('description', '描述');

                return $grid;
            });

            $show->relation('servers', '服务器', function ($model) {
                $grid = new \Dcat\Admin\Grid();
                $grid->column('id', 'ID');
                $grid->column('name', '服务器名称');
                $grid->column('host', '主机地址');
                $grid->column('status', '状态');

                return $grid;
            });
        });
    }

    /**
     * Make a form builder.
     */
    protected function form(): Form
    {
        return Form::make(ServerGroup::class, function (Form $form) {
            $form->display('id', 'ID');

            $form->text('name', '分组名称')
                ->required()
                ->rules('required|string|max:100')
                ->help('为分组设置一个名称');

            $form->select('parent_id', '父分组')
                ->options(function () {
                    return ServerGroup::pluck('name', 'id')->toArray();
                })
                ->placeholder('选择父分组（可选）')
                ->help('支持层级分组结构');

            $form->textarea('description', '描述')
                ->rules('max:500')
                ->help('分组描述信息（可选）');

            $form->display('created_at', '创建时间');
            $form->display('updated_at', '更新时间');

            $form->saving(function (Form $form) {
                if ($form->parent_id && $form->parent_id == $form->getKey()) {
                    return $form->response()->error('父分组不能选择自己');
                }
            });
        });
    }
}
