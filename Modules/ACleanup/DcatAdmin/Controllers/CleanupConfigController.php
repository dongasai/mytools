<?php

namespace Modules\AClean\DcatAdmin\Controllers;

use Modules\AClean\DcatAdmin\Repositories\CleanupConfigRepository;
use Modules\AClean\Models\CleanupConfig;
use Dcat\Admin\Form;
use Dcat\Admin\Grid;
use Dcat\Admin\Show;
use Modules\DcatAdmin\DcatAdmin\AdminController;

/**
 * 清理配置管理控制器
 *
 * 路由：/admin/backup-clean-admin/configs
 */
class CleanupConfigController extends AdminController
{
    /**
     * 页面标题
     */
    protected $title = '清理配置管理';

    /**
     * 数据仓库
     */
    protected function repository()
    {
        return CleanupConfigRepository::class;
    }

    /**
     * 列表页面
     */
    protected function grid(): Grid
    {
        return Grid::make(new CleanupConfigRepository, function (Grid $grid) {
            // 基础设置
            $grid->column('id', 'ID')->sortable();
            $grid->column('table_name', '表名')->sortable();
            $grid->column('module_name', '模块')->sortable();

            // 数据分类
            $grid->column('data_category', '数据分类')->using([
                1 => '用户数据',
                2 => '日志数据',
                3 => '交易数据',
                4 => '缓存数据',
                5 => '配置数据',
            ])->label([
                1 => 'primary',
                2 => 'info',
                3 => 'warning',
                4 => 'secondary',
                5 => 'danger',
            ])->sortable();

            // 清理类型
            $grid->column('default_cleanup_type', '默认清理类型')->using([
                1 => '清空表',
                2 => '删除所有',
                3 => '按时间删除',
                4 => '按用户删除',
                5 => '按条件删除',
            ])->sortable();

            // 状态和优先级
            $grid->column('is_enabled', '启用状态')->switch()->sortable();
            $grid->column('priority', '优先级')->sortable();
            $grid->column('batch_size', '批处理大小')->sortable();

            // 最后清理时间
            $grid->column('last_cleanup_at', '最后清理时间')->sortable();
            $grid->column('created_at', '创建时间')->sortable();

            // 筛选器
            $grid->filter(function (Grid\Filter $filter) {
                $filter->equal('module_name', '模块')->select(
                    CleanupConfig::distinct()->pluck('module_name', 'module_name')->toArray()
                );

                $filter->equal('data_category', '数据分类')->select([
                    1 => '用户数据',
                    2 => '日志数据',
                    3 => '交易数据',
                    4 => '缓存数据',
                    5 => '配置数据',
                ]);

                $filter->equal('default_cleanup_type', '清理类型')->select([
                    1 => '清空表',
                    2 => '删除所有',
                    3 => '按时间删除',
                    4 => '按用户删除',
                    5 => '按条件删除',
                ]);

                $filter->equal('is_enabled', '启用状态')->select([
                    1 => '启用',
                    0 => '禁用',
                ]);

                $filter->like('table_name', '表名');
                $filter->between('priority', '优先级');
            });

            // 批量操作
            $grid->batchActions([
                new \Modules\AClean\DcatAdmin\Actions\Batch\BatchEnableAction,
                new \Modules\AClean\DcatAdmin\Actions\Batch\BatchDisableAction,
            ]);

            // 工具栏
            $grid->tools([
                new \Modules\AClean\DcatAdmin\Actions\ScanTablesAction,
            ]);

            // 行操作
            $grid->actions(function (Grid\Displayers\Actions $actions) {
                $actions->append(new \Modules\AClean\DcatAdmin\Actions\TestCleanupAction);
            });

            // 禁用创建按钮（配置通过扫描生成）
            $grid->disableCreateButton();

            // 设置每页显示数量
            $grid->paginate(50);
        });
    }

    /**
     * 详情页面
     */
    protected function detail($id): Show
    {
        return Show::make($id, new CleanupConfigRepository, function (Show $show) {
            $show->field('id', 'ID');
            $show->field('table_name', '表名');
            $show->field('module_name', '模块名称');

            $show->field('data_category', '数据分类')->using([
                1 => '用户数据',
                2 => '日志数据',
                3 => '交易数据',
                4 => '缓存数据',
                5 => '配置数据',
            ]);

            $show->field('default_cleanup_type', '默认清理类型')->using([
                1 => '清空表',
                2 => '删除所有',
                3 => '按时间删除',
                4 => '按用户删除',
                5 => '按条件删除',
            ]);

            $show->field('default_conditions', '默认条件')->json();
            $show->field('is_enabled', '启用状态')->using([1 => '启用', 0 => '禁用']);
            $show->field('priority', '优先级');
            $show->field('batch_size', '批处理大小');
            $show->field('description', '描述');
            $show->field('last_cleanup_at', '最后清理时间');
            $show->field('created_at', '创建时间');
            $show->field('updated_at', '更新时间');
        });
    }

    /**
     * 编辑表单
     */
    protected function form(): Form
    {
        return Form::make(new CleanupConfigRepository, function (Form $form) {
            $form->display('id', 'ID');
            $form->display('table_name', '表名');
            $form->display('module_name', '模块名称');
            $form->display('data_category', '数据分类')->using([
                1 => '用户数据',
                2 => '日志数据',
                3 => '交易数据',
                4 => '缓存数据',
                5 => '配置数据',
            ]);

            $form->select('default_cleanup_type', '默认清理类型')
                ->options([
                    1 => '清空表',
                    2 => '删除所有',
                    3 => '按时间删除',
                    4 => '按用户删除',
                    5 => '按条件删除',
                ])
                ->required();

            $form->keyValue('default_conditions', '默认条件')
                ->help('JSON格式的清理条件配置');

            $form->switch('is_enabled', '启用状态')->default(1);
            $form->number('priority', '优先级')->default(100)->min(1)->max(999);
            $form->number('batch_size', '批处理大小')->default(1000)->min(100)->max(10000);
            $form->textarea('description', '描述');

            $form->display('last_cleanup_at', '最后清理时间');
            $form->display('created_at', '创建时间');
            $form->display('updated_at', '更新时间');
        });
    }
}
