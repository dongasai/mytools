<?php

namespace Modules\Application\DcatAdmin\Controllers;

use Dcat\Admin\Grid;
use Dcat\Admin\Show;
use Modules\Application\Models\JobBatch;
use Modules\DcatAdmin\DcatAdmin\AdminController;
use Modules\DcatAdmin\DcatAdmin\FilterHelper;
use Modules\DcatAdmin\DcatAdmin\GridHelper;
use Modules\DcatAdmin\DcatAdmin\ShowHelper;

/**
 * 批量队列任务管理控制器
 */
class JobBatchController extends AdminController
{
    /**
     * 页面标题
     *
     * @var string
     */
    protected $title = '批量队列任务管理';

    /**
     * 列表页面
     *
     * @return Grid
     */
    protected function grid()
    {
        return Grid::make(new JobBatch, function (Grid $grid) {
            $helper = new GridHelper($grid, $this);

            $grid->column('id', '批次ID')->copyable();

            $grid->column('name', '批次名称')->limit(30);

            $grid->column('total_jobs', '总任务数')->label('info');

            $grid->column('success_jobs', '成功任务数')->label('success');

            $grid->column('pending_jobs', '待处理数')->label('warning');

            $grid->column('failed_jobs', '失败任务数')->label('danger');

            $grid->column('progress_percentage', '完成进度')->display(function ($value) {
                return $value.'%';
            })->progressBar([
                'style' => 'primary',
                'size' => 'sm',
            ]);

            $grid->column('status', '状态')->using([
                '已取消' => '已取消',
                '已完成' => '已完成',
                '处理中' => '处理中',
                '未知' => '未知',
            ])->label([
                '已取消' => 'danger',
                '已完成' => 'success',
                '处理中' => 'warning',
                '未知' => 'secondary',
            ]);

            $grid->column('runtime_formatted', '运行时长');

            $grid->column('created_at_formatted', '创建时间')->sortable();

            // 筛选器
            $grid->filter(function (Grid\Filter $filter) {
                $helper = new FilterHelper($filter, $this);

                $filter->like('id', '批次ID');
                $filter->like('name', '批次名称');
                $filter->between('total_jobs', '总任务数');
                $filter->between('created_at', '创建时间')->datetime();
                $filter->equal('status', '状态')->select([
                    '已取消' => '已取消',
                    '已完成' => '已完成',
                    '处理中' => '处理中',
                    '未知' => '未知',
                ]);
            });

            // 禁用新增、编辑、删除
            $grid->disableCreateButton();
            $grid->disableEditButton();
            $grid->disableDeleteButton();
            $grid->disableBatchDelete();

            // 排序
            $grid->model()->orderBy('created_at', 'desc');

            // 分页
            $grid->paginate(20);
        });
    }

    /**
     * 详情页面
     *
     * @param  mixed  $id
     * @return Show
     */
    protected function detail($id)
    {
        return Show::make($id, new JobBatch, function (Show $show) {
            new ShowHelper($show, $this);

            $show->field('id', '批次ID');
            $show->field('name', '批次名称');
            $show->field('total_jobs', '总任务数');
            $show->field('success_jobs', '成功任务数');
            $show->field('pending_jobs', '待处理任务数');
            $show->field('failed_jobs', '失败任务数');
            $show->field('progress_percentage', '完成进度')->as(function ($value) {
                return $value.'%';
            });
            $show->field('failure_rate', '失败率')->as(function ($value) {
                return $value.'%';
            });
            $show->field('status', '状态');
            $show->field('runtime_formatted', '运行时长');
            $show->field('created_at_formatted', '创建时间');
            $show->field('finished_at_formatted', '完成时间');
            $show->field('cancelled_at_formatted', '取消时间');

            $show->field('failed_job_ids', '失败任务ID列表')->json();
            $show->field('options', '选项配置')->json();

            // 禁用编辑和删除
            $show->disableEditButton();
            $show->disableDeleteButton();
        });
    }
}
