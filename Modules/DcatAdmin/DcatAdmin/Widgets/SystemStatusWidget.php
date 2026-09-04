<?php

namespace Modules\DcatAdmin\DcatAdmin\Widgets;

use Dcat\Admin\Widgets\Card;

/**
 * 系统状态小部件
 */
class SystemStatusWidget extends Card
{
    protected array $systemData;

    public function __construct(array $systemData)
    {
        $this->systemData = $systemData;
        parent::__construct('系统状态', $this->buildContent());
    }

    /**
     * 构建内容
     */
    protected function buildContent(): string
    {
        $html = '<div class="row">';

        // 服务器信息
        $html .= $this->buildServerInfo();

        // 性能指标
        $html .= $this->buildPerformanceMetrics();

        $html .= '</div>';

        return $html;
    }

    /**
     * 构建服务器信息
     */
    protected function buildServerInfo(): string
    {
        $serverInfo = $this->systemData['server_info'] ?? [];

        $html = '<div class="col-md-6">';
        $html .= '<h6>服务器信息</h6>';
        $html .= '<table class="table table-sm table-borderless">';

        $items = [
            '操作系统' => $serverInfo['os'] ?? 'Unknown',
            '主机名' => $serverInfo['hostname'] ?? 'Unknown',
            '架构' => $serverInfo['architecture'] ?? 'Unknown',
            'Web服务器' => $serverInfo['server_software'] ?? 'Unknown',
        ];

        foreach ($items as $label => $value) {
            $html .= "<tr><td class=\"text-muted\">{$label}:</td><td>{$value}</td></tr>";
        }

        $html .= '</table>';
        $html .= '</div>';

        return $html;
    }

    /**
     * 构建性能指标
     */
    protected function buildPerformanceMetrics(): string
    {
        $performance = $this->systemData['performance'] ?? [];

        $html = '<div class="col-md-6">';
        $html .= '<h6>性能指标</h6>';

        // 内存使用率
        if (isset($performance['memory'])) {
            $memory = $performance['memory'];
            $percentage = $memory['percentage'];
            $progressClass = $this->getProgressClass($percentage);

            $html .= '<div class="mb-2">';
            $html .= '<small class="text-muted">内存使用率</small>';
            $html .= '<div class="progress" style="height: 8px;">';
            $html .= "<div class=\"progress-bar bg-{$progressClass}\" style=\"width: {$percentage}%\"></div>";
            $html .= '</div>';
            $html .= "<small>{$memory['current']} / {$memory['limit']} ({$percentage}%)</small>";
            $html .= '</div>';
        }

        // 磁盘使用率
        if (isset($performance['disk'])) {
            $disk = $performance['disk'];
            $percentage = $disk['percentage'];
            $progressClass = $this->getProgressClass($percentage);

            $html .= '<div class="mb-2">';
            $html .= '<small class="text-muted">磁盘使用率</small>';
            $html .= '<div class="progress" style="height: 8px;">';
            $html .= "<div class=\"progress-bar bg-{$progressClass}\" style=\"width: {$percentage}%\"></div>";
            $html .= '</div>';
            $html .= "<small>{$disk['used']} / {$disk['total']} ({$percentage}%)</small>";
            $html .= '</div>';
        }

        // CPU使用率
        if (isset($performance['cpu_usage'])) {
            $cpuUsage = $performance['cpu_usage'];
            $progressClass = $this->getProgressClass($cpuUsage);

            $html .= '<div class="mb-2">';
            $html .= '<small class="text-muted">CPU使用率</small>';
            $html .= '<div class="progress" style="height: 8px;">';
            $html .= "<div class=\"progress-bar bg-{$progressClass}\" style=\"width: {$cpuUsage}%\"></div>";
            $html .= '</div>';
            $html .= "<small>{$cpuUsage}%</small>";
            $html .= '</div>';
        }

        // 负载平均值
        if (isset($performance['load_average'])) {
            $load = $performance['load_average'];
            $html .= '<div class="mb-2">';
            $html .= '<small class="text-muted">负载平均值</small><br>';
            $html .= "<small>1分钟: {$load['1min']} | 5分钟: {$load['5min']} | 15分钟: {$load['15min']}</small>";
            $html .= '</div>';
        }

        // 数据库状态
        if (isset($performance['database'])) {
            $database = $performance['database'];
            $statusClass = $database['status'] === 'healthy' ? 'success' : 'danger';
            $statusText = $database['status'] === 'healthy' ? '正常' : '异常';

            $html .= '<div class="mb-2">';
            $html .= '<small class="text-muted">数据库状态</small><br>';
            $html .= "<span class=\"badge badge-{$statusClass}\">{$statusText}</span>";

            if (isset($database['response_time'])) {
                $html .= " <small class=\"text-muted\">响应时间: {$database['response_time']}</small>";
            }

            $html .= '</div>';
        }

        $html .= '</div>';

        return $html;
    }

    /**
     * 根据百分比获取进度条颜色类
     */
    protected function getProgressClass(float $percentage): string
    {
        if ($percentage < 50) {
            return 'success';
        } elseif ($percentage < 80) {
            return 'warning';
        } else {
            return 'danger';
        }
    }
}
