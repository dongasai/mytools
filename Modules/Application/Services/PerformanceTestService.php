<?php

declare(strict_types=1);

namespace Modules\Application\Services;

use Illuminate\Support\Collection;
use Modules\Application\Dtos\PerformanceTestResult;

/**
 * 性能测试核心服务
 *
 * 协调各种性能测试并提供综合测试功能
 */
class PerformanceTestService
{
    /**
     * 执行完整的性能测试套件
     *
     * @param array $testTypes 测试类型列表（query、write、all）
     * @param int $queryIterations 查询测试迭代次数
     * @param int $writeIterations 写入测试迭代次数
     * @param int $batchSize 批量测试大小
     * @return Collection
     */
    public static function runFullSuite(
        array $testTypes = ['all'],
        int $queryIterations = 100,
        int $writeIterations = 50,
        int $batchSize = 100
    ): Collection {
        $results = collect();

        // 查询性能测试
        if (in_array('query', $testTypes) || in_array('all', $testTypes)) {
            $results->push(QueryPerformanceTest::testSimpleQuery($queryIterations));
            $results->push(QueryPerformanceTest::testComplexQuery((int) ($queryIterations / 2)));
            $results->push(QueryPerformanceTest::testAggregateQuery((int) ($queryIterations / 3)));
        }

        // 写入性能测试
        if (in_array('write', $testTypes) || in_array('all', $testTypes)) {
            $results->push(WritePerformanceTest::testSingleInsert($writeIterations));
            $results->push(WritePerformanceTest::testBatchInsert($batchSize, (int) ($writeIterations / 5)));
            $results->push(WritePerformanceTest::testUpdate($writeIterations));
        }

        return $results;
    }

    /**
     * 执行自定义性能测试
     *
     * @param string $testName 测试名称
     * @param callable $testFunction 测试函数
     * @param int $iterations 迭代次数
     * @return PerformanceTestResult
     */
    public static function runCustomTest(
        string $testName,
        callable $testFunction,
        int $iterations = 100
    ): PerformanceTestResult {
        $startTime = microtime(true);
        $startMemory = memory_get_usage();

        try {
            $times = [];

            for ($i = 0; $i < $iterations; $i++) {
                $iterationStart = microtime(true);

                // 执行自定义测试函数
                $testFunction();

                $times[] = (microtime(true) - $iterationStart) * 1000;
            }

            $totalTime = (microtime(true) - $startTime) * 1000;
            $memoryUsage = memory_get_usage() - $startMemory;

            // 计算统计数据
            $minTime = min($times);
            $maxTime = max($times);
            $avgTime = array_sum($times) / count($times);

            return PerformanceTestResult::createSuccess(
                testName: $testName,
                testType: 'custom',
                metrics: collect([
                    new \Modules\Application\Dtos\PerformanceMetric(
                        name: 'avg_time',
                        value: $avgTime,
                        unit: 'ms',
                        min: $minTime,
                        max: $maxTime,
                        avg: $avgTime,
                        description: '平均执行时间'
                    ),
                    new \Modules\Application\Dtos\PerformanceMetric(
                        name: 'total_time',
                        value: $totalTime,
                        unit: 'ms',
                        description: '总耗时'
                    ),
                    \Modules\Application\Dtos\PerformanceMetric::createQueryCount($iterations),
                    \Modules\Application\Dtos\PerformanceMetric::createMemoryUsage($memoryUsage),
                ]),
                details: [
                    'iterations' => $iterations,
                ],
                totalTime: $totalTime
            );
        } catch (\Exception $e) {
            $totalTime = (microtime(true) - $startTime) * 1000;

            return PerformanceTestResult::createFailure(
                testName: $testName,
                testType: 'custom',
                errorMessage: $e->getMessage(),
                totalTime: $totalTime
            );
        }
    }

    /**
     * 执行快速性能测试（小规模）
     *
     * @return Collection
     */
    public static function runQuickTest(): Collection
    {
        return self::runFullSuite(
            testTypes: ['all'],
            queryIterations: 20,
            writeIterations: 10,
            batchSize: 50
        );
    }

    /**
     * 执行深度性能测试（大规模）
     *
     * @return Collection
     */
    public static function runDeepTest(): Collection
    {
        return self::runFullSuite(
            testTypes: ['all'],
            queryIterations: 200,
            writeIterations: 100,
            batchSize: 200
        );
    }

    /**
     * 仅执行查询性能测试
     *
     * @param int $iterations 迭代次数
     * @return Collection
     */
    public static function runQueryOnly(int $iterations = 100): Collection
    {
        return self::runFullSuite(
            testTypes: ['query'],
            queryIterations: $iterations
        );
    }

    /**
     * 仅执行写入性能测试
     *
     * @param int $iterations 迭代次数
     * @param int $batchSize 批量大小
     * @return Collection
     */
    public static function runWriteOnly(int $iterations = 50, int $batchSize = 100): Collection
    {
        return self::runFullSuite(
            testTypes: ['write'],
            writeIterations: $iterations,
            batchSize: $batchSize
        );
    }

    /**
     * 获取数据库基本信息
     *
     * @return array
     */
    public static function getDatabaseInfo(): array
    {
        $connection = config('database.default');
        $config = config('database.connections.' . $connection);

        return [
            'connection' => $connection,
            'driver' => $config['driver'] ?? 'unknown',
            'database' => $config['database'] ?? 'unknown',
            'host' => $config['host'] ?? 'localhost',
            'port' => $config['port'] ?? 'default',
            'prefix' => $config['prefix'] ?? '',
        ];
    }
}