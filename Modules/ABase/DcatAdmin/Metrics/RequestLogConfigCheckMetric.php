<?php

namespace Modules\ABase\DcatAdmin\Metrics;

use Dcat\Admin\Widgets\Metrics\Card;
use Illuminate\Http\Request;
use Modules\ABase\Services\RequestLogConfigCheckService;

/**
 * 请求日志配置检查卡片
 *
 * 检查请求日志配置是否正确，包括数据库连接和数据表
 *
 * @package Modules\ABase\DcatAdmin\Metrics
 */
class RequestLogConfigCheckMetric extends Card
{
    /**
     * 初始化卡片
     *
     * @return void
     */
    protected function init()
    {
        parent::init();
        $this->title('请求日志配置检查');
        $this->height(300);  // 大型卡片：与其他配置检查 Metric 保持一致
    }

    /**
     * 处理异步请求
     *
     * @param Request $request
     * @return void
     */
    public function handle(Request $request)
    {
        $summary = RequestLogConfigCheckService::getSummary();
        $configInfo = RequestLogConfigCheckService::getConfigInfo();
        $currentCount = RequestLogConfigCheckService::getCurrentLogCount();

        $this->withContent($summary, $configInfo, $currentCount);
    }

    /**
     * 卡片内容
     *
     * @param array $summary 检查结果摘要
     * @param array $configInfo 配置信息
     * @param int $currentCount 当前日志条数
     * @return $this
     */
    public function withContent(array $summary, array $configInfo, int $currentCount = 0)
    {
        // XSS 防护：转义所有变量
        $enabled = $summary['enabled'];
        $total = (int) $summary['total'];
        $issues = $summary['issues'];
        $status = e($summary['status']);
        $message = e($summary['message']);

        // 配置信息
        $maxRecords = (int) $configInfo['max_records'];
        $connection = e($configInfo['connection']);
        $table = e($configInfo['table']);

        // 根据状态选择图标和颜色
        if (!$enabled) {
            // 未启用状态
            $html = $this->renderDisabledState();
        } elseif ($total === 0) {
            // 配置正常状态
            $html = $this->renderSuccessState($connection, $table, $maxRecords, $currentCount);
        } else {
            // 发现问题状态
            $html = $this->renderWarningState($message, $total, $issues, $connection, $table, $maxRecords, $currentCount);
        }

        return $this->content($html);
    }

    /**
     * 渲染未启用状态
     *
     * @return string
     */
    protected function renderDisabledState(): string
    {
        return <<<HTML
<div style="padding: 0.5rem;">
    <div class="text-center" style="padding: 2rem 0;">
        <div class="text-info mb-2">
            <i class="fa fa-info-circle mr-1"></i>
            <span class="font-weight-bold">请求日志功能未启用</span>
        </div>
        <div class="text-muted small">
            启用：<code>REQUEST_LOG_ENABLED=true</code>
        </div>
    </div>
</div>
HTML;
    }

    /**
     * 渲染配置正常状态
     *
     * @param string $connection 数据库连接
     * @param string $table 数据表
     * @param int $maxRecords 保留记录数
     * @param int $currentCount 当前日志条数
     * @return string
     */
    protected function renderSuccessState(string $connection, string $table, int $maxRecords, int $currentCount): string
    {
        return <<<HTML
<div style="padding: 0.5rem;">
    <div class="text-success text-center mb-2">
        <i class="fa fa-check-circle mr-1"></i>
        <span class="font-weight-bold">配置正常</span>
    </div>
    <div class="list-group list-group-flush">
        <div class="list-group-item d-flex justify-content-between py-1 px-2">
            <span class="text-muted"><i class="fa fa-list-ol mr-1"></i>当前条数</span>
            <span class="text-primary font-weight-bold">{$currentCount} 条</span>
        </div>
        <div class="list-group-item d-flex justify-content-between py-1 px-2">
            <span class="text-muted"><i class="fa fa-database mr-1"></i>连接</span>
            <span class="text-primary">{$connection}</span>
        </div>
        <div class="list-group-item d-flex justify-content-between py-1 px-2">
            <span class="text-muted"><i class="fa fa-table mr-1"></i>表</span>
            <span class="text-primary">{$table}</span>
        </div>
        <div class="list-group-item d-flex justify-content-between py-1 px-2">
            <span class="text-muted"><i class="fa fa-archive mr-1"></i>保留</span>
            <span class="text-primary">{$maxRecords} 条</span>
        </div>
    </div>
</div>
HTML;
    }

    /**
     * 渲染发现问题状态
     *
     * @param string $message 消息
     * @param int $total 问题总数
     * @param array $issues 问题列表
     * @param string $connection 数据库连接
     * @param string $table 数据表
     * @param int $maxRecords 保留记录数
     * @param int $currentCount 当前日志条数
     * @return string
     */
    protected function renderWarningState(
        string $message,
        int $total,
        array $issues,
        string $connection,
        string $table,
        int $maxRecords,
        int $currentCount
    ): string {
        // XSS 防护：转义每个问题，限制最多显示 2 个问题
        $issueList = '';
        $displayIssues = array_slice($issues, 0, 2);
        foreach ($displayIssues as $issue) {
            $escapedIssue = e($issue);
            $issueList .= "<li>{$escapedIssue}</li>";
        }

        // 如果问题超过 2 个，显示省略提示
        if (count($issues) > 2) {
            $remaining = count($issues) - 2;
            $issueList .= "<li class='text-muted'>...还有 {$remaining} 个问题</li>";
        }

        return <<<HTML
<div style="padding: 0.5rem;">
    <div class="text-warning mb-2">
        <i class="fa fa-exclamation-triangle mr-1"></i>
        <span class="font-weight-bold">{$message}</span>
        <span class="badge badge-warning">{$total}</span>
    </div>
    <ul style="padding-left: 1.2rem; margin: 0.5rem 0; max-height: 60px; overflow-y: auto;">
        {$issueList}
    </ul>
    <div class="list-group list-group-flush" style="border-top: 1px solid #eee; padding-top: 0.3rem;">
        <div class="list-group-item d-flex justify-content-between py-1 px-2" style="border: none;">
            <span class="text-muted"><i class="fa fa-list-ol mr-1"></i>当前条数</span>
            <span>{$currentCount} 条</span>
        </div>
        <div class="list-group-item d-flex justify-content-between py-1 px-2" style="border: none;">
            <span class="text-muted"><i class="fa fa-database mr-1"></i>连接</span>
            <span>{$connection}</span>
        </div>
        <div class="list-group-item d-flex justify-content-between py-1 px-2" style="border: none;">
            <span class="text-muted"><i class="fa fa-table mr-1"></i>表</span>
            <span>{$table}</span>
        </div>
    </div>
</div>
HTML;
    }
}