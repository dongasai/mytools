<?php

namespace Modules\DcatAdmin\DcatAdmin\Controllers;

use Dcat\Admin\Layout\Content;
use Dcat\Admin\Layout\Row;
use Modules\DcatAdmin\DcatAdmin\Widgets\QuickActionsWidget;
use Modules\DcatAdmin\DcatAdmin\Widgets\StatisticsWidget;
use Modules\DcatAdmin\DcatAdmin\Widgets\SystemStatusWidget;
use Modules\DcatAdmin\Services\AdminService;
use Modules\DcatAdmin\DcatAdmin\AdminController;

/**
 * 仪表板控制器
 */
class DashboardController extends AdminController
{
    protected $title = '仪表板';

    /**
     * 仪表板首页
     */
    public function index(Content $content): Content
    {
        return $content
            ->title($this->title)
            ->description('系统概览')
            ->body($this->buildDashboard());
    }

    /**
     * 构建仪表板内容
     */
    protected function buildDashboard(): Row
    {

        return new Row(function (Row $row) {
            $adminService = new AdminService;
            $dashboardData = $adminService->getDashboardData();

            // 第一行：统计卡片
            $row->column(12, function ($column) use ($dashboardData) {
                $column->append(new StatisticsWidget($dashboardData['statistics']));
            });

            // 第二行：系统状态和快捷操作
            $row->column(8, function ($column) use ($dashboardData) {
                $column->append(new SystemStatusWidget($dashboardData['system']));
            });

            $row->column(4, function ($column) {
                $column->append(new QuickActionsWidget);
            });

            // 第三行：系统警报（如果有）
            if (! empty($dashboardData['alerts'])) {
                $row->column(12, function ($column) use ($dashboardData) {
                    $column->append($this->buildAlertsWidget($dashboardData['alerts']));
                });
            }

            // 第四行：最近操作
            if (! empty($dashboardData['recent_actions'])) {
                $row->column(12, function ($column) use ($dashboardData) {
                    $column->append($this->buildRecentActionsWidget($dashboardData['recent_actions']));
                });
            }
        });
    }

    /**
     * 构建警报小部件
     */
    protected function buildAlertsWidget(array $alerts): string
    {
        $html = '<div class="card">';
        $html .= '<div class="card-header"><h4 class="card-title">系统警报</h4></div>';
        $html .= '<div class="card-body">';

        foreach ($alerts as $alert) {
            $type = $alert['type'];
            $title = $alert['title'];
            $message = $alert['message'];
            $timestamp = $alert['timestamp']->format('Y-m-d H:i:s');

            $html .= "<div class=\"alert alert-{$type}\">";
            $html .= "<strong>{$title}</strong><br>";
            $html .= "{$message}<br>";
            $html .= "<small class=\"text-muted\">{$timestamp}</small>";
            $html .= '</div>';
        }

        $html .= '</div></div>';

        return $html;
    }

    /**
     * 构建最近操作小部件
     */
    protected function buildRecentActionsWidget(array $actions): string
    {
        $html = '<div class="card">';
        $html .= '<div class="card-header"><h4 class="card-title">最近操作</h4></div>';
        $html .= '<div class="card-body">';
        $html .= '<div class="table-responsive">';
        $html .= '<table class="table table-sm">';
        $html .= '<thead>';
        $html .= '<tr>';
        $html .= '<th>操作</th>';
        $html .= '<th>管理员</th>';
        $html .= '<th>时间</th>';
        $html .= '<th>IP地址</th>';
        $html .= '</tr>';
        $html .= '</thead>';
        $html .= '<tbody>';

        foreach ($actions as $action) {
            $html .= '<tr>';
            $html .= "<td>{$action['description']}</td>";
            $html .= "<td>{$action['admin_name']}</td>";
            $html .= "<td>{$action['timestamp']}</td>";
            $html .= "<td>{$action['ip_address']}</td>";
            $html .= '</tr>';
        }

        $html .= '</tbody>';
        $html .= '</table>';
        $html .= '</div>';
        $html .= '</div></div>';

        return $html;
    }

    /**
     * 获取系统状态API
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getSystemStatus()
    {
        try {
            $status = $this->adminService->getSystemStatus();

            return response()->json([
                'status' => 'success',
                'data' => $status,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * 刷新仪表板数据API
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function refreshDashboard()
    {
        try {
            // 清理仪表板缓存
            cache()->forget('admin.dashboard.data');

            // 获取新数据
            $data = $this->adminService->getDashboardData();

            return response()->json([
                'status' => 'success',
                'message' => '仪表板数据已刷新',
                'data' => $data,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}
