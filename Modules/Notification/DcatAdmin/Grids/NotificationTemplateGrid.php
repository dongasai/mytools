<?php

declare(strict_types=1);

namespace Modules\Notification\DcatAdmin\Grids;

use Dcat\Admin\Grid;

/**
 * 通知模板表格类.
 *
 * 用于构建通知模板的数据表格展示，包含模板列表的所有列配置和过滤器。
 * 支持模板的启用状态切换和快速筛选。
 *
 * @package Modules\Notification\DcatAdmin\Grids
 */
class NotificationTemplateGrid
{
    /**
     * 构建通知模板表格.
     *
     * @param string $model 模型类名
     * @return Grid
     */
    public static function make(string $model): Grid
    {
        return Grid::make($model, function (Grid $grid) {
            // 列配置
            $grid->column('id', 'ID')->sortable();
            $grid->column('name', '模板名称');
            $grid->column('notification_type', '通知类型');
            $grid->column('channel', '适用渠道');
            $grid->column('subject', '标题');
            $grid->column('content', '内容')->limit(50);
            $grid->column('is_active', '启用状态')->switch();
            $grid->column('created_at', '创建时间')->sortable();

            // 过滤器配置
            $grid->filter(function (Grid\Filter $filter) {
                $filter->equal('id', 'ID');
                $filter->like('name', '模板名称');
                $filter->equal('notification_type', '通知类型');
                $filter->equal('channel', '适用渠道');
                $filter->equal('is_active', '启用状态')->select([
                    0 => '禁用',
                    1 => '启用',
                ]);
            });

            // 默认排序
            $grid->model()->orderBy('id', 'desc');
        });
    }
}