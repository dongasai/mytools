<?php

declare(strict_types=1);

namespace Modules\Application\Dtos;

/**
 * 性能测试指标数据传输对象
 *
 * 用于封装单个性能测试的指标数据
 */
class PerformanceMetric
{
    /**
     * 指标名称
     *
     * @var string
     */
    private string $name;

    /**
     * 指标值
     *
     * @var float
     */
    private float $value;

    /**
     * 单位（ms、queries、bytes等）
     *
     * @var string
     */
    private string $unit;

    /**
     * 最小值（可选）
     *
     * @var float
     */
    private float $min;

    /**
     * 最大值（可选）
     *
     * @var float
     */
    private float $max;

    /**
     * 平均值（可选）
     *
     * @var float
     */
    private float $avg;

    /**
     * 描述信息
     *
     * @var string
     */
    private string $description;

    /**
     * 构造函数
     *
     * @param string $name 指标名称
     * @param float $value 指标值
     * @param string $unit 单位（ms、queries、bytes等）
     * @param float $min 最小值（可选）
     * @param float $max 最大值（可选）
     * @param float $avg 平均值（可选）
     * @param string $description 描述信息
     */
    public function __construct(
        string $name,
        float $value,
        string $unit,
        float $min = 0.0,
        float $max = 0.0,
        float $avg = 0.0,
        string $description = ''
    ) {
        $this->name = $name;
        $this->value = $value;
        $this->unit = $unit;
        $this->min = $min;
        $this->max = $max;
        $this->avg = $avg;
        $this->description = $description;
    }

    /**
     * 获取指标名称
     *
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * 获取指标值
     *
     * @return float
     */
    public function getValue(): float
    {
        return $this->value;
    }

    /**
     * 获取单位
     *
     * @return string
     */
    public function getUnit(): string
    {
        return $this->unit;
    }

    /**
     * 获取最小值
     *
     * @return float
     */
    public function getMin(): float
    {
        return $this->min;
    }

    /**
     * 获取最大值
     *
     * @return float
     */
    public function getMax(): float
    {
        return $this->max;
    }

    /**
     * 获取平均值
     *
     * @return float
     */
    public function getAvg(): float
    {
        return $this->avg;
    }

    /**
     * 获取描述信息
     *
     * @return string
     */
    public function getDescription(): string
    {
        return $this->description;
    }

    /**
     * 创建查询时间指标
     *
     * @param float $time 查询时间（毫秒）
     * @return self
     */
    public static function createQueryTime(float $time): self
    {
        return new self(
            name: 'query_time',
            value: $time,
            unit: 'ms',
            description: '查询执行时间'
        );
    }

    /**
     * 创建查询次数指标
     *
     * @param int $count 查询次数
     * @return self
     */
    public static function createQueryCount(int $count): self
    {
        return new self(
            name: 'query_count',
            value: (float) $count,
            unit: 'queries',
            description: '执行的查询次数'
        );
    }

    /**
     * 创建内存使用指标
     *
     * @param float $memory 内存使用量（字节）
     * @return self
     */
    public static function createMemoryUsage(float $memory): self
    {
        return new self(
            name: 'memory_usage',
            value: $memory,
            unit: 'bytes',
            description: '内存使用量'
        );
    }

    /**
     * 转换为数组
     *
     * @return array
     */
    public function toArray(): array
    {
        return [
            'name' => $this->getName(),
            'value' => $this->getValue(),
            'unit' => $this->getUnit(),
            'min' => $this->getMin(),
            'max' => $this->getMax(),
            'avg' => $this->getAvg(),
            'description' => $this->getDescription(),
        ];
    }

    /**
     * 格式化为人类可读字符串
     *
     * @return string
     */
    public function toReadableString(): string
    {
        $formattedValue = $this->formatValue($this->getValue(), $this->getUnit());

        if ($this->getMin() > 0 || $this->getMax() > 0) {
            $formattedMin = $this->formatValue($this->getMin(), $this->getUnit());
            $formattedMax = $this->formatValue($this->getMax(), $this->getUnit());
            $formattedAvg = $this->formatValue($this->getAvg(), $this->getUnit());

            return sprintf(
                '%s: %s (最小: %s, 最大: %s, 平均: %s)',
                $this->getName(),
                $formattedValue,
                $formattedMin,
                $formattedMax,
                $formattedAvg
            );
        }

        return sprintf('%s: %s', $this->getName(), $formattedValue);
    }

    /**
     * 格式化值
     *
     * @param float $value 值
     * @param string $unit 单位
     * @return string
     */
    private function formatValue(float $value, string $unit): string
    {
        switch ($unit) {
            case 'ms':
                return sprintf('%.2f ms', $value);
            case 'bytes':
                return sprintf('%.2f MB', $value / 1024 / 1024);
            case 'queries':
                return sprintf('%d 次', (int) $value);
            default:
                return sprintf('%.2f %s', $value, $unit);
        }
    }
}