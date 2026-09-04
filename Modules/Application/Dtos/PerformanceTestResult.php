<?php

declare(strict_types=1);

namespace Modules\Application\Dtos;

use Illuminate\Support\Collection;

/**
 * 性能测试结果数据传输对象
 *
 * 用于封装完整的性能测试结果数据
 */
class PerformanceTestResult
{
    /**
     * 测试名称
     *
     * @var string
     */
    private string $testName;

    /**
     * 测试类型（query、write、mixed）
     *
     * @var string
     */
    private string $testType;

    /**
     * 性能指标集合
     *
     * @var Collection
     */
    private Collection $metrics;

    /**
     * 详细信息
     *
     * @var array
     */
    private array $details;

    /**
     * 总耗时（毫秒）
     *
     * @var float
     */
    private float $totalTime;

    /**
     * 是否成功
     *
     * @var bool
     */
    private bool $success;

    /**
     * 错误信息（可选）
     *
     * @var string
     */
    private string $errorMessage;

    /**
     * 测试时间戳
     *
     * @var int
     */
    private int $timestamp;

    /**
     * 构造函数
     *
     * @param string $testName 测试名称
     * @param string $testType 测试类型（query、write、mixed）
     * @param Collection $metrics 性能指标集合
     * @param array $details 详细信息
     * @param float $totalTime 总耗时（毫秒）
     * @param bool $success 是否成功
     * @param string $errorMessage 错误信息（可选）
     * @param int $timestamp 测试时间戳
     */
    public function __construct(
        string $testName,
        string $testType,
        Collection $metrics,
        array $details,
        float $totalTime,
        bool $success = true,
        string $errorMessage = '',
        int $timestamp = 0
    ) {
        $this->testName = $testName;
        $this->testType = $testType;
        $this->metrics = $metrics;
        $this->details = $details;
        $this->totalTime = $totalTime;
        $this->success = $success;
        $this->errorMessage = $errorMessage;
        $this->timestamp = $timestamp;
    }

    /**
     * 获取测试名称
     *
     * @return string
     */
    public function getTestName(): string
    {
        return $this->testName;
    }

    /**
     * 获取测试类型
     *
     * @return string
     */
    public function getTestType(): string
    {
        return $this->testType;
    }

    /**
     * 获取性能指标集合
     *
     * @return Collection
     */
    public function getMetrics(): Collection
    {
        return $this->metrics;
    }

    /**
     * 获取详细信息
     *
     * @return array
     */
    public function getDetails(): array
    {
        return $this->details;
    }

    /**
     * 获取总耗时
     *
     * @return float
     */
    public function getTotalTime(): float
    {
        return $this->totalTime;
    }

    /**
     * 获取是否成功
     *
     * @return bool
     */
    public function isSuccess(): bool
    {
        return $this->success;
    }

    /**
     * 获取错误信息
     *
     * @return string
     */
    public function getErrorMessage(): string
    {
        return $this->errorMessage;
    }

    /**
     * 获取测试时间戳
     *
     * @return int
     */
    public function getTimestamp(): int
    {
        return $this->timestamp;
    }

    /**
     * 创建成功的测试结果
     *
     * @param string $testName 测试名称
     * @param string $testType 测试类型
     * @param Collection $metrics 性能指标集合
     * @param array $details 详细信息
     * @param float $totalTime 总耗时
     * @return self
     */
    public static function createSuccess(
        string $testName,
        string $testType,
        Collection $metrics,
        array $details,
        float $totalTime
    ): self {
        return new self(
            testName: $testName,
            testType: $testType,
            metrics: $metrics,
            details: $details,
            totalTime: $totalTime,
            success: true,
            errorMessage: '',
            timestamp: time()
        );
    }

    /**
     * 创建失败的测试结果
     *
     * @param string $testName 测试名称
     * @param string $testType 测试类型
     * @param string $errorMessage 错误信息
     * @param float $totalTime 总耗时
     * @return self
     */
    public static function createFailure(
        string $testName,
        string $testType,
        string $errorMessage,
        float $totalTime = 0.0
    ): self {
        return new self(
            testName: $testName,
            testType: $testType,
            metrics: collect(),
            details: [],
            totalTime: $totalTime,
            success: false,
            errorMessage: $errorMessage,
            timestamp: time()
        );
    }

    /**
     * 添加性能指标
     *
     * @param PerformanceMetric $metric 性能指标
     * @return void
     */
    public function addMetric(PerformanceMetric $metric): void
    {
        $this->metrics->push($metric);
    }

    /**
     * 获取指定名称的指标
     *
     * @param string $name 指标名称
     * @return PerformanceMetric|null
     */
    public function getMetric(string $name): ?PerformanceMetric
    {
        return $this->metrics->first(fn ($metric) => $metric->getName() === $name);
    }

    /**
     * 获取查询时间指标
     *
     * @return float
     */
    public function getQueryTime(): float
    {
        $metric = $this->getMetric('query_time');

        return $metric ? $metric->getValue() : 0.0;
    }

    /**
     * 获取查询次数指标
     *
     * @return int
     */
    public function getQueryCount(): int
    {
        $metric = $this->getMetric('query_count');

        return $metric ? (int) $metric->getValue() : 0;
    }

    /**
     * 获取内存使用指标
     *
     * @return float
     */
    public function getMemoryUsage(): float
    {
        $metric = $this->getMetric('memory_usage');

        return $metric ? $metric->getValue() : 0.0;
    }

    /**
     * 转换为数组
     *
     * @return array
     */
    public function toArray(): array
    {
        return [
            'test_name' => $this->getTestName(),
            'test_type' => $this->getTestType(),
            'success' => $this->isSuccess(),
            'total_time' => $this->getTotalTime(),
            'error_message' => $this->getErrorMessage(),
            'timestamp' => $this->getTimestamp(),
            'metrics' => $this->getMetrics()->map(fn ($m) => $m->toArray())->toArray(),
            'details' => $this->getDetails(),
        ];
    }

    /**
     * 转换为报告格式
     *
     * @return array
     */
    public function toReportFormat(): array
    {
        $report = [
            '测试名称' => $this->getTestName(),
            '测试类型' => $this->getTestType(),
            '测试状态' => $this->isSuccess() ? '成功' : '失败',
            '总耗时' => sprintf('%.2f ms', $this->getTotalTime()),
        ];

        if (!$this->isSuccess()) {
            $report['错误信息'] = $this->getErrorMessage();

            return $report;
        }

        // 添加性能指标
        foreach ($this->getMetrics() as $metric) {
            $report[$metric->getName()] = $metric->toReadableString();
        }

        // 添加详细信息
        if (!empty($this->getDetails())) {
            $report['详细信息'] = $this->getDetails();
        }

        return $report;
    }
}