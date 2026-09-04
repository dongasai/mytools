<?php

declare(strict_types=1);

namespace Modules\Application\Services;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Modules\Application\Dtos\PerformanceTestResult;

/**
 * 性能测试报告服务
 *
 * 提供性能测试结果的报告生成、格式化和日志记录功能
 */
class PerformanceReportService
{
    /**
     * 生成文本格式报告
     *
     * @param Collection $results 测试结果集合
     * @return string
     */
    public static function generateTextReport(Collection $results): string
    {
        $report = "数据库性能测试报告\n";
        $report .= "==================\n\n";
        $report .= "测试时间: " . date('Y-m-d H:i:s') . "\n";
        $report .= "数据库信息: " . json_encode(PerformanceTestService::getDatabaseInfo()) . "\n\n";

        foreach ($results as $result) {
            $report .= self::formatSingleResult($result);
            $report .= "\n" . str_repeat('-', 50) . "\n\n";
        }

        // 添加总结
        $report .= self::generateSummary($results);

        return $report;
    }

    /**
     * 生成表格格式报告
     *
     * @param Collection $results 测试结果集合
     * @return array
     */
    public static function generateTableReport(Collection $results): array
    {
        $tableData = [];

        foreach ($results as $result) {
            if (!$result->isSuccess()) {
                $tableData[] = [
                    '测试名称' => $result->getTestName(),
                    '状态' => '失败',
                    '总耗时' => sprintf('%.2f ms', $result->getTotalTime()),
                    '错误信息' => $result->getErrorMessage(),
                ];
                continue;
            }

            $avgTimeMetric = $result->getMetric('avg_query_time')
                ?? $result->getMetric('avg_insert_time')
                ?? $result->getMetric('avg_update_time')
                ?? $result->getMetric('avg_batch_time');

            $tableData[] = [
                '测试名称' => $result->getTestName(),
                '状态' => '成功',
                '平均耗时' => $avgTimeMetric ? sprintf('%.2f ms', $avgTimeMetric->getValue()) : 'N/A',
                '总耗时' => sprintf('%.2f ms', $result->getTotalTime()),
                '迭代次数' => $result->getQueryCount(),
                '内存使用' => sprintf('%.2f MB', $result->getMemoryUsage() / 1024 / 1024),
            ];
        }

        return $tableData;
    }

    /**
     * 格式化单个测试结果
     *
     * @param PerformanceTestResult $result 测试结果
     * @return string
     */
    private static function formatSingleResult(PerformanceTestResult $result): string
    {
        $text = "测试名称: {$result->getTestName()}\n";
        $text .= "测试类型: {$result->getTestType()}\n";
        $text .= "测试状态: " . ($result->isSuccess() ? '成功' : '失败') . "\n";

        if (!$result->isSuccess()) {
            $text .= "错误信息: {$result->getErrorMessage()}\n";

            return $text;
        }

        $text .= "总耗时: " . sprintf('%.2f ms', $result->getTotalTime()) . "\n\n";
        $text .= "性能指标:\n";

        foreach ($result->getMetrics() as $metric) {
            $text .= "  - {$metric->toReadableString()}\n";
        }

        if (!empty($result->getDetails())) {
            $text .= "\n详细信息:\n";
            foreach ($result->getDetails() as $key => $value) {
                $text .= "  - {$key}: {$value}\n";
            }
        }

        return $text;
    }

    /**
     * 生成测试总结
     *
     * @param Collection $results 测试结果集合
     * @return string
     */
    private static function generateSummary(Collection $results): string
    {
        $successCount = $results->filter(fn($result) => $result->isSuccess())->count();
        $failureCount = $results->filter(fn($result) => !$result->isSuccess())->count();
        $totalTime = $results->sum('totalTime');

        $summary = "测试总结\n";
        $summary .= "--------\n";
        $summary .= "成功测试: {$successCount}\n";
        $summary .= "失败测试: {$failureCount}\n";
        $summary .= "总耗时: " . sprintf('%.2f ms', $totalTime) . "\n";

        if ($failureCount > 0) {
            $summary .= "\n警告: 存在失败的测试，请检查数据库配置和性能！\n";
        } else {
            $summary .= "\n所有测试通过，数据库性能良好！\n";
        }

        return $summary;
    }

    /**
     * 保存测试报告到日志
     *
     * @param Collection $results 测试结果集合
     * @return void
     */
    public static function saveToLog(Collection $results): void
    {
        $report = self::generateTextReport($results);

        Log::info('数据库性能测试报告', [
            'report' => $report,
            'results' => $results->map(fn ($r) => $r->toArray())->toArray(),
            'database_info' => PerformanceTestService::getDatabaseInfo(),
        ]);
    }

    /**
     * 分析性能测试结果
     *
     * @param Collection $results 测试结果集合
     * @return array
     */
    public static function analyzeResults(Collection $results): array
    {
        $successCount = $results->filter(fn($r) => $r->isSuccess())->count();
        $totalCount = $results->count();

        $analysis = [
            'total_tests' => $totalCount,
            'success_rate' => $totalCount > 0 ? ($successCount / $totalCount * 100) : 0,
            'avg_total_time' => $results->avg(fn($r) => $r->getTotalTime()),
            'max_total_time' => $results->max(fn($r) => $r->getTotalTime()),
            'min_total_time' => $results->min(fn($r) => $r->getTotalTime()),
        ];

        // 分析查询性能
        $queryResults = $results->filter(fn($r) => $r->getTestType() === 'query');
        if ($queryResults->count() > 0) {
            $analysis['query_performance'] = [
                'avg_query_time' => $queryResults->avg(fn ($r) => $r->getQueryTime()),
                'total_queries' => $queryResults->sum(fn ($r) => $r->getQueryCount()),
            ];
        }

        // 分析写入性能
        $writeResults = $results->filter(fn($r) => $r->getTestType() === 'write');
        if ($writeResults->count() > 0) {
            $analysis['write_performance'] = [
                'avg_write_time' => $writeResults->avg(fn($r) => $r->getTotalTime()),
                'total_writes' => $writeResults->sum(fn ($r) => $r->getQueryCount()),
            ];
        }

        return $analysis;
    }

    /**
     * 生成性能建议
     *
     * @param Collection $results 测试结果集合
     * @return array
     */
    public static function generateRecommendations(Collection $results): array
    {
        $recommendations = [];
        $analysis = self::analyzeResults($results);

        // 检查平均查询时间
        if (isset($analysis['query_performance']['avg_query_time'])) {
            $avgQueryTime = $analysis['query_performance']['avg_query_time'];

            if ($avgQueryTime > 100) {
                $recommendations[] = '查询平均耗时过高（>100ms），建议优化查询语句或添加索引';
            } elseif ($avgQueryTime > 50) {
                $recommendations[] = '查询平均耗时较高（>50ms），建议检查查询性能';
            }
        }

        // 检查成功率
        if ($analysis['success_rate'] < 100) {
            $recommendations[] = '存在失败的测试，建议检查数据库连接和配置';
        }

        // 检查内存使用
        $avgMemory = $results->avg(fn ($r) => $r->getMemoryUsage());
        if ($avgMemory > 10 * 1024 * 1024) { // 10MB
            $recommendations[] = '内存使用量较高，建议优化查询或减少数据量';
        }

        if (empty($recommendations)) {
            $recommendations[] = '数据库性能良好，无需特别优化';
        }

        return $recommendations;
    }
}