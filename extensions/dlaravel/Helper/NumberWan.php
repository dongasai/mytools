<?php

namespace DLaravel\Helper;

/**
 * 万分位数字助手类
 *
 * 专注于万分位和中文数位单位的数字格式化、转换功能
 * 支持高精度数值处理（20位整数 + 20位小数，共40位精度）
 */
class NumberWan
{
    /** @var int 默认小数精度 */
    const DEFAULT_SCALE = 20;

    /** @var array 中文数位单位配置 */
    const CHINESE_UNITS = [
        'GAI' => ['name' => '垓', 'value' => '100000000000000000000'], // 10^20
        'JING' => ['name' => '京', 'value' => '10000000000000000'],    // 10^16
        'ZHAO' => ['name' => '兆', 'value' => '1000000000000'],        // 10^12
        'YI' => ['name' => '亿', 'value' => '100000000'],              // 10^8
        'WAN' => ['name' => '万', 'value' => '10000'],                 // 10^4
    ];

    /** @var int 万分位转换的基数（向后兼容） */
    const WAN_BASE = 10000;

    /**
     * 万分位数据表示转换
     *
     * 将数字转换为中文万分位表示法，支持40位精度（20位整数+20位小数）
     * 例如：100020 -> "10万20"
     *
     * @param  int|float|string  $number  要转换的数字
     * @param  int  $scale  小数精度，默认20位
     * @return string 万分位表示的字符串
     */
    public static function formatToWan($number, int $scale = self::DEFAULT_SCALE): string
    {
        // 确保输入为字符串格式，保持精度
        $numStr = (string) $number;

        // 处理负数
        $isNegative = bccomp($numStr, '0', $scale) < 0;
        if ($isNegative) {
            $numStr = bcmul($numStr, '-1', $scale);
        }

        // 分离整数和小数部分
        $parts = explode('.', $numStr);
        $integerPart = $parts[0];
        $decimalPart = isset($parts[1]) ? $parts[1] : '';

        $result = '';

        // 处理整数部分的万分位转换
        if (bccomp($integerPart, (string) self::WAN_BASE, 0) >= 0) {
            $wan = bcdiv($integerPart, (string) self::WAN_BASE, 0);
            $remainder = bcmod($integerPart, (string) self::WAN_BASE);

            $result = $wan.'万';

            // 如果余数不为0，添加余数
            if (bccomp($remainder, '0', 0) > 0) {
                $result .= $remainder;
            }
        } else {
            // 小于1万的直接显示
            $result = $integerPart;
        }

        // 处理小数部分（如果有）
        if (! empty($decimalPart)) {
            // 去除末尾的0，但保持精度
            $decimalPart = rtrim($decimalPart, '0');
            if (! empty($decimalPart)) {
                $result .= '.'.$decimalPart;
            }
        }

        // 添加负号
        if ($isNegative) {
            $result = '-'.$result;
        }

        return $result;
    }

    /**
     * 中文大数位单位转换（高精度版本）
     *
     * 支持万、亿、兆、京、垓等中文数位单位
     * 例如：12345678901234567890 -> "1234垓5678京9012兆3456亿7890万"
     *
     * @param  int|float|string  $number  要转换的数字
     * @param  int  $scale  小数精度，默认20位
     * @param  bool  $simplify  是否简化显示（只显示最大单位），默认false
     * @return string 中文数位单位表示的字符串
     */
    public static function formatToChineseUnits($number, int $scale = self::DEFAULT_SCALE, bool $simplify = false): string
    {
        // 确保输入为字符串格式，保持精度
        $numStr = (string) $number;

        // 处理负数
        $isNegative = bccomp($numStr, '0', $scale) < 0;
        if ($isNegative) {
            $numStr = bcmul($numStr, '-1', $scale);
        }

        // 分离整数和小数部分
        $parts = explode('.', $numStr);
        $integerPart = $parts[0];
        $decimalPart = isset($parts[1]) ? $parts[1] : '';

        $result = '';
        $remainingNumber = $integerPart;

        // 按照从大到小的顺序处理各个单位
        foreach (self::CHINESE_UNITS as $unitKey => $unitInfo) {
            $unitValue = $unitInfo['value'];
            $unitName = $unitInfo['name'];

            if (bccomp($remainingNumber, $unitValue, 0) >= 0) {
                $unitCount = bcdiv($remainingNumber, $unitValue, 0);
                $remainingNumber = bcmod($remainingNumber, $unitValue);

                $result .= $unitCount.$unitName;

                // 如果是简化模式，只显示最大单位
                if ($simplify) {
                    // 如果还有余数，显示余数
                    if (bccomp($remainingNumber, '0', 0) > 0) {
                        $result .= $remainingNumber;
                    }
                    break;
                }
            }
        }

        // 如果没有匹配到任何单位，或者还有余数，直接显示数字
        if (empty($result) || (! $simplify && bccomp($remainingNumber, '0', 0) > 0)) {
            $result .= $remainingNumber;
        }

        // 处理小数部分（如果有）
        if (! empty($decimalPart)) {
            $decimalPart = rtrim($decimalPart, '0');
            if (! empty($decimalPart)) {
                $result .= '.'.$decimalPart;
            }
        }

        // 添加负号
        if ($isNegative) {
            $result = '-'.$result;
        }

        return $result;
    }

    /**
     * 智能中文数位单位转换
     *
     * 根据数字大小自动选择合适的中文单位显示
     *
     * @param  int|float|string  $number  要转换的数字
     * @param  int  $scale  小数精度，默认20位
     * @return string 智能选择的中文数位单位表示
     */
    public static function formatToSmartChineseUnits($number, int $scale = self::DEFAULT_SCALE): string
    {
        $numStr = (string) $number;

        // 获取绝对值进行判断
        $absNum = str_replace('-', '', $numStr);
        $parts = explode('.', $absNum);
        $integerPart = $parts[0];

        // 根据数字大小选择显示方式
        foreach (self::CHINESE_UNITS as $unitKey => $unitInfo) {
            if (bccomp($integerPart, $unitInfo['value'], 0) >= 0) {
                // 使用简化模式显示最大单位
                return self::formatToChineseUnits($number, $scale, true);
            }
        }

        // 小于万的数字直接显示
        return $numStr;
    }

    /**
     * 将万分位表示转换回数字
     *
     * 例如："10万20" -> "100020"
     *
     * @param  string  $wanString  万分位表示的字符串
     * @param  int  $scale  小数精度，默认20位
     * @return string 转换后的数字字符串（保持高精度）
     */
    public static function parseFromWan(string $wanString, int $scale = self::DEFAULT_SCALE): string
    {
        // 处理负数
        $isNegative = str_starts_with($wanString, '-');
        if ($isNegative) {
            $wanString = substr($wanString, 1);
        }

        // 分离整数和小数部分
        $parts = explode('.', $wanString);
        $integerPart = $parts[0];
        $decimalPart = isset($parts[1]) ? $parts[1] : '';

        // 查找"万"字符
        $wanPos = mb_strpos($integerPart, '万');

        if ($wanPos === false) {
            // 没有"万"字符，直接返回
            $result = $integerPart;
        } else {
            // 有"万"字符，分别处理万和余数部分
            $wanPart = mb_substr($integerPart, 0, $wanPos);
            $remainderPart = mb_substr($integerPart, $wanPos + 1);

            // 使用BC数学函数进行高精度计算
            $wanValue = bcmul($wanPart, (string) self::WAN_BASE, $scale);
            $remainder = empty($remainderPart) ? '0' : $remainderPart;

            $result = bcadd($wanValue, $remainder, $scale);
        }

        // 处理小数部分
        if (! empty($decimalPart)) {
            $decimalPart = rtrim($decimalPart, '0');
            if (! empty($decimalPart)) {
                // 确保结果没有重复的小数点
                if (strpos($result, '.') === false) {
                    $result .= '.'.$decimalPart;
                }
            }
        }

        // 处理负数
        if ($isNegative) {
            $result = bcmul($result, '-1', $scale);
        }

        return $result;
    }

    /**
     * 将中文数位单位表示转换回数字
     *
     * 支持万、亿、兆、京、垓等中文数位单位的反向转换
     * 例如："1234垓5678京9012兆3456亿7890万" -> "12345678901234567890000000000000"
     *
     * @param  string  $chineseString  中文数位单位表示的字符串
     * @param  int  $scale  小数精度，默认20位
     * @return string 转换后的数字字符串（保持高精度）
     */
    public static function parseFromChineseUnits(string $chineseString, int $scale = self::DEFAULT_SCALE): string
    {
        // 处理负数
        $isNegative = str_starts_with($chineseString, '-');
        if ($isNegative) {
            $chineseString = substr($chineseString, 1);
        }

        // 分离整数和小数部分
        $parts = explode('.', $chineseString);
        $integerPart = $parts[0];
        $decimalPart = isset($parts[1]) ? $parts[1] : '';

        $result = '0';
        $remainingString = $integerPart;

        // 按照从大到小的顺序处理各个单位
        foreach (self::CHINESE_UNITS as $unitInfo) {
            $unitValue = $unitInfo['value'];
            $unitName = $unitInfo['name'];

            $unitPos = mb_strpos($remainingString, $unitName);
            if ($unitPos !== false) {
                // 提取单位前的数字
                $beforeUnit = mb_substr($remainingString, 0, $unitPos);
                $afterUnit = mb_substr($remainingString, $unitPos + 1);

                if (! empty($beforeUnit)) {
                    // 计算该单位的值
                    $unitTotal = bcmul($beforeUnit, $unitValue, $scale);
                    $result = bcadd($result, $unitTotal, $scale);
                }

                // 更新剩余字符串
                $remainingString = $afterUnit;
            }
        }

        // 处理剩余的数字（没有单位的部分）
        if (! empty($remainingString) && is_numeric($remainingString)) {
            $result = bcadd($result, $remainingString, $scale);
        }

        // 处理小数部分
        if (! empty($decimalPart)) {
            // 去除末尾的0
            $decimalPart = rtrim($decimalPart, '0');
            if (! empty($decimalPart)) {
                // 确保结果是正确的小数格式
                if (strpos($result, '.') === false) {
                    $result .= '.'.$decimalPart;
                }
            }
        }

        // 处理负数
        if ($isNegative) {
            $result = bcmul($result, '-1', $scale);
        }

        return $result;
    }

    /**
     * 智能格式化
     *
     * 根据数字大小自动选择合适的格式化方式
     *
     * @param  int|float|string  $number  要格式化的数字
     * @param  int  $scale  小数精度，默认20位
     * @param  bool  $useChineseUnits  是否使用中文数位单位（万、亿、兆、京、垓），默认false
     * @return string 格式化后的字符串
     */
    public static function smartFormat($number, int $scale = self::DEFAULT_SCALE, bool $useChineseUnits = false): string
    {
        $numStr = (string) $number;

        // 获取整数部分进行比较
        $parts = explode('.', $numStr);
        $integerPart = str_replace('-', '', $parts[0]);

        // 如果启用中文数位单位，优先使用
        if ($useChineseUnits) {
            // 检查是否达到万的级别
            if (bccomp($integerPart, (string) self::WAN_BASE, 0) >= 0) {
                return self::formatToSmartChineseUnits($number, $scale);
            }
        }

        // 使用万分位表示
        if (bccomp($integerPart, (string) self::WAN_BASE, 0) >= 0) {
            return self::formatToWan($number, $scale);
        }

        // 小于万的数字直接返回
        return $numStr;
    }

    /**
     * 数字精度验证
     *
     * 验证数字是否符合指定的精度要求
     *
     * @param  string  $number  要验证的数字
     * @param  int  $integerDigits  整数位数限制，默认20位
     * @param  int  $decimalDigits  小数位数限制，默认20位
     * @return bool 是否符合精度要求
     */
    public static function validate(string $number, int $integerDigits = 20, int $decimalDigits = 20): bool
    {
        // 移除负号进行验证
        $numStr = ltrim($number, '-');

        // 分离整数和小数部分
        $parts = explode('.', $numStr);
        $integerPart = $parts[0];
        $decimalPart = isset($parts[1]) ? $parts[1] : '';

        // 验证整数位数
        if (strlen($integerPart) > $integerDigits) {
            return false;
        }

        // 验证小数位数
        if (strlen($decimalPart) > $decimalDigits) {
            return false;
        }

        return true;
    }

    /**
     * 数字精度截取
     *
     * 将数字截取到指定精度
     *
     * @param  string  $number  要截取的数字
     * @param  int  $integerDigits  整数位数限制，默认20位
     * @param  int  $decimalDigits  小数位数限制，默认20位
     * @return string 截取后的数字
     */
    public static function truncate(string $number, int $integerDigits = 20, int $decimalDigits = 20): string
    {
        // 处理负数
        $isNegative = str_starts_with($number, '-');
        $numStr = $isNegative ? substr($number, 1) : $number;

        // 分离整数和小数部分
        $parts = explode('.', $numStr);
        $integerPart = $parts[0];
        $decimalPart = isset($parts[1]) ? $parts[1] : '';

        // 截取整数部分（从右边开始保留指定位数）
        if (strlen($integerPart) > $integerDigits) {
            $integerPart = substr($integerPart, -$integerDigits);
        }

        // 截取小数部分
        if (strlen($decimalPart) > $decimalDigits) {
            $decimalPart = substr($decimalPart, 0, $decimalDigits);
        }

        // 组装结果
        $result = $integerPart;
        if (! empty($decimalPart)) {
            $result .= '.'.rtrim($decimalPart, '0');
        }

        return $isNegative ? '-'.$result : $result;
    }
}
