<?php

namespace Modules\AClean\DcatAdmin\Controllers;

use Modules\AClean\DcatAdmin\Repositories\CleanupPlanRepository;
use Modules\AClean\Logics\ModelScannerLogic;
use Dcat\Admin\Admin;
use Dcat\Admin\Form;
use Dcat\Admin\Grid;
use Dcat\Admin\Show;
use Modules\DcatAdmin\DcatAdmin\AdminController;

/**
 * 清理计划管理控制器
 *
 * 路由：/admin/backup-clean-admin/plans
 */
class CleanupPlanController extends AdminController
{
    /**
     * 页面标题
     */
    protected $title = '清理计划管理';

    /**
     * 数据仓库
     */
    protected function repository()
    {
        return CleanupPlanRepository::class;
    }

    /**
     * 列表页面
     */
    protected function grid(): Grid
    {
        return Grid::make(new CleanupPlanRepository, function (Grid $grid) {
            // 基础设置
            $grid->column('id', 'ID')->sortable();
            $grid->column('plan_name', '计划名称')->sortable();

            // 选择的Model类数量
            $grid->column('selected_tables_count', '选择Model数')->display(function () {
                return is_array($this->selected_tables) ? count($this->selected_tables) : 0;
            });

            // 状态
            $grid->column('is_template', '模板')->switch()->sortable();
            $grid->column('is_enabled', '启用状态')->switch()->sortable();

            // 统计信息
            $grid->column('contents_count', '包含表数')->display(function () {
                return $this->contents()->count();
            });

            $grid->column('tasks_count', '关联任务数')->display(function () {
                return $this->tasks()->count();
            });

            // 时间
            $grid->column('created_at', '创建时间')->sortable();
            $grid->column('updated_at', '更新时间')->sortable();

            // 筛选器
            $grid->filter(function (Grid\Filter $filter) {
                $filter->equal('is_template', '模板')->select([
                    1 => '是',
                    0 => '否',
                ]);

                $filter->equal('is_enabled', '启用状态')->select([
                    1 => '启用',
                    0 => '禁用',
                ]);

                $filter->like('plan_name', '计划名称');
                $filter->between('created_at', '创建时间')->datetime();
            });

            // 行操作
            $grid->actions(function (Grid\Displayers\Actions $actions) {
                $actions->append(new \Modules\AClean\DcatAdmin\Actions\ViewPlanContentsAction);
                $actions->append(new \Modules\AClean\DcatAdmin\Actions\AddTableToPlanAction);
                $actions->append(new \Modules\AClean\DcatAdmin\Actions\CreateTaskFromPlanAction);
                $actions->append(new \Modules\AClean\DcatAdmin\Actions\PreviewPlanAction);
            });

            // 批量操作
            $grid->batchActions([
                new \Modules\AClean\DcatAdmin\Actions\BatchEnablePlanAction,
                new \Modules\AClean\DcatAdmin\Actions\BatchDisablePlanAction,
            ]);

            // 工具栏
            $grid->tools([
                new \Modules\AClean\DcatAdmin\Actions\CreatePlanFromTemplateAction,
            ]);

            // 设置每页显示数量
            $grid->paginate(20);
        });
    }

    /**
     * 详情页面
     */
    protected function detail($id): Show
    {
        return Show::make($id, new CleanupPlanRepository, function (Show $show) {
            $show->field('id', 'ID');
            $show->field('plan_name', '计划名称');

            $show->field('selected_tables', '选择的Model类')->json();
            $show->field('global_conditions', '全局清理条件')->json();
            $show->field('backup_config', '备份配置')->json();

            $show->field('is_template', '模板')->using([1 => '是', 0 => '否']);
            $show->field('is_enabled', '启用状态')->using([1 => '启用', 0 => '禁用']);
            $show->field('description', '计划描述');

            $show->field('created_by', '创建者ID');
            $show->field('created_at', '创建时间');
            $show->field('updated_at', '更新时间');

            // 显示计划内容
            $show->relation('contents', '计划内容', function ($model) {
                $grid = new Grid(new \Modules\AClean\Models\CleanupPlanContent);
                $grid->model()->where('plan_id', $model->id);

                $grid->column('table_name', '表名');
                $grid->column('cleanup_type', '清理类型')->using([
                    1 => '清空表',
                    2 => '删除所有',
                    3 => '按时间删除',
                    4 => '按用户删除',
                    5 => '按条件删除',
                ]);
                $grid->column('priority', '优先级');
                $grid->column('batch_size', '批处理大小');
                $grid->column('is_enabled', '启用')->using([1 => '是', 0 => '否']);
                $grid->column('backup_enabled', '备份')->using([1 => '是', 0 => '否']);

                $grid->disableActions();
                $grid->disableCreateButton();
                $grid->disableFilter();
                $grid->disablePagination();

                return $grid;
            });

            // 显示关联任务
            $show->relation('tasks', '关联任务', function ($model) {
                $grid = new Grid(new \Modules\AClean\Models\CleanupTask);
                $grid->model()->where('plan_id', $model->id);

                $grid->column('id', 'ID');
                $grid->column('task_name', '任务名称');
                $grid->column('status', '状态')->using([
                    1 => '待执行',
                    2 => '备份中',
                    3 => '执行中',
                    4 => '已完成',
                    5 => '已失败',
                    6 => '已取消',
                    7 => '已暂停',
                ]);
                $grid->column('progress', '进度')->display(function ($progress) {
                    return $progress.'%';
                });
                $grid->column('created_at', '创建时间');

                $grid->disableActions();
                $grid->disableCreateButton();
                $grid->disableFilter();
                $grid->disablePagination();

                return $grid;
            });
        });
    }

    /**
     * 创建/编辑表单
     */
    protected function form(): Form
    {
        return Form::make(new CleanupPlanRepository, function (Form $form) {
            $form->display('id', 'ID');

            $form->text('plan_name', '计划名称')->required();

            // 获取所有可用的Model类
            $availableModels = $this->getAvailableModels();
            $form->multipleSelect('selected_tables', '选择要清理的Model类')
                ->options($availableModels)
                ->help('选择需要清理的Model类')
                ->required();

            $form->keyValue('global_conditions', '全局清理条件')
                ->help('JSON格式的全局清理条件');

            $form->keyValue('backup_config', '备份配置')
                ->help('JSON格式的备份配置');

            $form->switch('is_template', '设为模板')->default(0);
            $form->switch('is_enabled', '启用状态')->default(1);

            $form->textarea('description', '计划描述');

            $form->hidden('created_by')->value(Admin::user()->getKey());

            $form->display('created_at', '创建时间');
            $form->display('updated_at', '更新时间');
        });
    }

    /**
     * 获取所有可用的Model类
     *
     * @return array<string, string> Model类名映射
     */
    private function getAvailableModels(): array
    {
        return ModelScannerLogic::getAvailableModels();
    }
}
