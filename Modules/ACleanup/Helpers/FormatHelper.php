<?php

namespace Modules\AClean\Helpers;

/**
 * 格式化帮助类
 *
 * 提供各种数据格式化功能
 */
class FormatHelper
{
    /**
     * 格式化字节大小
     *
     * @param  int  $bytes  字节数
     * @param  int  $precision  精度
     * @return string 格式化后的大小
     */
    public static function formatBytes(int $bytes, int $precision = 2): string
    {
        if ($bytes == 0) {
            return '0 B';
        }

        $units = ['B', 'KB', 'MB', 'GB', 'TB', 'PB'];
        $base = log($bytes, 1024);
        $index = floor($base);

        if ($index >= count($units)) {
            $index = count($units) - 1;
        }

        $size = round(pow(1024, $base - $index), $precision);

        return $size.' '.$units[$index];
    }

    /**
     * 格式化执行时间
     *
     * @param  float  $seconds  秒数
     * @return string 格式化后的时间
     */
    public static function formatExecutionTime(float $seconds): string
    {
        if ($seconds < 1) {
            return round($seconds * 1000).' ms';
        } elseif ($seconds < 60) {
            return round($seconds, 2).' 秒';
        } elseif ($seconds < 3600) {
            $minutes = floor($seconds / 60);
            $remainingSeconds = $seconds % 60;

            return $minutes.' 分 '.round($remainingSeconds).' 秒';
        } else {
            $hours = floor($seconds / 3600);
            $minutes = floor(($seconds % 3600) / 60);

            return $hours.' 小时 '.$minutes.' 分';
        }
    }

    /**
     * 格式化数字
     *
     * @param  int|float  $number  数字
     * @return string 格式化后的数字
     */
    public static function formatNumber($number): string
    {
        if ($number >= 1000000000) {
            return round($number / 1000000000, 1).'B';
        } elseif ($number >= 1000000) {
            return round($number / 1000000, 1).'M';
        } elseif ($number >= 1000) {
            return round($number / 1000, 1).'K';
        } else {
            return number_format($number);
        }
    }

    /**
     * 格式化百分比
     *
     * @param  float  $value  值
     * @param  float  $total  总数
     * @param  int  $precision  精度
     * @return string 格式化后的百分比
     */
    public static function formatPercentage(float $value, float $total, int $precision = 2): string
    {
        if ($total == 0) {
            return '0%';
        }

        $percentage = ($value / $total) * 100;

        return round($percentage, $precision).'%';
    }

    /**
     * 格式化进度条HTML
     *
     * @param  float  $progress  进度值 (0-100)
     * @param  string  $color  颜色类型
     * @return string HTML字符串
     */
    public static function formatProgressBar(float $progress, string $color = 'primary'): string
    {
        $progress = max(0, min(100, $progress));

        return "<div class='progress' style='height: 20px;'>
            <div class='progress-bar bg-{$color}' style='width: {$progress}%'>{$progress}%</div>
        </div>";
    }

    /**
     * 根据状态获取标签颜色
     *
     * @param  int  $status  状态值
     * @param  array  $colorMap  颜色映射
     * @return string 颜色类名
     */
    public static function getStatusColor(int $status, array $colorMap = []): string
    {
        return $colorMap[$status] ?? 'secondary';
    }

    /**
     * 格式化状态标签
     *
     * @param  int  $status  状态值
     * @param  array  $statusMap  状态映射
     * @param  array  $colorMap  颜色映射
     * @return string HTML字符串
     */
    public static function formatStatusLabel(int $status, array $statusMap, array $colorMap = []): string
    {
        $text = $statusMap[$status] ?? '未知';
        $color = self::getStatusColor($status, $colorMap);

        return "<span class='badge badge-{$color}'>{$text}</span>";
    }

    /**
     * 格式化时间差
     *
     * @param  string|\DateTime  $startTime  开始时间
     * @param  string|\DateTime|null  $endTime  结束时间，null表示当前时间
     * @return string 格式化后的时间差
     */
    public static function formatTimeDiff($startTime, $endTime = null): string
    {
        if (is_string($startTime)) {
            $startTime = new \DateTime($startTime);
        }

        if ($endTime === null) {
            $endTime = new \DateTime;
        } elseif (is_string($endTime)) {
            $endTime = new \DateTime($endTime);
        }

        $diff = $endTime->diff($startTime);

        if ($diff->days > 0) {
            return $diff->days.' 天';
        } elseif ($diff->h > 0) {
            return $diff->h.' 小时 '.$diff->i.' 分钟';
        } elseif ($diff->i > 0) {
            return $diff->i.' 分钟 '.$diff->s.' 秒';
        } else {
            return $diff->s.' 秒';
        }
    }

    /**
     * 格式化文件路径显示
     *
     * @param  string  $path  文件路径
     * @param  int  $maxLength  最大显示长度
     * @return string 格式化后的路径
     */
    public static function formatPath(string $path, int $maxLength = 50): string
    {
        if (strlen($path) <= $maxLength) {
            return $path;
        }

        $start = substr($path, 0, 20);
        $end = substr($path, -($maxLength - 23));

        return $start.'...'.$end;
    }
}
