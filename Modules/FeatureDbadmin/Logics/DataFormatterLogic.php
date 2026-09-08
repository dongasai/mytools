<?php

namespace Modules\FeatureDbadmin\Logics;

/**
 * 数据格式化逻辑
 *
 * 提供格式化数据输出功能
 */
class DataFormatterLogic
{
    /**
     * 格式化值
     *
     * 根据 PHP 类型或数据库类型格式化
     * - NULL → 'NULL'
     * - bool → 'true'/'false'
     * - JSON → 格式化 JSON
     * - 其他类型转字符串
     *
     * @param mixed $value 原始值
     * @param string $type 类型标识
     * @return string 格式化后的字符串
     */
    public static function formatValue(mixed $value, string $type = 'string'): string
    {
        // NULL 处理
        if ($value === null) {
            return 'NULL';
        }

        // 布尔值处理
        if (is_bool($value)) {
            return $value ? 'true' : 'false';
        }

        // JSON 类型处理
        $jsonTypes = ['json', 'jsonb', 'array', 'object'];
        if (in_array(strtolower($type), $jsonTypes, true)) {
            if (is_string($value)) {
                // 尝试解析并重新格式化
                $decoded = json_decode($value, true);
                if ($decoded !== null || $value === 'null') {
                    return json_encode($decoded, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
                }
            } elseif (is_array($value) || is_object($value)) {
                return json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            }
        }

        // 日期时间类型
        $dateTypes = ['date', 'datetime', 'timestamp', 'time'];
        if (in_array(strtolower($type), $dateTypes, true)) {
            if ($value instanceof \DateTimeInterface) {
                return $value->format('Y-m-d H:i:s');
            }
        }

        // 数组处理
        if (is_array($value)) {
            return json_encode($value, JSON_UNESCAPED_UNICODE);
        }

        // 对象处理
        if (is_object($value)) {
            if (method_exists($value, '__toString')) {
                return (string) $value;
            }

            return json_encode($value, JSON_UNESCAPED_UNICODE);
        }

        // 浮点数处理
        if (is_float($value)) {
            // 避免科学计数法，保留足够精度
            if (abs($value) >= 1e6 || (abs($value) < 1e-6 && $value != 0)) {
                return sprintf('%.10f', $value);
            }

            return rtrim(rtrim(sprintf('%.10f', $value), '0'), '.');
        }

        // 默认转字符串
        return (string) $value;
    }

    /**
     * 数组转 CSV
     *
     * 第一行为列名，处理逗号、引号、换行符转义
     *
     * @param array $data 数据数组，第一行应为列名
     * @return string CSV 格式字符串
     */
    public static function arrayToCsv(array $data): string
    {
        if (empty($data)) {
            return '';
        }

        $output = fopen('php://temp', 'r+');

        foreach ($data as $row) {
            $formattedRow = [];
            foreach ($row as $value) {
                $formattedRow[] = self::formatValue($value, 'string');
            }
            fputcsv($output, $formattedRow);
        }

        rewind($output);
        $csv = stream_get_contents($output);
        fclose($output);

        return $csv ?: '';
    }

    /**
     * 数组转 JSON
     *
     * 处理中文编码，可选格式化输出
     *
     * @param array $data 数据数组
     * @param bool $pretty 是否格式化输出
     * @return string JSON 格式字符串
     */
    public static function arrayToJson(array $data, bool $pretty = false): string
    {
        $flags = JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES;

        if ($pretty) {
            $flags |= JSON_PRETTY_PRINT;
        }

        $result = json_encode($data, $flags);

        return $result !== false ? $result : '[]';
    }

    /**
     * 格式化文件大小
     *
     * 将字节数转换为人类可读格式
     *
     * @param int $bytes 字节数
     * @return string 格式化后的大小
     */
    public static function formatFileSize(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB', 'PB'];
        $unitIndex = 0;

        $size = $bytes;
        while ($size >= 1024 && $unitIndex < count($units) - 1) {
            $size /= 1024;
            $unitIndex++;
        }

        $decimals = $size < 10 ? 2 : ($size < 100 ? 1 : 0);

        return round($size, $decimals) . ' ' . $units[$unitIndex];
    }

    /**
     * 格式化执行时间
     *
     * 将微秒数转换为人类可读格式
     *
     * @param float $microseconds 微秒数
     * @return string 格式化后的时间
     */
    public static function formatExecutionTime(float $microseconds): string
    {
        if ($microseconds < 1000) {
            return round($microseconds, 2) . ' μs';
        } elseif ($microseconds < 1000000) {
            return round($microseconds / 1000, 2) . ' ms';
        } else {
            return round($microseconds / 1000000, 3) . ' s';
        }
    }
}
