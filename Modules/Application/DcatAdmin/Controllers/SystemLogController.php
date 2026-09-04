<?php

namespace Modules\Application\DcatAdmin\Controllers;

use Dcat\Admin\Grid;
use Dcat\Admin\Show;
use Modules\Application\Models\SystemLog;
use Modules\DcatAdmin\DcatAdmin\AdminController;
use Modules\DcatAdmin\DcatAdmin\FilterHelper;
use Modules\DcatAdmin\DcatAdmin\GridHelper;

/**
 * 系统日志管理控制器 - 只读
 */
class SystemLogController extends AdminController
{
    /**
     * 页面标题
     *
     * @var string
     */
    protected $title = '系统日志';

    /**
     * 列表页面
     *
     * @return Grid
     */
    protected function grid()
    {
        return Grid::make(new SystemLog, function (Grid $grid) {
            $helper = new GridHelper($grid, $this);

            // 按ID倒序排列，显示最新的日志
            $grid->model()->orderByDesc('id');

            $helper->columnId();
            $grid->column('level1', '来源类型')->label('primary');
            $grid->column('message', '日志消息')->limit(80);
            $grid->column('data1', '数据')->display(function ($value) {
                if (empty($value)) {
                    return '-';
                }
                // 尝试解析JSON数据
                $decoded = json_decode($value, true);
                if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                    return json_encode($decoded, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
                }

                return $value;
            })->limit(50);
            $helper->columnCreatedAt();

            // 禁用创建按钮
            $grid->disableCreateButton();

            // 禁用编辑和删除按钮
            $grid->actions(function (Grid\Displayers\Actions $actions) {
                $actions->disableEdit();
                $actions->disableDelete();
            });

            // 筛选器
            $grid->filter(function (Grid\Filter $filter) {
                $helper = new FilterHelper($filter, $this);
                $helper->equalId();
                $filter->equal('level1', '来源类型');
                $filter->like('message', '日志消息');
                $helper->betweenDatetime('created_at', '创建时间');
            });
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
        return Show::make($id, new SystemLog, function (Show $show) {
            $show->field('id', 'ID');
            $show->field('level1', '来源类型');
            $show->field('message', '日志消息');
            $show->field('data1', '数据')->as(function ($value) {
                if (empty($value)) {
                    return '-';
                }
                // 尝试解析JSON数据并格式化显示
                $decoded = json_decode($value, true);
                if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                    return '<pre>'.json_encode($decoded, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT).'</pre>';
                }

                return $value;
            });
            $show->field('created_at', '创建时间');
        });
    }
}
