<?php

declare(strict_types=1);

namespace Modules\Application\Console;

use DLaravel\Commands\Command;
use Illuminate\Support\Collection;
use Modules\Application\Services\PerformanceReportService;
use Modules\Application\Services\PerformanceTestService;

/**
 * 数据库性能测试命令
 *
 * 用于执行数据库性能测试并生成报告
 * 支持查询性能测试、写入性能测试和综合测试
 */
class DbPerformanceTestCommand extends Command
{
    /**
     * 命令名称
     *
     * @var string
     */
    protected $signature = 'application:db-performance-test
                            {--type=all : 测试类型（query、write、all、quick、deep）}
                            {--iterations=100 : 查询测试迭代次数}
                            {--write-iterations=50 : 写入测试迭代次数}
                            {--batch-size=100 : 批量写入测试大小}
                            {--output=text : 输出格式（text、table、log）}
                            {--save-log : 将测试结果保存到日志}';

    /**
     * 命令描述
     *
     * @var string
     */
    protected $description = '执行数据库性能测试并生成报告';

    /**
     * 执行命令
     *
     * @return void
     */
    public function handleRun(): void
    {
        try {
            $this->info('开始数据库性能测试...');
            $this->newLine();

            // 获取命令选项
            $type = $this->option('type');
            $iterations = (int) $this->option('iterations');
            $writeIterations = (int) $this->option('write-iterations');
            $batchSize = (int) $this->option('batch-size');
            $output = $this->option('output');
            $saveLog = $this->option('save-log');

            // 验证参数
            $this->validateOptions($type, $iterations, $writeIterations, $batchSize);

            // 显示测试配置
            $this->showTestConfig($type, $iterations, $writeIterations, $batchSize);

            // 执行性能测试
            $results = $this->runPerformanceTests($type, $iterations, $writeIterations, $batchSize);

            // 显示测试结果
            $this->displayResults($results, $output);

            // 保存到日志
            if ($saveLog) {
                PerformanceReportService::saveToLog($results);
                $this->info('测试结果已保存到日志');
            }

            // 显示性能建议
            $this->showRecommendations($results);

            $this->newLine();
            $this->info('性能测试完成！');
        } catch (\InvalidArgumentException $e) {
            $this->error('参数验证失败: ' . $e->getMessage());
            $this->info('请使用正确的参数值重新运行测试');
        }
    }

    /**
     * 验证命令选项
     *
     * @param string $type 测试类型
     * @param int $iterations 迭代次数
     * @param int $writeIterations 写入迭代次数
     * @param int $batchSize 批量大小
     * @return void
     */
    protected function validateOptions(
        string $type,
        int $iterations,
        int $writeIterations,
        int $batchSize
    ): void {
        $validTypes = ['query', 'write', 'all', 'quick', 'deep'];

        if (!in_array($type, $validTypes)) {
            $this->error("无效的测试类型: {$type}");
            $this->info("有效的测试类型: " . implode(', ', $validTypes));
            throw new \InvalidArgumentException("无效的测试类型: {$type}");
        }

        if ($iterations < 1 || $iterations > 1000) {
            $this->error('查询迭代次数必须在1-1000之间');
            throw new \InvalidArgumentException('查询迭代次数必须在1-1000之间');
        }

        if ($writeIterations < 1 || $writeIterations > 500) {
            $this->error('写入迭代次数必须在1-500之间');
            throw new \InvalidArgumentException('写入迭代次数必须在1-500之间');
        }

        if ($batchSize < 10 || $batchSize > 1000) {
            $this->error('批量大小必须在10-1000之间');
            throw new \InvalidArgumentException('批量大小必须在10-1000之间');
        }
    }

    /**
     * 显示测试配置
     *
     * @param string $type 测试类型
     * @param int $iterations 迭代次数
     * @param int $writeIterations 写入迭代次数
     * @param int $batchSize 批量大小
     * @return void
     */
    protected function showTestConfig(
        string $type,
        int $iterations,
        int $writeIterations,
        int $batchSize
    ): void {
        $this->info('测试配置:');
        $this->table(['配置项', '值'], [
            ['测试类型', $type],
            ['查询迭代次数', $iterations],
            ['写入迭代次数', $writeIterations],
            ['批量大小', $batchSize],
        ]);

        $this->newLine();

        // 显示数据库信息
        $dbInfo = PerformanceTestService::getDatabaseInfo();
        $this->info('数据库信息:');
        $this->table(['信息项', '值'], [
            ['连接类型', $dbInfo['connection']],
            ['驱动', $dbInfo['driver']],
            ['数据库', $dbInfo['database']],
            ['主机', $dbInfo['host']],
        ]);

        $this->newLine();
    }

    /**
     * 执行性能测试
     *
     * @param string $type 测试类型
     * @param int $iterations 迭代次数
     * @param int $writeIterations 写入迭代次数
     * @param int $batchSize 批量大小
     * @return Collection
     */
    protected function runPerformanceTests(
        string $type,
        int $iterations,
        int $writeIterations,
        int $batchSize
    ): Collection {
        $this->info('正在执行性能测试...');
        $this->newLine();

        $results = match ($type) {
            'query' => PerformanceTestService::runQueryOnly($iterations),
            'write' => PerformanceTestService::runWriteOnly($writeIterations, $batchSize),
            'all' => PerformanceTestService::runFullSuite(['all'], $iterations, $writeIterations, $batchSize),
            'quick' => PerformanceTestService::runQuickTest(),
            'deep' => PerformanceTestService::runDeepTest(),
            default => collect(),
        };

        return $results;
    }

    /**
     * 显示测试结果
     *
     * @param Collection $results 测试结果集合
     * @param string $output 输出格式
     * @return void
     */
    protected function displayResults(Collection $results, string $output): void
    {
        $this->newLine();
        $this->info('测试结果:');
        $this->newLine();

        if ($output === 'table') {
            $tableData = PerformanceReportService::generateTableReport($results);
            $this->table(
                ['测试名称', '状态', '平均耗时', '总耗时', '迭代次数', '内存使用'],
                $tableData
            );
        } elseif ($output === 'log') {
            PerformanceReportService::saveToLog($results);
            $this->info('测试结果已保存到日志文件');
        } else {
            // 默认文本格式
            $report = PerformanceReportService::generateTextReport($results);
            $this->line($report);
        }
    }

    /**
     * 显示性能建议
     *
     * @param Collection $results 测试结果集合
     * @return void
     */
    protected function showRecommendations(Collection $results): void
    {
        $this->newLine();
        $this->info('性能建议:');
        $this->newLine();

        $recommendations = PerformanceReportService::generateRecommendations($results);

        foreach ($recommendations as $index => $recommendation) {
            $this->line(($index + 1) . ". {$recommendation}");
        }
    }
}