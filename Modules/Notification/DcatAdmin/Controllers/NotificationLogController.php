<?php

declare(strict_types=1);

namespace Modules\Notification\DcatAdmin\Controllers;

use Dcat\Admin\Http\Controllers\AdminController;
use Dcat\Admin\Grid;
use Dcat\Admin\Show;
use Modules\Notification\DcatAdmin\Grids\NotificationLogGrid;
use Modules\Notification\Models\NotificationLog;

/**
 * 通知日志后台控制器.
 *
 * 用于管理通知日志的后台控制器，提供日志列表展示和详情查看功能。
 * 通知日志为只读数据，不支持创建和编辑操作。
 *
 * @package Modules\Notification\DcatAdmin\Controllers
 */
class NotificationLogController extends AdminController
{
    /**
     * 模型类名.
     *
     * @var string
     */
    protected $model = NotificationLog::class;

    /**
     * 构建通知日志列表表格.
     *
     * @return Grid
     */
    public function grid(): Grid
    {
        return NotificationLogGrid::make($this->model);
    }

    /**
     * 显示通知日志详情.
     *
     * @param int $id 日志ID
     * @return Show
     */
    public function detail(int $id): Show
    {
        return Show::make($id, $this->model, function (Show $show) {
            $show->id('ID');
            $show->field('notification_type', '通知类型');
            $show->field('channel', '通知渠道');
            $show->field('notifiable_type', '接收对象类型');
            $show->field('notifiable_id', '接收对象ID');
            $show->field('status', '发送状态');
            $show->field('data', '通知数据')->json();
            $show->field('error_message', '错误信息');
            $show->field('sent_at', '发送时间');
            $show->field('retry_count', '重试次数');
            $show->field('created_at', '创建时间');
            $show->field('updated_at', '更新时间');
        });
    }
}