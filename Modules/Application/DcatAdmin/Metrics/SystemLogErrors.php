<?php

namespace Modules\Application\DcatAdmin\Metrics;

use Dcat\Admin\Widgets\Metrics\Card;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Modules\Application\Models\SystemLog;

/**
 * 系统错误日志统计 Metric
 *
 * 显示最近24小时的错误日志数量和详细信息
 *
 * @since 2026-08-27
 */
class SystemLogErrors extends Card
{
    /**
     * 初始化卡片
     *
     * @return void
     */
    protected function init(): void
    {
        parent::init();
        $this->title('错误日志统计');
        $this->height(300);  // 大型卡片
    }

    /**
     * 处理请求
     *
     * @param Request $request
     * @return void
     */
    public function handle(Request $request): void
    {
        $stats = $this->getTodayErrorStats();
        $this->withContent($stats);
    }

    /**
     * 获取最近24小时的错误统计
     *
     * @return array<string, mixed>
     */
    protected function getTodayErrorStats(): array
    {
        $now = time();
        $yesterday = $now - 86400; // 24小时前

        // 查询最近24小时的所有日志
        $logs = SystemLog::query()
            ->where('created_at', '>=', date('Y-m-d H:i:s', $yesterday))
            ->get();

        // 解析日志级别
        $errorCount = 0;
        $warningCount = 0;
        $criticalCount = 0;
        $errorSources = [];

        foreach ($logs as $log) {
            $data = json_decode($log->data1, true);
            $level = $data['level'] ?? 'info';

            if ($level === 'error') {
                $errorCount++;
                $source = $log->level1 ?: 'unknown';
                if (!isset($errorSources[$source])) {
                    $errorSources[$source] = 0;
                }
                $errorSources[$source]++;
            } elseif ($level === 'warning') {
                $warningCount++;
            } elseif (in_array($level, ['critical', 'alert', 'emergency'])) {
                $criticalCount++;
                $source = $log->level1 ?: 'unknown';
                if (!isset($errorSources[$source])) {
                    $errorSources[$source] = 0;
                }
                $errorSources[$source]++;
            }
        }

        // 按错误数量排序
        arsort($errorSources);
        $topSources = array_slice($errorSources, 0, 5, true);

        return [
            'error_count' => $errorCount,
            'warning_count' => $warningCount,
            'critical_count' => $criticalCount,
            'total_count' => $errorCount + $warningCount + $criticalCount,
            'top_sources' => $topSources,
        ];
    }

    /**
     * 设置卡片内容
     *
     * @param array<string, mixed> $content
     * @return $this
     */
    public function withContent($content): self
    {
        $errorCount = e((string) $content['error_count']);
        $warningCount = e((string) $content['warning_count']);
        $criticalCount = e((string) $content['critical_count']);
        $totalCount = $content['total_count'];
        $topSources = $content['top_sources'];

        // 根据错误数量设置状态
        $statusColor = $totalCount === 0 ? 'success' : ($criticalCount > 0 ? 'danger' : 'warning');
        $statusIcon = $totalCount === 0 ? 'check-circle' : ($criticalCount > 0 ? 'times-circle' : 'exclamation-triangle');

        // 日志列表 URL
        $logUrl = admin_url('module_application/system-log');

        // 构建主要错误来源列表
        $sourcesHtml = '';
        if (!empty($topSources)) {
            $sourcesHtml = '<div class="mt-3" style="border-top: 1px solid #eee; padding-top: 0.75rem;">';
            $sourcesHtml .= '<div class="text-muted small mb-2"><i class="fa fa-bug"></i> 主要错误来源</div>';
            foreach ($topSources as $source => $count) {
                $source = e($source);
                $count = e((string) $count);
                $sourcesHtml .= "<div class=\"d-flex justify-content-between py-1 px-2 small\">";
                $sourcesHtml .= "<span class=\"text-muted\"><i class=\"fa fa-circle text-danger\" style=\"font-size: 0.5rem;\"></i> {$source}</span>";
                $sourcesHtml .= "<span class=\"badge badge-danger\">{$count}</span>";
                $sourcesHtml .= "</div>";
            }
            $sourcesHtml .= '</div>';
        }

        $html = <<<HTML
<div style="padding: 0.75rem; line-height: 1.4;">
    <!-- 状态指示 -->
    <div class="d-flex align-items-center justify-content-between mb-3">
        <div class="text-{$statusColor}">
            <i class="fa fa-{$statusIcon} mr-2" style="font-size: 1.25rem;"></i>
            <span class="font-weight-bold">最近 24 小时</span>
        </div>
        <a href="{$logUrl}" class="btn btn-sm btn-outline-secondary">
            <i class="fa fa-list"></i> 查看详情
        </a>
    </div>

    <!-- 统计数字 -->
    <div class="row text-center mb-2">
        <div class="col-4">
            <div class="card bg-light border-0">
                <div class="card-body p-2">
                    <div class="text-danger font-weight-bold" style="font-size: 1.5rem;">{$errorCount}</div>
                    <small class="text-muted">错误</small>
                </div>
            </div>
        </div>
        <div class="col-4">
            <div class="card bg-light border-0">
                <div class="card-body p-2">
                    <div class="text-warning font-weight-bold" style="font-size: 1.5rem;">{$warningCount}</div>
                    <small class="text-muted">警告</small>
                </div>
            </div>
        </div>
        <div class="col-4">
            <div class="card bg-light border-0">
                <div class="card-body p-2">
                    <div class="text-danger font-weight-bold" style="font-size: 1.5rem;">{$criticalCount}</div>
                    <small class="text-muted">严重</small>
                </div>
            </div>
        </div>
    </div>

    <!-- 错误来源 -->
    {$sourcesHtml}
</div>
HTML;

        return $this->content($html);
    }

    }