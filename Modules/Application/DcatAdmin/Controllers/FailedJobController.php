<?php

namespace Modules\Application\DcatAdmin\Controllers;

use Dcat\Admin\Grid;
use Dcat\Admin\Show;
use Modules\Application\Models\FailedJob;
use Modules\DcatAdmin\DcatAdmin\AdminController;
use Modules\DcatAdmin\DcatAdmin\FilterHelper;
use Modules\DcatAdmin\DcatAdmin\GridHelper;
use Modules\DcatAdmin\DcatAdmin\ShowHelper;

/**
 * 失败队列任务管理控制器
 */
class FailedJobController extends AdminController
{
    /**
     * 页面标题
     *
     * @var string
     */
    protected $title = '失败队列任务管理';

    /**
     * 列表页面
     *
     * @return Grid
     */
    protected function grid()
    {
        return Grid::make(new FailedJob, function (Grid $grid) {
            $helper = new GridHelper($grid, $this);

            $helper->columnId();

            $grid->column('uuid', 'UUID')->copyable();

            $grid->column('queue', '队列名称')->label('primary');

            $grid->column('connection', '连接名称')->label('info');

            $grid->column('job_class', '任务类')->display(function ($value) {
                if (! $value) {
                    return '';
                }
                $parts = explode('\\', $value);

                return end($parts);
            });

            $grid->column('exception_class', '异常类')->display(function ($value) {
                if (! $value) {
                    return '';
                }
                $parts = explode('\\', $value);

                return end($parts);
            })->label('danger');

            $grid->column('exception_message', '异常消息')->limit(50);

            $grid->column('failed_at_formatted', '失败时间')->sortable();

            // 筛选器
            $grid->filter(function (Grid\Filter $filter) {
                $helper = new FilterHelper($filter, $this);

                $filter->like('queue', '队列名称');
                $filter->like('connection', '连接名称');
                $filter->like('uuid', 'UUID');
                $filter->between('failed_at', '失败时间')->datetime();
                $filter->like('exception', '异常信息');
            });

            // 禁用新增、编辑
            $grid->disableCreateButton();
            $grid->disableEditButton();

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
        return Show::make($id, new FailedJob, function (Show $show) {
            $helper = new ShowHelper($show, $this);

            $show->field('id', 'ID');
            $show->field('uuid', 'UUID');
            $show->field('queue', '队列名称');
            $show->field('connection', '连接名称');
            $show->field('job_class', '任务类');
            $show->field('exception_class', '异常类');
            $show->field('exception_message', '异常消息');
            $show->field('failed_at_formatted', '失败时间');

            $show->field('payload', '任务载荷')->json();

            $show->field('exception', '完整异常信息')->code();

            // 禁用编辑
            $show->disableEditButton();
        });
    }
}
