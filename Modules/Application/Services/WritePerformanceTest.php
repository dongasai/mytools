<?php

declare(strict_types=1);

namespace Modules\Application\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Modules\Application\Dtos\PerformanceMetric;
use Modules\Application\Dtos\PerformanceTestResult;

/**
 * 写入性能测试服务
 *
 * 提供数据库写入性能测试功能
 */
class WritePerformanceTest
{
    /**
     * 执行单条插入性能测试
     *
     * @param int $iterations 测试迭代次数
     * @return PerformanceTestResult
     */
    public static function testSingleInsert(int $iterations = 50): PerformanceTestResult
    {
        $startTime = microtime(true);
        $startMemory = memory_get_usage();
        $writeTimes = [];

        try {
            // 创建临时测试表
            self::createTestTable();

            for ($i = 0; $i < $iterations; $i++) {
                $writeStart = microtime(true);

                DB::table(self::$testTableName)->insert([
                    'test_key' => 'test_' . $i,
                    'test_value' => 'value_' . $i,
                    'created_at' => time(),
                ]);

                $writeTimes[] = (microtime(true) - $writeStart) * 1000;
            }

            $totalTime = (microtime(true) - $startTime) * 1000;
            $memoryUsage = memory_get_usage() - $startMemory;

            // 清理测试数据
            self::cleanupTestTable();

            // 计算统计数据
            $minTime = min($writeTimes);
            $maxTime = max($writeTimes);
            $avgTime = array_sum($writeTimes) / count($writeTimes);

            $metrics = collect([
                new PerformanceMetric(
                    name: 'avg_insert_time',
                    value: $avgTime,
                    unit: 'ms',
                    min: $minTime,
                    max: $maxTime,
                    avg: $avgTime,
                    description: '平均插入时间'
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
                testName: '单条插入性能测试',
                testType: 'write',
                metrics: $metrics,
                details: [
                    'iterations' => $iterations,
                    'operation' => 'single_insert',
                ],
                totalTime: $totalTime
            );
        } catch (\Exception $e) {
            // 清理测试数据
            self::cleanupTestTable();

            $totalTime = (microtime(true) - $startTime) * 1000;

            return PerformanceTestResult::createFailure(
                testName: '单条插入性能测试',
                testType: 'write',
                errorMessage: $e->getMessage(),
                totalTime: $totalTime
            );
        }
    }

    /**
     * 执行批量插入性能测试
     *
     * @param int $batchSize 批量大小
     * @param int $iterations 测试迭代次数
     * @return PerformanceTestResult
     */
    public static function testBatchInsert(int $batchSize = 100, int $iterations = 10): PerformanceTestResult
    {
        $startTime = microtime(true);
        $startMemory = memory_get_usage();
        $writeTimes = [];

        try {
            // 创建临时测试表
            self::createTestTable();

            for ($i = 0; $i < $iterations; $i++) {
                $writeStart = microtime(true);

                // 准备批量数据
                $data = [];
                for ($j = 0; $j < $batchSize; $j++) {
                    $data[] = [
                        'test_key' => 'batch_' . $i . '_' . $j,
                        'test_value' => 'value_' . $j,
                        'created_at' => time(),
                    ];
                }

                DB::table(self::$testTableName)->insert($data);

                $writeTimes[] = (microtime(true) - $writeStart) * 1000;
            }

            $totalTime = (microtime(true) - $startTime) * 1000;
            $memoryUsage = memory_get_usage() - $startMemory;

            // 清理测试数据
            self::cleanupTestTable();

            // 计算统计数据
            $minTime = min($writeTimes);
            $maxTime = max($writeTimes);
            $avgTime = array_sum($writeTimes) / count($writeTimes);
            $totalWrites = $batchSize * $iterations;

            $metrics = collect([
                new PerformanceMetric(
                    name: 'avg_batch_time',
                    value: $avgTime,
                    unit: 'ms',
                    min: $minTime,
                    max: $maxTime,
                    avg: $avgTime,
                    description: '平均批量插入时间'
                ),
                new PerformanceMetric(
                    name: 'avg_single_time',
                    value: $avgTime / $batchSize,
                    unit: 'ms',
                    description: '平均单条插入时间（批量中）'
                ),
                new PerformanceMetric(
                    name: 'total_time',
                    value: $totalTime,
                    unit: 'ms',
                    description: '总耗时'
                ),
                new PerformanceMetric(
                    name: 'total_records',
                    value: (float) $totalWrites,
                    unit: 'records',
                    description: '总插入记录数'
                ),
                PerformanceMetric::createQueryCount($iterations),
                PerformanceMetric::createMemoryUsage($memoryUsage),
            ]);

            return PerformanceTestResult::createSuccess(
                testName: '批量插入性能测试',
                testType: 'write',
                metrics: $metrics,
                details: [
                    'iterations' => $iterations,
                    'batch_size' => $batchSize,
                    'total_records' => $totalWrites,
                    'operation' => 'batch_insert',
                ],
                totalTime: $totalTime
            );
        } catch (\Exception $e) {
            // 清理测试数据
            self::cleanupTestTable();

            $totalTime = (microtime(true) - $startTime) * 1000;

            return PerformanceTestResult::createFailure(
                testName: '批量插入性能测试',
                testType: 'write',
                errorMessage: $e->getMessage(),
                totalTime: $totalTime
            );
        }
    }

    /**
     * 执行更新性能测试
     *
     * @param int $iterations 测试迭代次数
     * @return PerformanceTestResult
     */
    public static function testUpdate(int $iterations = 50): PerformanceTestResult
    {
        $startTime = microtime(true);
        $startMemory = memory_get_usage();
        $updateTimes = [];

        try {
            // 创建临时测试表并插入数据
            self::createTestTable();
            self::insertTestData($iterations);

            for ($i = 0; $i < $iterations; $i++) {
                $updateStart = microtime(true);

                DB::table(self::$testTableName)
                    ->where('test_key', 'update_' . $i)
                    ->update([
                        'test_value' => 'updated_' . $i,
                        'updated_at' => time(),
                    ]);

                $updateTimes[] = (microtime(true) - $updateStart) * 1000;
            }

            $totalTime = (microtime(true) - $startTime) * 1000;
            $memoryUsage = memory_get_usage() - $startMemory;

            // 清理测试数据
            self::cleanupTestTable();

            // 计算统计数据
            $minTime = min($updateTimes);
            $maxTime = max($updateTimes);
            $avgTime = array_sum($updateTimes) / count($updateTimes);

            $metrics = collect([
                new PerformanceMetric(
                    name: 'avg_update_time',
                    value: $avgTime,
                    unit: 'ms',
                    min: $minTime,
                    max: $maxTime,
                    avg: $avgTime,
                    description: '平均更新时间'
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
                testName: '更新性能测试',
                testType: 'write',
                metrics: $metrics,
                details: [
                    'iterations' => $iterations,
                    'operation' => 'update',
                ],
                totalTime: $totalTime
            );
        } catch (\Exception $e) {
            // 清理测试数据
            self::cleanupTestTable();

            $totalTime = (microtime(true) - $startTime) * 1000;

            return PerformanceTestResult::createFailure(
                testName: '更新性能测试',
                testType: 'write',
                errorMessage: $e->getMessage(),
                totalTime: $totalTime
            );
        }
    }

    /**
     * 创建临时测试表
     *
     * @return void
     */
    private static function createTestTable(): void
    {
        // 使用唯一表名避免多进程并发冲突
        $tableName = 'perf_test_' . time() . '_' . rand(1000, 9999);

        $driver = config('database.connections.' . config('database.default') . '.driver');

        if ($driver === 'mysql') {
            $sql = "
                CREATE TABLE IF NOT EXISTS {$tableName} (
                    id INTEGER PRIMARY KEY AUTO_INCREMENT,
                    test_key VARCHAR(255) NOT NULL,
                    test_value TEXT,
                    created_at INTEGER,
                    updated_at INTEGER
                )
            ";
        } else {
            // SQLite 或其他数据库
            $sql = "
                CREATE TABLE IF NOT EXISTS {$tableName} (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    test_key VARCHAR(255) NOT NULL,
                    test_value TEXT,
                    created_at INTEGER,
                    updated_at INTEGER
                )
            ";
        }

        DB::statement($sql);

        // 存储表名供后续清理使用
        self::$testTableName = $tableName;
    }

    /**
     * 清理临时测试表
     *
     * @return void
     */
    private static function cleanupTestTable(): void
    {
        if (self::$testTableName) {
            try {
                DB::statement('DROP TABLE IF EXISTS ' . self::$testTableName);
            } catch (\Exception $e) {
                Log::warning('临时测试表清理失败', [
                    'table' => self::$testTableName,
                    'error' => $e->getMessage(),
                ]);
            }
            self::$testTableName = null;
        }
    }

    /**
     * 临时测试表名（动态生成）
     *
     * @var string|null
     */
    private static ?string $testTableName = null;

    /**
     * 插入测试数据
     *
     * @param int $count 数据数量
     * @return void
     */
    private static function insertTestData(int $count): void
    {
        for ($i = 0; $i < $count; $i++) {
            DB::table(self::$testTableName)->insert([
                'test_key' => 'update_' . $i,
                'test_value' => 'value_' . $i,
                'created_at' => time(),
            ]);
        }
    }
}