<?php

declare(strict_types=1);

namespace Modules\FeatureExcel\Engines\Transform;

use Carbon\Carbon;
use Modules\Application\Services\SystemLogService;

/**
 * 值格式化器（导出用）
 *
 * 业务值 → 显示值，含时区处理
 */
class ValueTransformer
{
    /**
     * 值格式化主入口
     *
     * @param mixed $value 业务值
     * @param string $format 格式规则：number:N / date:format
     * @param string $type 数据类型
     * @param string $timezoneFrom 源时区
     * @param string $timezoneTo 目标时区
     * @return mixed 格式化后的值
     */
    public static function format(
        mixed $value,
        string $format,
        string $type = 'string',
        string $timezoneFrom = 'UTC',
        string $timezoneTo = 'Asia/Shanghai'
    ): mixed {
        if ($value === null) {
            return '';
        }

        try {
            // 显式格式规则优先
            if ($format !== '') {
                // number:N 格式（保留N位小数）
                if (str_starts_with($format, 'number:')) {
                    $decimals = (int) substr($format, 7);
                    return number_format((float) $value, $decimals, '.', '');
                }

                // date:format 格式
                if (str_starts_with($format, 'date:')) {
                    $dateFormat = substr($format, 5);
                    return Carbon::parse($value)->setTimezone($timezoneTo)->format($dateFormat);
                }
            }

            // 无 format 时按 type 默认格式化
            return match ($type) {
                'date' => Carbon::parse($value)->setTimezone($timezoneTo)->toDateString(),
                'datetime' => Carbon::parse($value)->setTimezone($timezoneTo)->format('Y-m-d H:i:s'),
                'float' => (float) $value,
                'integer' => (int) $value,
                'boolean' => $value ? '是' : '否',
                default => (string) $value,
            };
        } catch (\Throwable $e) {
            SystemLogService::exception('feature_excel', $e, [
                'operation' => 'format',
                'value' => $value,
                'format' => $format,
                'type' => $type,
            ]);
            throw $e;
        }
    }
}
