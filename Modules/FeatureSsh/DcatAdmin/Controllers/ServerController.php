<?php

declare(strict_types=1);

namespace Modules\FeatureSsh\DcatAdmin\Controllers;

use Dcat\Admin\Admin;
use Dcat\Admin\Form;
use Dcat\Admin\Grid;
use Dcat\Admin\Layout\Content;
use Dcat\Admin\Show;
use Dcat\Admin\Widgets\Table;
use Modules\DcatAdmin\DcatAdmin\AdminController;
use Modules\FeatureSsh\Enums\ServerStatus;
use Modules\FeatureSsh\Enums\SystemType;
use Modules\FeatureSsh\Models\Server;
use Modules\FeatureSsh\Models\ServerGroup;
use Modules\FeatureSsh\Services\SshConnectionService;

/**
 * SSH服务器管理控制器
 *
 * @author AI开发团队
 * @date 2026-09-02
 */
class ServerController extends AdminController
{
    /**
     * 页面标题
     */
    protected $title = 'SSH服务器管理';

    /**
     * 页面描述
     */
    protected $description = '管理SSH服务器信息，支持连接测试和状态监控';

    /**
     * Make a grid builder.
     */
    protected function grid(): Grid
    {
        return Grid::make(Server::with('group'), function (Grid $grid) {
            $grid->column('id', 'ID')->sortable()->width('60px');
            $grid->column('name', '服务器名称')->width('150px');
            $grid->column('group.name', '分组')
                ->width('120px')
                ->display(function ($value) {
                    return $value ?: '-';
                });
            $grid->column('host', '主机:端口')
                ->width('180px')
                ->display(function () {
                    return $this->host . ':' . $this->port;
                });
            $grid->column('system_type', '系统类型')
                ->width('100px')
                ->using(SystemType::options());
            $grid->column('status', '状态')
                ->width('100px')
                ->using(ServerStatus::options())
                ->dot([
                    'active' => 'success',
                    'inactive' => 'warning',
                    'offline' => 'danger',
                ], 'active');
            $grid->column('last_check_at', '最后检测')
                ->width('150px')
                ->display(function ($value) {
                    return $value ?: '从未检测';
                });
            $grid->column('description', '描述')
                ->width('200px')
                ->display(function ($value) {
                    return $value ?: '-';
                });

            $grid->enableBatchActions();
            $grid->disableViewButton();

            $grid->filter(function (Grid\Filter $filter) {
                $filter->panel();
                $filter->expand(false);

                $filter->like('name', '服务器名称');
                $filter->equal('group_id', '分组')
                    ->select(function () {
                        return ServerGroup::pluck('name', 'id')->toArray();
                    });
                $filter->equal('system_type', '系统类型')
                    ->select(SystemType::options());
                $filter->equal('status', '状态')
                    ->select(ServerStatus::options());
            });

            $grid->actions(function (Grid\Displayers\Actions $actions) {
                $actions->prepend(
                    '<a href="' . admin_route('featuressh.servers.test', ['id' => $actions->getKey()]) . '" class="btn btn-sm btn-primary" title="测试连接">
                        <i class="feather icon-wifi"></i>
                    </a>&nbsp;'
                );
            });

            $grid->header(function () {
                return '<div class="alert alert-info">
                    <i class="feather icon-info"></i>
                    提示：点击"测试连接"按钮可以验证服务器连接是否正常
                </div>';
            });
        });
    }

    /**
     * Make a show builder.
     *
     * @param mixed $id
     */
    protected function detail($id): Show
    {
        return Show::make($id, Server::with('group', 'authentications'), function (Show $show) {
            $show->field('id', 'ID');
            $show->field('name', '服务器名称');
            $show->field('group.name', '分组');
            $show->field('host', '主机地址');
            $show->field('port', 'SSH端口');
            $show->field('system_type', '系统类型')
                ->as(function ($type) {
                    return $type->label();
                });
            $show->field('system_info', '系统信息')
                ->json();
            $show->field('status', '状态')
                ->as(function ($status) {
                    return $status->label();
                });
            $show->field('last_check_at', '最后检测时间');
            $show->field('description', '描述');
            $show->field('created_at', '创建时间');
            $show->field('updated_at', '更新时间');
        });
    }

    /**
     * Make a form builder.
     */
    protected function form(): Form
    {
        return Form::make(Server::class, function (Form $form) {
            $form->display('id', 'ID');

            $form->text('name', '服务器名称')
                ->required()
                ->rules('required|string|max:100')
                ->help('为服务器设置一个有意义的名称');

            $form->select('group_id', '分组')
                ->options(function () {
                    return ServerGroup::pluck('name', 'id')->toArray();
                })
                ->placeholder('选择分组');

            $form->divider('网络配置');

            $form->text('host', '主机地址')
                ->required()
                ->rules('required|string|max:255')
                ->help('可以是IP地址或域名');

            $form->number('port', 'SSH端口')
                ->required()
                ->default(22)
                ->min(1)
                ->max(65535)
                ->help('默认SSH端口为22');

            $form->select('system_type', '系统类型')
                ->required()
                ->options(SystemType::options())
                ->default('linux')
                ->help('选择服务器的操作系统类型');

            $form->textarea('description', '描述')
                ->rules('max:500')
                ->help('服务器描述信息（可选）');

            $form->display('created_at', '创建时间');
            $form->display('updated_at', '更新时间');

            $form->saving(function (Form $form) {
                if (!$form->status) {
                    $form->status = ServerStatus::ACTIVE;
                }
            });
        });
    }

    /**
     * 测试服务器连接
     *
     * @param Content $content
     * @param int $id
     * @return Content
     */
    public function test(Content $content, int $id): Content
    {
        $server = Server::find($id);

        if (!$server) {
            return $content
                ->title('测试连接')
                ->body('<div class="alert alert-danger">服务器不存在</div>');
        }

        $result = [
            'server_name' => $server->name,
            'host' => $server->host,
            'port' => $server->port,
            'success' => false,
            'message' => '',
            'latency' => 0,
            'system_info' => [],
        ];

        try {
            $testResult = SshConnectionService::testConnection($id);
            $result['success'] = $testResult['success'];
            $result['message'] = $testResult['message'];
            $result['latency'] = $testResult['latency'];
            $result['system_info'] = $testResult['system_info'] ?? [];

            if ($testResult['success']) {
                $server->updateSystemInfo($result['system_info']);
                $server->status = ServerStatus::ACTIVE;
                $server->save();
            } else {
                $server->status = ServerStatus::OFFLINE;
                $server->save();
            }
        } catch (\Exception $e) {
            $result['message'] = $e->getMessage();
            $server->status = ServerStatus::OFFLINE;
            $server->save();
        }

        $alertClass = $result['success'] ? 'alert-success' : 'alert-danger';
        $icon = $result['success'] ? 'icon-check' : 'icon-x';

        $body = '<div class="alert ' . $alertClass . '">
            <i class="feather ' . $icon . '"></i>
            <strong>' . ($result['success'] ? '连接成功' : '连接失败') . '</strong>
            <p>' . $result['message'] . '</p>
        </div>';

        if ($result['success']) {
            $body .= '<div class="card">
                <div class="card-header">连接详情</div>
                <div class="card-body">
                    <p><strong>延迟：</strong>' . $result['latency'] . ' ms</p>';

            if (!empty($result['system_info'])) {
                $body .= '<p><strong>系统信息：</strong></p>';
                $body .= '<table class="table table-bordered">';
                foreach ($result['system_info'] as $key => $value) {
                    if (is_array($value)) {
                        continue;
                    }
                    $body .= '<tr><td>' . $key . '</td><td>' . $value . '</td></tr>';
                }
                $body .= '</table>';
            }

            $body .= '</div></div>';
        }

        $body .= '<div class="mt-3">
            <a href="' . admin_route('featuressh.servers.index') . '" class="btn btn-primary">返回列表</a>
        </div>';

        return $content
            ->title('测试连接 - ' . $server->name)
            ->body($body);
    }
}
