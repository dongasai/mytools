<?php

namespace Modules\Application\DcatAdmin\Controllers;

use Dcat\Admin\Grid;
use Dcat\Admin\Show;
use Modules\Application\Models\JobRun;
use Modules\DcatAdmin\DcatAdmin\AdminController;
use Modules\DcatAdmin\DcatAdmin\FilterHelper;
use Modules\DcatAdmin\DcatAdmin\GridHelper;
use Modules\DcatAdmin\DcatAdmin\ShowHelper;

/**
 * 队列运行记录管理控制器
 */
class JobRunController extends AdminController
{
    /**
     * 页面标题
     *
     * @var string
     */
    protected $title = '队列运行记录管理';

    /**
     * 列表页面
     *
     * @return Grid
     */
    protected function grid()
    {
        return Grid::make(new JobRun, function (Grid $grid) {
            $helper = new GridHelper($grid, $this);

            $helper->columnId();

            $grid->column('queue', '队列名称')->label('primary');

            $grid->column('runclass', '运行类');

            $grid->column('attempts', '尝试次数')->label('info');

            $grid->column('status', '状态');

            $grid->column('runtime_formatted', '运行时间');

            $grid->column('job_parameters_short', '任务参数');

            $grid->column('desc_short', '描述')->limit(50);

            $grid->column('created_at_formatted', '创建时间')->sortable();

            // 筛选器
            $grid->filter(function (Grid\Filter $filter) {
                $helper = new FilterHelper($filter, $this);

                $filter->like('queue', '队列名称');
                $filter->like('runclass', '运行类');
                $filter->equal('status', '状态')->select([
                    'run-end' => '已完成',
                    'run' => '运行中',
                    'wait' => '待处理',
                    'error' => '失败',
                ]);
                $filter->between('runtime', '运行时间(秒)');
                $filter->between('created_at', '创建时间')->datetime();
                $filter->like('desc', '描述');
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
        return Show::make($id, new JobRun, function (Show $show) {
            new ShowHelper($show, $this);

            $show->field('id', 'ID');
            $show->field('queue', '队列名称');
            $show->field('runclass', '运行类');
            $show->field('job_class', '任务类');
            $show->field('attempts', '尝试次数');
            $show->field('status_label', '状态');
            $show->field('runtime_formatted', '运行时间');
            $show->field('job_parameters', '任务参数');
            $show->field('desc', '描述信息');
            $show->field('created_at_formatted', '创建时间');
            $show->field('available_at_formatted', '可用时间');
            $show->field('reserved_at_formatted', '保留时间');

            $show->field('payload', '任务载荷')->json();

            // 禁用编辑和删除
            $show->disableEditButton();
            $show->disableDeleteButton();
        });
    }
}
