<?php

declare(strict_types=1);

namespace Modules\FeatureSsh\DcatAdmin\Controllers;

use Dcat\Admin\Grid;
use Modules\DcatAdmin\DcatAdmin\AdminController;
use Modules\FeatureSsh\Enums\CommandStatus;
use Modules\FeatureSsh\Models\CommandLog;
use Modules\FeatureSsh\Models\Server;

/**
 * SSH命令执行日志控制器
 *
 * @author AI开发团队
 * @date 2026-09-02
 */
class CommandLogController extends AdminController
{
    /**
     * 页面标题
     */
    protected $title = '命令执行日志';

    /**
     * 页面描述
     */
    protected $description = '查看SSH命令执行历史记录，仅支持查看，不支持编辑和删除';

    /**
     * Make a grid builder.
     */
    protected function grid(): Grid
    {
        return Grid::make(CommandLog::with('server'), function (Grid $grid) {
            $grid->column('id', 'ID')->sortable()->width('60px');
            $grid->column('server.name', '服务器')
                ->width('150px')
                ->display(function ($value) {
                    return $value ?: '-';
                });
            $grid->column('command', '执行命令')
                ->width('300px')
                ->display(function ($value) {
                    $maxLength = 60;
                    if (strlen($value) > $maxLength) {
                        return '<span title="' . htmlspecialchars($value) . '">' . substr($value, 0, $maxLength) . '...</span>';
                    }
                    return $value;
                });
            $grid->column('exit_code', '退出码')
                ->width('80px')
                ->label(function ($value) {
                    if ($value === 0) {
                        return 'success';
                    }
                    return 'danger';
                });
            $grid->column('status', '状态')
                ->width('100px')
                ->using(CommandStatus::options())
                ->dot([
                    'success' => 'success',
                    'failed' => 'danger',
                    'timeout' => 'warning',
                ], 'success');
            $grid->column('execution_time', '执行时间')
                ->width('120px')
                ->display(function ($value) {
                    return $value . ' ms';
                });
            $grid->column('executed_at', '执行时间')
                ->width('150px');

            $grid->disableActions();
            $grid->disableBatchActions();
            $grid->disableCreateButton();
            $grid->disableEditButton();
            $grid->disableDeleteButton();

            $grid->filter(function (Grid\Filter $filter) {
                $filter->panel();
                $filter->expand(false);

                $filter->equal('server_id', '服务器')
                    ->select(function () {
                        return Server::pluck('name', 'id')->toArray();
                    });
                $filter->like('command', '命令内容');
                $filter->equal('status', '状态')
                    ->select(CommandStatus::options());
                $filter->equal('exit_code', '退出码');
                $filter->between('executed_at', '执行时间')
                    ->datetime();
            });

            $grid->tools(function (Grid\Tools $tools) {
                $tools->append('<div class="alert alert-info m-0">
                    <i class="feather icon-info"></i>
                    提示：命令日志仅用于审计和查看，不支持编辑和删除操作
                </div>');
            });
        });
    }

    /**
     * 重写编辑方法，禁止编辑日志
     *
     * @param int $id
     * @return mixed
     */
    public function edit(int $id)
    {
        return back()->with('error', '命令执行日志不支持编辑');
    }

    /**
     * 重写删除方法，禁止删除日志
     *
     * @param int $id
     * @return mixed
     */
    public function destroy(int $id)
    {
        return response()->json([
            'status' => false,
            'message' => '命令执行日志不支持删除',
        ]);
    }
}
