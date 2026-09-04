<?php

namespace Modules\Application\DcatAdmin\Controllers;

use Dcat\Admin\Grid;
use Dcat\Admin\Show;
use Dcat\Admin\Http\Controllers\AdminController;
use Modules\Application\DcatAdmin\Repositories\RequestLog;

/**
 * 请求日志控制器
 */
class RequestLogController extends AdminController
{
    protected $title = '请求日志';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        return Grid::make(new RequestLog(), function (Grid $grid) {

            $grid->model()->orderBy('id', 'desc');

            $grid->column('id', 'ID')->sortable();
            $grid->column('unid', '请求ID')->display(function ($value) {
                return $value;
            })->copyable();
            $grid->column('method', '方法')->label([
                'GET' => 'info',
                'POST' => 'success',
                'PUT' => 'warning',
                'DELETE' => 'danger',
            ]);
            $grid->column('path', '路径')->limit(30);
            $grid->column('module', '模块')->label();
            $grid->column('user_id', '用户ID');
            $grid->column('ipaddress', 'IP地址');
            $grid->column('run_ms', '耗时(ms)')->display(function ($value) {
                if ($value > 1000) {
                    return "<span class='text-danger'>{$value}</span>";
                } elseif ($value > 500) {
                    return "<span class='text-warning'>{$value}</span>";
                }
                return $value;
            });
            $grid->column('response_status', '状态码')->display(function ($value) {
                if ($value >= 400) {
                    return "<span class='text-danger'>{$value}</span>";
                }
                return "<span class='text-success'>{$value}</span>";
            });
            $grid->column('created_at', '创建时间')->sortable();

            // 筛选
            $grid->filter(function (Grid\Filter $filter) {
                $filter->equal('unid', '请求ID');
                $filter->equal('user_id', '用户ID');
                $filter->equal('module', '模块');
                $filter->equal('method', '请求方法')->select([
                    'GET' => 'GET',
                    'POST' => 'POST',
                    'PUT' => 'PUT',
                    'DELETE' => 'DELETE',
                ]);
                $filter->like('path', '路径');
                $filter->like('ipaddress', 'IP地址');
                $filter->between('run_ms', '耗时(ms)');
                $filter->between('created_at', '创建时间')->datetime();
                $filter->expand();
                $filter->panel();
            });

            // 禁用创建按钮
            $grid->disableCreateButton();
            $grid->disableEditButton();
            $grid->disableDeleteButton();

            // 快速搜索
            $grid->quickSearch(['id', 'unid', 'path', 'ipaddress']);

            // 数据统计
            $grid->header(function ($query) {
                $total = $query->count();
                $avgRunMs = $query->avg('run_ms');
                $errorCount = $query->where('response_status', '>=', 400)->count();

                return "
                <div class='alert alert-info'>
                    <strong>统计信息:</strong>
                    总计: {$total} 条 |
                    平均耗时: " . round($avgRunMs, 2) . "ms |
                    错误请求: {$errorCount} 条
                </div>
                ";
            });
        });
    }

    /**
     * Make a show builder.
     *
     * @param mixed $id
     * @return Show
     */
    protected function detail($id)
    {
        return Show::make($id, new RequestLog(), function (Show $show) {
            $show->id('ID');
            $show->field('unid', '请求ID');
            $show->field('request_unid', '请求UNID');
            $show->field('run_unid', '运行UNID');
            $show->field('method', '请求方法');
            $show->field('path', '请求路径');
            $show->field('router', '路由');
            $show->field('module', '模块');
            $show->field('ipaddress', 'IP地址');
            $show->field('host', '主机');
            $show->field('user_agent', 'User Agent')->unescape()->as(function ($value) {
                return "<small>{$value}</small>";
            });
            $show->field('user_id', '用户ID');
            $show->field('token', 'Token');
            $show->field('headers', '请求头')->unescape()->as(function ($value) {
                $data = json_decode($value, true);
                if (!$data) return $value;
                $html = '<table class="table table-sm table-bordered">';
                foreach ($data as $key => $val) {
                    $valStr = is_array($val) ? implode(', ', $val) : $val;
                    $html .= "<tr><th width='200'>{$key}</th><td>{$valStr}</td></tr>";
                }
                $html .= '</table>';
                return $html;
            });
            $show->field('query', 'Query参数')->unescape()->as(function ($value) {
                $data = json_decode($value, true);
                if (!$data) return '-';
                $html = '<table class="table table-sm table-bordered">';
                foreach ($data as $key => $val) {
                    $valStr = is_array($val) ? json_encode($val, JSON_UNESCAPED_UNICODE) : $val;
                    $html .= "<tr><th width='200'>{$key}</th><td>{$valStr}</td></tr>";
                }
                $html .= '</table>';
                return $html;
            });
            $show->field('post', 'POST数据')->unescape()->as(function ($value) {
                if (!$value) return '-';
                $data = json_decode($value, true);
                if (!$data) return $value;
                $json = json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
                return "<pre class='bg-light p-3'><code>{$json}</code></pre>";
            });
            $show->field('files', '上传文件')->unescape()->as(function ($value) {
                $data = json_decode($value, true);
                if (!$data) return '-';
                $html = '<ul>';
                foreach ($data as $file) {
                    $html .= "<li>{$file['name']} ({$file['size']} bytes, {$file['type']})</li>";
                }
                $html .= '</ul>';
                return $html;
            });
            $show->field('response_status', '响应状态码');
            $show->field('response_type', '响应类型');
            $show->field('response_size', '响应大小(字节)')->as(function ($value) {
                return number_format($value);
            });
            $show->field('run_ms', '耗时(ms)');
            $show->field('sql_num', 'SQL查询次数');
            $show->field('response', '响应内容')->unescape()->as(function ($value) {
                if (!$value) return '-';
                $truncated = $this->response_truncated ? ' <span class="badge badge-warning">已截断</span>' : '';
                return "<pre class='bg-light p-3' style='max-height: 400px; overflow-y: auto;'><code>{$value}</code></pre>{$truncated}";
            });
            $show->field('error', '错误信息');
            $show->field('created_at', '创建时间');
            $show->field('updated_at', '更新时间');

            // 禁用按钮
            $show->disableEditButton();
            $show->disableDeleteButton();

            // 设置字段宽度
            $show->width('id', 2);
            $show->width('unid', 3);
            $show->width('method', 2);
            $show->width('user_id', 2);
            $show->width('ipaddress', 2);
        });
    }
}