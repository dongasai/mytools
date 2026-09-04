<?php

namespace Modules\Demo5\DcatAdmin\Controllers;

use Dcat\Admin\Http\Controllers\AdminController;
use Dcat\Admin\Layout\Content;
use Dcat\Admin\Layout\Row;
// Metrics导入
use Modules\Demo5\DcatAdmin\Metrics\PostStatus;
use Modules\Demo5\DcatAdmin\Metrics\CommentStatus;
use Modules\Demo5\DcatAdmin\Metrics\CommentTrend;
use Modules\Demo5\DcatAdmin\Metrics\UserActivity;
use Modules\Demo5\DcatAdmin\Metrics\RecentPosts;

/**
 * Demo5模块统计仪表盘控制器
 *
 * 专门负责文章、评论、用户相关的统计图表和数据展示
 */
class DashboardController extends AdminController
{
    /**
     * 统计仪表盘首页
     */
    public function index(Content $content)
    {
        return $content
            ->title('Demo5 统计仪表盘')
            ->description('博客系统数据统计与分析')
            ->body(function (Row $row) {
                // 第一行：文章状态统计和评论状态统计
                $row->column(6, new PostStatus());
                $row->column(6, new CommentStatus());

                // 第二行：评论趋势（占据8列）和用户活动（占据4列）
                $row->column(8, new CommentTrend());
                $row->column(4, new UserActivity());

                // 第三行：最新文章列表
                $row->column(12, new RecentPosts());
            });
    }
}