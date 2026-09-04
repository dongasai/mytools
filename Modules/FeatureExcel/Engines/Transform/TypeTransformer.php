<?php

declare(strict_types=1);

namespace Modules\FeatureExcel\Engines\Transform;

use Carbon\Carbon;
use Modules\Application\Services\SystemLogService;

/**
 * 类型转换器（导入用）
 *
 * 原始值 → 业务值，含时区处理
 */
class TypeTransformer
{
    /**
     * 类型转换主入口
     *
     * @param mixed $value 原始值
     * @param string $transform 转换规则：int/float/trim/bool/datetime:format/date:format
     * @param string $type 数据类型
     * @param string $timezoneFrom 源时区
     * @param string $timezoneTo 目标时区
     * @return mixed 转换后的值
     */
    public static function transform(
        mixed $value,
        string $transform,
        string $type = 'string',
        string $timezoneFrom = 'Asia/Shanghai',
        string $timezoneTo = 'UTC'
    ): mixed {
        if ($value === null || $value === '') {
            return null;
        }

        try {
            // 显式转换规则优先
            if ($transform !== '') {
                return match ($transform) {
                    'int' => (int) $value,
                    'float' => (float) $value,
                    'trim' => trim((string) $value),
                    'bool' => filter_var($value, FILTER_VALIDATE_BOOLEAN),
                    default => self::transformDatetime($value, $transform, $timezoneFrom, $timezoneTo),
                };
            }

            // 无 transform 时按 type 默认转换
            return match ($type) {
                'integer' => (int) $value,
                'float' => (float) $value,
                'boolean' => filter_var($value, FILTER_VALIDATE_BOOLEAN),
                default => $value,
            };
        } catch (\Throwable $e) {
            SystemLogService::exception('feature_excel', $e, [
                'operation' => 'transform',
                'value' => $value,
                'transform' => $transform,
                'type' => $type,
            ]);
            throw $e;
        }
    }

    /**
     * 日期时间转换（含时区）
     *
     * @param mixed $value 原始值
     * @param string $transform 转换规则（datetime:format 或 date:format）
     * @param string $timezoneFrom 源时区
     * @param string $timezoneTo 目标时区
     * @return Carbon|string 转换后的日期对象或字符串
     */
    private static function transformDatetime(
        mixed $value,
        string $transform,
        string $timezoneFrom,
        string $timezoneTo
    ): mixed {
        // datetime:Y-m-d H:i:s 格式
        if (str_starts_with($transform, 'datetime:')) {
            $format = substr($transform, 9);
            $dt = Carbon::createFromFormat($format, (string) $value, $timezoneFrom);
            $dt->setTimezone($timezoneTo);
            return $dt;
        }

        // date:Y-m-d 格式
        if (str_starts_with($transform, 'date:')) {
            $format = substr($transform, 5);
            $dt = Carbon::createFromFormat($format, (string) $value, $timezoneFrom);
            $dt->setTimezone($timezoneTo);
            return $dt->toDateString();
        }

        return $value;
    }
}
