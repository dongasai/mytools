<?php

namespace Modules\AClean\DcatAdmin\Controllers;

use Modules\AClean\DcatAdmin\Repositories\CleanupLogRepository;
use Dcat\Admin\Form;
use Dcat\Admin\Grid;
use Dcat\Admin\Show;
use Modules\DcatAdmin\DcatAdmin\AdminController;

/**
 * 清理日志管理控制器
 *
 * 路由：/admin/backup-clean-admin/logs
 */
class CleanupLogController extends AdminController
{
    /**
     * 页面标题
     */
    protected $title = '清理日志';

    /**
     * 数据仓库
     */
    protected function repository()
    {
        return CleanupLogRepository::class;
    }

    /**
     * 列表页面
     */
    protected function grid(): Grid
    {
        return Grid::make(new CleanupLogRepository, function (Grid $grid) {
            // 基础设置
            $grid->column('id', 'ID')->sortable();

            // 关联信息
            $grid->column('task.task_name', '关联任务')->sortable();
            $grid->column('table_name', '表名')->sortable();

            // 清理类型
            $grid->column('cleanup_type', '清理类型')->using([
                1 => '清空表',
                2 => '删除所有',
                3 => '按时间删除',
                4 => '按用户删除',
                5 => '按条件删除',
            ])->label([
                1 => 'danger',
                2 => 'warning',
                3 => 'info',
                4 => 'primary',
                5 => 'secondary',
            ])->sortable();

            // 记录统计
            $grid->column('before_count', '清理前记录数')->display(function ($value) {
                return number_format($value);
            })->sortable();

            $grid->column('after_count', '清理后记录数')->display(function ($value) {
                return number_format($value);
            })->sortable();

            $grid->column('deleted_records', '删除记录数')->display(function ($value) {
                return number_format($value);
            })->sortable();

            // 删除率
            $grid->column('delete_rate', '删除率')->display(function () {
                if ($this->before_count == 0) {
                    return '0%';
                }
                $rate = ($this->deleted_records / $this->before_count) * 100;
                $color = 'secondary';
                if ($rate >= 90) {
                    $color = 'danger';
                } elseif ($rate >= 50) {
                    $color = 'warning';
                } elseif ($rate > 0) {
                    $color = 'info';
                }

                return "<span class='badge badge-{$color}'>".number_format($rate, 2).'%</span>';
            });

            // 执行时间
            $grid->column('execution_time', '执行时间(秒)')->display(function ($value) {
                $color = 'secondary';
                if ($value >= 60) {
                    $color = 'danger';
                } elseif ($value >= 10) {
                    $color = 'warning';
                } elseif ($value >= 1) {
                    $color = 'info';
                }

                return "<span class='badge badge-{$color}'>".number_format($value, 3).'</span>';
            })->sortable();

            // 执行时间
            $grid->column('created_at', '执行时间')->sortable();

            // 错误信息
            $grid->column('error_message', '错误信息')->display(function ($value) {
                if (empty($value)) {
                    return '<span class="badge badge-success">成功</span>';
                }

                return '<span class="badge badge-danger">失败</span>';
            });

            // 筛选器
            $grid->filter(function (Grid\Filter $filter) {
                $filter->equal('task_id', '关联任务')->select(
                    \Modules\AClean\Models\CleanupTask::pluck('task_name', 'id')->toArray()
                );

                $filter->equal('cleanup_type', '清理类型')->select([
                    1 => '清空表',
                    2 => '删除所有',
                    3 => '按时间删除',
                    4 => '按用户删除',
                    5 => '按条件删除',
                ]);

                $filter->like('table_name', '表名');
                $filter->between('deleted_records', '删除记录数');
                $filter->between('execution_time', '执行时间(秒)');
                $filter->between('created_at', '执行时间')->datetime();

                $filter->scope('has_error', '仅显示错误')->where(function ($query) {
                    $query->whereNotNull('error_message')->where('error_message', '!=', '');
                });
            });

            // 禁用操作
            $grid->disableActions();
            $grid->disableCreateButton();
            $grid->disableBatchActions();

            // 工具栏
            $grid->tools([
                new \Modules\AClean\DcatAdmin\Actions\ExportLogsAction,
                new \Modules\AClean\DcatAdmin\Actions\CleanOldLogsAction,
            ]);

            // 设置每页显示数量
            $grid->paginate(50);

            // 默认排序
            $grid->model()->orderBy('created_at', 'desc');
        });
    }

    /**
     * 详情页面
     */
    protected function detail($id): Show
    {
        return Show::make($id, new CleanupLogRepository, function (Show $show) {
            $show->field('id', 'ID');
            $show->field('task.task_name', '关联任务');
            $show->field('table_name', '表名');

            $show->field('cleanup_type', '清理类型')->using([
                1 => '清空表',
                2 => '删除所有',
                3 => '按时间删除',
                4 => '按用户删除',
                5 => '按条件删除',
            ]);

            // 记录统计
            $show->field('before_count', '清理前记录数')->display(function ($value) {
                return number_format($value);
            });
            $show->field('after_count', '清理后记录数')->display(function ($value) {
                return number_format($value);
            });
            $show->field('deleted_records', '删除记录数')->display(function ($value) {
                return number_format($value);
            });

            // 删除率
            $show->field('delete_rate', '删除率')->display(function () {
                if ($this->before_count == 0) {
                    return '0%';
                }
                $rate = ($this->deleted_records / $this->before_count) * 100;

                return number_format($rate, 2).'%';
            });

            $show->field('execution_time', '执行时间')->display(function ($value) {
                return number_format($value, 3).' 秒';
            });

            $show->field('conditions', '使用的清理条件')->json();
            $show->field('error_message', '错误信息');
            $show->field('created_at', '执行时间');
        });
    }

    /**
     * 表单（只读，日志不允许创建/编辑）
     */
    protected function form(): Form
    {
        return Form::make(new CleanupLogRepository, function (Form $form) {
            $form->display('id', 'ID');
            $form->display('task.task_name', '关联任务');
            $form->display('table_name', '表名');

            $form->display('cleanup_type', '清理类型')->using([
                1 => '清空表',
                2 => '删除所有',
                3 => '按时间删除',
                4 => '按用户删除',
                5 => '按条件删除',
            ]);

            $form->display('before_count', '清理前记录数');
            $form->display('after_count', '清理后记录数');
            $form->display('deleted_records', '删除记录数');
            $form->display('execution_time', '执行时间(秒)');
            $form->display('conditions', '使用的清理条件');
            $form->display('error_message', '错误信息');
            $form->display('created_at', '执行时间');

            // 禁用所有操作
            $form->disableCreatingCheck();
            $form->disableEditingCheck();
            $form->disableViewCheck();
        });
    }
}
