<?php

namespace Modules\Application\DcatAdmin\Controllers;

use Dcat\Admin\Grid;
use Dcat\Admin\Show;
use Modules\Application\Models\Job;
use Modules\DcatAdmin\DcatAdmin\AdminController;
use Modules\DcatAdmin\DcatAdmin\FilterHelper;
use Modules\DcatAdmin\DcatAdmin\GridHelper;
use Modules\DcatAdmin\DcatAdmin\ShowHelper;

/**
 * 队列任务管理控制器
 */
class JobController extends AdminController
{
    /**
     * 页面标题
     *
     * @var string
     */
    protected $title = '队列任务管理';

    /**
     * 列表页面
     *
     * @return Grid
     */
    protected function grid()
    {
        return Grid::make(new Job, function (Grid $grid) {
            $helper = new GridHelper($grid, $this);

            $helper->columnId();

            $grid->column('queue', '队列名称')->label('primary');

            $grid->column('job_class', '任务类')->display(function ($value) {
                if (! $value) {
                    return '';
                }
                $parts = explode('\\', $value);

                return end($parts);
            });

            $grid->column('attempts', '尝试次数')->label('info');

            $grid->column('status', '状态')->using([
                '已保留' => '已保留',
                '延迟中' => '延迟中',
                '待处理' => '待处理',
            ])->label([
                '已保留' => 'warning',
                '延迟中' => 'info',
                '待处理' => 'success',
            ]);

            $grid->column('job_parameters_short', '任务参数')->limit(50);

            $grid->column('created_at_formatted', '创建时间')->sortable();
            $grid->column('available_at_formatted', '可用时间');

            // 筛选器
            $grid->filter(function (Grid\Filter $filter) {
                $helper = new FilterHelper($filter, $this);

                $filter->like('queue', '队列名称');
                $filter->between('created_at', '创建时间')->datetime();
                $filter->between('available_at', '可用时间')->datetime();
                $filter->equal('attempts', '尝试次数');
            });

            // 禁用新增、编辑、删除
            $grid->disableCreateButton();
            $grid->disableEditButton();
            $grid->disableDeleteButton();
            $grid->disableBatchDelete();

            // 排序
            $grid->model()->orderBy('id', 'desc');

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
        return Show::make($id, new Job, function (Show $show) {
            $helper = new ShowHelper($show, $this);

            $show->field('id', 'ID');
            $show->field('queue', '队列名称');
            $show->field('job_class', '任务类');
            $show->field('attempts', '尝试次数');
            $show->field('status', '状态');
            $show->field('created_at_formatted', '创建时间');
            $show->field('available_at_formatted', '可用时间');
            $show->field('reserved_at_formatted', '保留时间');

            $show->field('job_parameters', '任务参数');

            $show->field('payload', '任务载荷')->json();

            // 禁用编辑和删除
            $show->disableEditButton();
            $show->disableDeleteButton();
        });
    }
}
