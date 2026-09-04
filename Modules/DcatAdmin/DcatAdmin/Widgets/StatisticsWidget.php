<?php

namespace Modules\DcatAdmin\DcatAdmin\Widgets;

use Dcat\Admin\Widgets\Widget;

/**
 * 统计信息小部件
 */
class StatisticsWidget extends Widget
{
    protected array $statistics;

    public function __construct(array $statistics)
    {
        $this->statistics = $statistics;
    }

    /**
     * 渲染小部件
     */
    public function render(): string
    {
        $stats = [
            [
                'title' => '在线用户',
                'value' => $this->statistics['users_online'] ?? 0,
                'icon' => 'fa-users',
                'color' => 'primary',
                'description' => '当前在线用户数量',
            ],
            [
                'title' => '总请求数',
                'value' => number_format($this->statistics['total_requests'] ?? 0),
                'icon' => 'fa-chart-bar',
                'color' => 'success',
                'description' => '今日总请求数量',
            ],
            [
                'title' => '错误率',
                'value' => number_format($this->statistics['error_rate'] ?? 0, 2).'%',
                'icon' => 'fa-exclamation-triangle',
                'color' => $this->getErrorRateColor($this->statistics['error_rate'] ?? 0),
                'description' => '今日错误率',
            ],
            [
                'title' => '响应时间',
                'value' => number_format($this->statistics['response_time'] ?? 0, 0).'ms',
                'icon' => 'fa-clock',
                'color' => $this->getResponseTimeColor($this->statistics['response_time'] ?? 0),
                'description' => '平均响应时间',
            ],
        ];

        $html = '<div class="row">';

        foreach ($stats as $stat) {
            $html .= $this->buildStatCard($stat);
        }

        $html .= '</div>';

        return $html;
    }

    /**
     * 构建统计卡片
     */
    protected function buildStatCard(array $stat): string
    {
        $html = '<div class="col-lg-3 col-md-6 col-sm-6 col-12">';
        $html .= '<div class="card card-statistic-1">';
        $html .= '<div class="card-icon bg-'.$stat['color'].'">';
        $html .= '<i class="fa '.$stat['icon'].'"></i>';
        $html .= '</div>';
        $html .= '<div class="card-wrap">';
        $html .= '<div class="card-header">';
        $html .= '<h4>'.$stat['title'].'</h4>';
        $html .= '</div>';
        $html .= '<div class="card-body">';
        $html .= '<div class="d-flex justify-content-between align-items-center">';
        $html .= '<div>';
        $html .= '<h2 class="mb-0">'.$stat['value'].'</h2>';
        $html .= '<small class="text-muted">'.$stat['description'].'</small>';
        $html .= '</div>';
        $html .= '</div>';
        $html .= '</div>';
        $html .= '</div>';
        $html .= '</div>';
        $html .= '</div>';

        return $html;
    }

    /**
     * 根据错误率获取颜色
     */
    protected function getErrorRateColor(float $errorRate): string
    {
        if ($errorRate < 1) {
            return 'success';
        } elseif ($errorRate < 5) {
            return 'warning';
        } else {
            return 'danger';
        }
    }

    /**
     * 根据响应时间获取颜色
     */
    protected function getResponseTimeColor(float $responseTime): string
    {
        if ($responseTime < 500) {
            return 'success';
        } elseif ($responseTime < 1000) {
            return 'warning';
        } else {
            return 'danger';
        }
    }
}
