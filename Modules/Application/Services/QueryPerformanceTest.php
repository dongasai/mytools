<?php

declare(strict_types=1);

namespace Modules\Application\Services;

use Illuminate\Support\Facades\DB;
use Modules\Application\Dtos\PerformanceMetric;
use Modules\Application\Dtos\PerformanceTestResult;

/**
 * 查询性能测试服务
 *
 * 提供数据库查询性能测试功能
 */
class QueryPerformanceTest
{
    /**
     * 执行简单查询性能测试
     *
     * @param int $iterations 测试迭代次数
     * @return PerformanceTestResult
     */
    public static function testSimpleQuery(int $iterations = 100): PerformanceTestResult
    {
        $startTime = microtime(true);
        $startMemory = memory_get_usage();
        $queryTimes = [];

        try {
            for ($i = 0; $i < $iterations; $i++) {
                $queryStart = microtime(true);

                // 执行简单查询
                DB::table('application_configs')->select('id')->limit(1)->get();

                $queryTimes[] = (microtime(true) - $queryStart) * 1000;
            }

            $totalTime = (microtime(true) - $startTime) * 1000;
            $memoryUsage = memory_get_usage() - $startMemory;

            // 计算统计数据
            $minTime = min($queryTimes);
            $maxTime = max($queryTimes);
            $avgTime = array_sum($queryTimes) / count($queryTimes);

            $metrics = collect([
                new PerformanceMetric(
                    name: 'avg_query_time',
                    value: $avgTime,
                    unit: 'ms',
                    min: $minTime,
                    max: $maxTime,
                    avg: $avgTime,
                    description: '平均查询时间'
                ),
                new PerformanceMetric(
                    name: 'total_time',
                    value: $totalTime,
                    unit: 'ms',
                    description: '总耗时'
                ),
                PerformanceMetric::createQueryCount($iterations),
                PerformanceMetric::createMemoryUsage($memoryUsage),
            ]);

            return PerformanceTestResult::createSuccess(
                testName: '简单查询性能测试',
                testType: 'query',
                metrics: $metrics,
                details: [
                    'iterations' => $iterations,
                    'table' => 'application_configs',
                ],
                totalTime: $totalTime
            );
        } catch (\Exception $e) {
            $totalTime = (microtime(true) - $startTime) * 1000;

            return PerformanceTestResult::createFailure(
                testName: '简单查询性能测试',
                testType: 'query',
                errorMessage: $e->getMessage(),
                totalTime: $totalTime
            );
        }
    }

    /**
     * 执行复杂查询性能测试
     *
     * @param int $iterations 测试迭代次数
     * @return PerformanceTestResult
     */
    public static function testComplexQuery(int $iterations = 50): PerformanceTestResult
    {
        $startTime = microtime(true);
        $startMemory = memory_get_usage();
        $queryTimes = [];

        try {
            for ($i = 0; $i < $iterations; $i++) {
                $queryStart = microtime(true);

                // 执行复杂查询（包含JOIN和条件）
                DB::table('application_configs')
                    ->select(['application_configs.*'])
                    ->where('application_configs.keyname', 'like', '%app%')
                    ->orderBy('application_configs.created_at', 'desc')
                    ->limit(10)
                    ->get();

                $queryTimes[] = (microtime(true) - $queryStart) * 1000;
            }

            $totalTime = (microtime(true) - $startTime) * 1000;
            $memoryUsage = memory_get_usage() - $startMemory;

            // 计算统计数据
            $minTime = min($queryTimes);
            $maxTime = max($queryTimes);
            $avgTime = array_sum($queryTimes) / count($queryTimes);

            $metrics = collect([
                new PerformanceMetric(
                    name: 'avg_query_time',
                    value: $avgTime,
                    unit: 'ms',
                    min: $minTime,
                    max: $maxTime,
                    avg: $avgTime,
                    description: '平均查询时间'
                ),
                new PerformanceMetric(
                    name: 'total_time',
                    value: $totalTime,
                    unit: 'ms',
                    description: '总耗时'
                ),
                PerformanceMetric::createQueryCount($iterations),
                PerformanceMetric::createMemoryUsage($memoryUsage),
            ]);

            return PerformanceTestResult::createSuccess(
                testName: '复杂查询性能测试',
                testType: 'query',
                metrics: $metrics,
                details: [
                    'iterations' => $iterations,
                    'table' => 'application_configs',
                    'query_type' => 'complex_with_conditions',
                ],
                totalTime: $totalTime
            );
        } catch (\Exception $e) {
            $totalTime = (microtime(true) - $startTime) * 1000;

            return PerformanceTestResult::createFailure(
                testName: '复杂查询性能测试',
                testType: 'query',
                errorMessage: $e->getMessage(),
                totalTime: $totalTime
            );
        }
    }

    /**
     * 执行聚合查询性能测试
     *
     * @param int $iterations 测试迭代次数
     * @return PerformanceTestResult
     */
    public static function testAggregateQuery(int $iterations = 30): PerformanceTestResult
    {
        $startTime = microtime(true);
        $startMemory = memory_get_usage();
        $queryTimes = [];

        try {
            for ($i = 0; $i < $iterations; $i++) {
                $queryStart = microtime(true);

                // 执行聚合查询
                DB::table('application_configs')
                    ->select([
                        DB::raw('COUNT(*) as total_count'),
                        DB::raw('MAX(created_at) as max_created'),
                        DB::raw('MIN(created_at) as min_created'),
                    ])
                    ->where('keyname', 'like', '%app%')
                    ->first();

                $queryTimes[] = (microtime(true) - $queryStart) * 1000;
            }

            $totalTime = (microtime(true) - $startTime) * 1000;
            $memoryUsage = memory_get_usage() - $startMemory;

            // 计算统计数据
            $minTime = min($queryTimes);
            $maxTime = max($queryTimes);
            $avgTime = array_sum($queryTimes) / count($queryTimes);

            $metrics = collect([
                new PerformanceMetric(
                    name: 'avg_query_time',
                    value: $avgTime,
                    unit: 'ms',
                    min: $minTime,
                    max: $maxTime,
                    avg: $avgTime,
                    description: '平均查询时间'
                ),
                new PerformanceMetric(
                    name: 'total_time',
                    value: $totalTime,
                    unit: 'ms',
                    description: '总耗时'
                ),
                PerformanceMetric::createQueryCount($iterations),
                PerformanceMetric::createMemoryUsage($memoryUsage),
            ]);

            return PerformanceTestResult::createSuccess(
                testName: '聚合查询性能测试',
                testType: 'query',
                metrics: $metrics,
                details: [
                    'iterations' => $iterations,
                    'table' => 'application_configs',
                    'query_type' => 'aggregate',
                ],
                totalTime: $totalTime
            );
        } catch (\Exception $e) {
            $totalTime = (microtime(true) - $startTime) * 1000;

            return PerformanceTestResult::createFailure(
                testName: '聚合查询性能测试',
                testType: 'query',
                errorMessage: $e->getMessage(),
                totalTime: $totalTime
            );
        }
    }
}