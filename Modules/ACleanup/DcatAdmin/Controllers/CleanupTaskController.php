<?php

namespace Modules\AClean\DcatAdmin\Controllers;

use Modules\AClean\DcatAdmin\Repositories\CleanupTaskRepository;
use Dcat\Admin\Form;
use Dcat\Admin\Grid;
use Dcat\Admin\Show;
use Modules\DcatAdmin\DcatAdmin\AdminController;

/**
 * 清理任务管理控制器
 *
 * 路由：/admin/backup-clean-admin/tasks
 */
class CleanupTaskController extends AdminController
{
    /**
     * 页面标题
     */
    protected $title = '清理任务管理';

    /**
     * 数据仓库
     */
    protected function repository()
    {
        return CleanupTaskRepository::class;
    }

    /**
     * 列表页面
     */
    protected function grid(): Grid
    {
        return Grid::make(new CleanupTaskRepository, function (Grid $grid) {
            // 基础设置
            $grid->column('id', 'ID')->sortable();
            $grid->column('task_name', '任务名称')->sortable();

            // 关联计划
            $grid->column('plan.plan_name', '关联计划')->sortable();

            // 任务状态
            $grid->column('status', '任务状态')->using([
                1 => '待执行',
                2 => '备份中',
                3 => '执行中',
                4 => '已完成',
                5 => '已失败',
                6 => '已取消',
                7 => '已暂停',
            ])->label([
                1 => 'secondary',
                2 => 'info',
                3 => 'primary',
                4 => 'success',
                5 => 'danger',
                6 => 'warning',
                7 => 'dark',
            ])->sortable();

            // 进度信息
            $grid->column('progress', '执行进度')->display(function ($progress) {
                $color = 'secondary';
                if ($progress >= 100) {
                    $color = 'success';
                } elseif ($progress >= 50) {
                    $color = 'primary';
                } elseif ($progress > 0) {
                    $color = 'info';
                }

                return "<div class='progress' style='height: 20px;'>
                    <div class='progress-bar bg-{$color}' style='width: {$progress}%'>{$progress}%</div>
                </div>";
            });

            $grid->column('current_step', '当前步骤');

            // 统计信息
            $grid->column('processed_tables', '处理进度')->display(function () {
                return "{$this->processed_tables}/{$this->total_tables}";
            });

            $grid->column('deleted_records', '删除记录数')->display(function ($value) {
                return number_format($value);
            });

            // 执行时间
            $grid->column('execution_time', '执行时间(秒)')->display(function ($value) {
                return number_format($value, 3);
            });

            // 时间信息
            $grid->column('started_at', '开始时间')->sortable();
            $grid->column('completed_at', '完成时间')->sortable();
            $grid->column('created_at', '创建时间')->sortable();

            // 筛选器
            $grid->filter(function (Grid\Filter $filter) {
                $filter->equal('status', '任务状态')->select([
                    1 => '待执行',
                    2 => '备份中',
                    3 => '执行中',
                    4 => '已完成',
                    5 => '已失败',
                    6 => '已取消',
                    7 => '已暂停',
                ]);

                $filter->equal('plan_id', '关联计划')->select(
                    \Modules\AClean\Models\CleanupPlan::pluck('plan_name', 'id')->toArray()
                );

                $filter->like('task_name', '任务名称');
                $filter->between('created_at', '创建时间')->datetime();
                $filter->between('started_at', '开始时间')->datetime();
                $filter->between('completed_at', '完成时间')->datetime();
            });

            // 行操作
            $grid->actions(function (Grid\Displayers\Actions $actions) {
                $row = $actions->row;

                // 根据状态显示不同操作
                if ($row->status == 1) { // 待执行
                    $actions->append(new \Modules\AClean\DcatAdmin\Actions\StartTaskAction);
                } elseif ($row->status == 3) { // 执行中
                    $actions->append(new \Modules\AClean\DcatAdmin\Actions\PauseTaskAction);
                    $actions->append(new \Modules\AClean\DcatAdmin\Actions\CancelTaskAction);
                } elseif ($row->status == 7) { // 已暂停
                    $actions->append(new \Modules\AClean\DcatAdmin\Actions\ResumeTaskAction);
                    $actions->append(new \Modules\AClean\DcatAdmin\Actions\CancelTaskAction);
                }

                $actions->append(new \Modules\AClean\DcatAdmin\Actions\ViewTaskLogsAction);

                if ($row->backup_id) {
                    $actions->append(new \Modules\AClean\DcatAdmin\Actions\ViewBackupAction);
                }
            });

            // 批量操作
            $grid->batchActions([
                new \Modules\AClean\DcatAdmin\Actions\BatchCancelTaskAction,
            ]);

            // 工具栏
            $grid->tools([
                new \Modules\AClean\DcatAdmin\Actions\CreateTaskAction,
            ]);

            // 设置每页显示数量
            $grid->paginate(20);

            // 默认排序
            $grid->model()->orderBy('created_at', 'desc');
        });
    }

    /**
     * 详情页面
     */
    protected function detail($id): Show
    {
        return Show::make($id, new CleanupTaskRepository, function (Show $show) {
            $show->field('id', 'ID');
            $show->field('task_name', '任务名称');
            $show->field('plan.plan_name', '关联计划');

            $show->field('status', '任务状态')->using([
                1 => '待执行',
                2 => '备份中',
                3 => '执行中',
                4 => '已完成',
                5 => '已失败',
                6 => '已取消',
                7 => '已暂停',
            ]);

            $show->field('progress', '执行进度')->display(function ($progress) {
                return $progress.'%';
            });

            $show->field('current_step', '当前步骤');

            // 统计信息
            $show->field('total_tables', '总表数');
            $show->field('processed_tables', '已处理表数');
            $show->field('total_records', '总记录数')->display(function ($value) {
                return number_format($value);
            });
            $show->field('deleted_records', '已删除记录数')->display(function ($value) {
                return number_format($value);
            });

            // 性能信息
            $show->field('backup_size', '备份大小')->display(function ($value) {
                return \Modules\AClean\Helpers\FormatHelper::formatBytes($value);
            });
            $show->field('execution_time', '执行时间')->display(function ($value) {
                return number_format($value, 3).' 秒';
            });
            $show->field('backup_time', '备份时间')->display(function ($value) {
                return number_format($value, 3).' 秒';
            });

            // 时间信息
            $show->field('started_at', '开始时间');
            $show->field('backup_completed_at', '备份完成时间');
            $show->field('completed_at', '完成时间');

            $show->field('error_message', '错误信息');
            $show->field('created_by', '创建者ID');
            $show->field('created_at', '创建时间');
            $show->field('updated_at', '更新时间');

            // 显示任务日志
            $show->relation('logs', '执行日志', function ($model) {
                $grid = new Grid(new \Modules\AClean\Models\CleanupLog);
                $grid->model()->where('task_id', $model->id);

                $grid->column('table_name', '表名');
                $grid->column('cleanup_type', '清理类型')->using([
                    1 => '清空表',
                    2 => '删除所有',
                    3 => '按时间删除',
                    4 => '按用户删除',
                    5 => '按条件删除',
                ]);
                $grid->column('before_count', '清理前记录数')->display(function ($value) {
                    return number_format($value);
                });
                $grid->column('after_count', '清理后记录数')->display(function ($value) {
                    return number_format($value);
                });
                $grid->column('deleted_records', '删除记录数')->display(function ($value) {
                    return number_format($value);
                });
                $grid->column('execution_time', '执行时间(秒)');
                $grid->column('created_at', '执行时间');

                $grid->disableActions();
                $grid->disableCreateButton();
                $grid->disableFilter();
                $grid->disablePagination();

                return $grid;
            });
        });
    }

    /**
     * 创建表单
     */
    protected function form(): Form
    {
        return Form::make(new CleanupTaskRepository, function (Form $form) {
            $form->display('id', 'ID');

            $form->text('task_name', '任务名称')->required();

            $form->select('plan_id', '关联计划')
                ->options(\Modules\AClean\Models\CleanupPlan::where('is_enabled', 1)->pluck('plan_name', 'id'))
                ->required();

            $form->hidden('status')->value(1); // 待执行
            $form->hidden('created_by')->value(admin_user_id());

            $form->display('created_at', '创建时间');
            $form->display('updated_at', '更新时间');

            // 禁用编辑（任务创建后不允许修改）
            $form->editing(function (Form $form) {
                $form->display('task_name', '任务名称');
                $form->display('plan.plan_name', '关联计划');
                $form->display('status', '任务状态')->using([
                    1 => '待执行',
                    2 => '备份中',
                    3 => '执行中',
                    4 => '已完成',
                    5 => '已失败',
                    6 => '已取消',
                    7 => '已暂停',
                ]);
            });
        });
    }
}
