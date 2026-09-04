<?php

declare(strict_types=1);

namespace Modules\Notification\DcatAdmin\Grids;

use Dcat\Admin\Grid;
use Modules\Notification\Enums\NOTIFICATION_STATUS;

/**
 * 通知日志表格类.
 *
 * 用于构建通知日志的数据表格展示，包含日志列表的所有列配置、过滤器和操作按钮。
 * 通知日志为只读数据，禁用创建和编辑功能。
 *
 * @package Modules\Notification\DcatAdmin\Grids
 */
class NotificationLogGrid
{
    /**
     * 构建通知日志表格.
     *
     * @param string $model 模型类名
     * @return Grid
     */
    public static function make(string $model): Grid
    {
        return Grid::make($model, function (Grid $grid) {
            // 列配置
            $grid->column('id', 'ID')->sortable();
            $grid->column('notification_type', '通知类型');
            $grid->column('channel', '渠道');
            $grid->column('notifiable_type', '接收对象类型');
            $grid->column('notifiable_id', '接收对象ID');
            $grid->column('status', '状态')->using([
                NOTIFICATION_STATUS::PENDING->value => '待发送',
                NOTIFICATION_STATUS::SENDING->value => '发送中',
                NOTIFICATION_STATUS::SENT->value => '已发送',
                NOTIFICATION_STATUS::FAILED->value => '发送失败',
            ])->label([
                NOTIFICATION_STATUS::PENDING->value => 'default',
                NOTIFICATION_STATUS::SENDING->value => 'info',
                NOTIFICATION_STATUS::SENT->value => 'success',
                NOTIFICATION_STATUS::FAILED->value => 'danger',
            ]);
            $grid->column('sent_at', '发送时间');
            $grid->column('retry_count', '重试次数');
            $grid->column('created_at', '创建时间')->sortable();

            // 过滤器配置
            $grid->filter(function (Grid\Filter $filter) {
                $filter->equal('id', 'ID');
                $filter->equal('notification_type', '通知类型');
                $filter->equal('channel', '渠道');
                $filter->equal('status', '状态')->select([
                    NOTIFICATION_STATUS::PENDING->value => '待发送',
                    NOTIFICATION_STATUS::SENDING->value => '发送中',
                    NOTIFICATION_STATUS::SENT->value => '已发送',
                    NOTIFICATION_STATUS::FAILED->value => '发送失败',
                ]);
                $filter->between('created_at', '创建时间')->datetime();
            });

            // 禁用按钮（日志只读）
            $grid->disableCreateButton();
            $grid->disableEditButton();
            $grid->disableViewButton();

            // 默认排序
            $grid->model()->orderBy('id', 'desc');
        });
    }
}