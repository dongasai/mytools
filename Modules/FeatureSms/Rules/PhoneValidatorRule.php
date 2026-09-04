<?php

namespace Modules\FeatureSms\Rules;

use Illuminate\Contracts\Validation\ValidationRule;

/**
 * 手机验证器规则
 *
 * 重构自 PhoneValidator，实现Laravel 12的ValidationRule接口
 * 验证手机号码的有效性、格式和业务规则
 */
class PhoneValidatorRule implements ValidationRule
{
    /**
     * 验证参数
     */
    protected array $args;

    /**
     * 错误消息
     */
    protected string $message;

    /**
     * 构造函数
     *
     * @param  mixed  $validation  验证对象
     * @param  array  $args  参数数组
     * @param  string|null  $message  自定义错误消息
     */
    public function __construct($validation, array $args = [], ?string $message = null)
    {
        $this->args = $args;
        $this->message = $message ?? '手机号码验证失败';
    }

    /**
     * 验证指定的属性
     *
     * @param  string  $attribute  属性名
     * @param  mixed  $value  属性值
     * @param  \Closure  $fail  失败回调
     */
    public function validate(string $attribute, mixed $value, \Closure $fail): void
    {
        $phone = $value;

        if (! $phone) {
            $fail('手机号码不能为空');

            return;
        }

        // 验证手机号码格式
        if (! $this->isValidFormat($phone)) {
            $fail('手机号码格式不正确');

            return;
        }

        // 验证手机号码类型
        $phoneType = $this->getPhoneType($phone);
        if (! $this->isSupportedType($phoneType)) {
            $fail('不支持的手机号码类型');

            return;
        }

        // 检查是否为虚拟号码
        if ($this->isVirtualNumber($phone)) {
            $fail('不支持虚拟手机号码');

            return;
        }

        // 检查是否在黑名单中
        if ($this->isInBlacklist($phone)) {
            $fail('该手机号码已被限制使用');

            return;
        }

        // 验证运营商支持
        if (! $this->isSupportedCarrier($phone)) {
            $fail('该手机号码运营商暂不支持');

            return;
        }

        // 可选：检查用户是否存在
        $checkUser = $this->args[0] ?? false;
        if ($checkUser && ! $this->userExists($phone)) {
            $fail('该手机号码未注册');

            return;
        }

        // 可选：检查是否已绑定
        $checkBinding = $this->args[1] ?? false;
        if ($checkBinding && $this->isAlreadyBound($phone)) {
            $fail('该手机号码已被绑定');

            return;
        }

        // 可选：将手机信息保存到请求中
        $infoFieldKey = $this->args[2] ?? null;
        if ($infoFieldKey) {
            request()->merge([$infoFieldKey => [
                'phone' => $phone,
                'type' => $phoneType,
                'carrier' => $this->getCarrier($phone),
                'province' => $this->getProvince($phone),
                'city' => $this->getCity($phone),
            ]]);
        }
    }

    /**
     * 验证手机号码格式
     *
     * @param  string  $phone  手机号码
     */
    protected function isValidFormat(string $phone): bool
    {
        // 中国大陆手机号正则表达式
        $pattern = '/^1[3-9]\d{9}$/';

        return preg_match($pattern, $phone) === 1;
    }

    /**
     * 获取手机号码类型
     *
     * @param  string  $phone  手机号码
     */
    protected function getPhoneType(string $phone): string
    {
        $prefix = substr($phone, 0, 3);

        // 移动号码段
        $mobilePrefixes = ['134', '135', '136', '137', '138', '139', '147', '150', '151', '152', '157', '158', '159', '172', '178', '182', '183', '184', '187', '188', '198'];

        // 联通号码段
        $unicomPrefixes = ['130', '131', '132', '145', '155', '156', '166', '171', '175', '176', '185', '186'];

        // 电信号码段
        $telecomPrefixes = ['133', '149', '153', '173', '177', '180', '181', '189', '199'];

        if (in_array($prefix, $mobilePrefixes, true)) {
            return 'mobile';
        } elseif (in_array($prefix, $unicomPrefixes, true)) {
            return 'unicom';
        } elseif (in_array($prefix, $telecomPrefixes, true)) {
            return 'telecom';
        }

        return 'unknown';
    }

    /**
     * 检查是否为支持的类型
     *
     * @param  string  $phoneType  手机类型
     */
    protected function isSupportedType(string $phoneType): bool
    {
        return in_array($phoneType, ['mobile', 'unicom', 'telecom'], true);
    }

    /**
     * 检查是否为虚拟号码
     *
     * @param  string  $phone  手机号码
     */
    protected function isVirtualNumber(string $phone): bool
    {
        // 虚拟号码前缀
        $virtualPrefixes = ['170', '171'];
        $prefix = substr($phone, 0, 3);

        return in_array($prefix, $virtualPrefixes, true);
    }

    /**
     * 检查是否在黑名单中
     *
     * @param  string  $phone  手机号码
     */
    protected function isInBlacklist(string $phone): bool
    {
        try {
            // 这里应该调用黑名单服务检查
            // 暂时返回false
            return false;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * 检查运营商是否支持
     *
     * @param  string  $phone  手机号码
     */
    protected function isSupportedCarrier(string $phone): bool
    {
        try {
            $phoneType = $this->getPhoneType($phone);

            return $this->isSupportedType($phoneType);
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * 检查用户是否存在
     *
     * @param  string  $phone  手机号码
     */
    protected function userExists(string $phone): bool
    {
        try {
            // 这里应该调用用户服务检查
            // 暂时返回true
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * 检查是否已绑定
     *
     * @param  string  $phone  手机号码
     */
    protected function isAlreadyBound(string $phone): bool
    {
        try {
            // 这里应该调用绑定服务检查
            // 暂时返回false
            return false;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * 获取运营商
     *
     * @param  string  $phone  手机号码
     */
    protected function getCarrier(string $phone): string
    {
        $phoneType = $this->getPhoneType($phone);

        return match ($phoneType) {
            'mobile' => '中国移动',
            'unicom' => '中国联通',
            'telecom' => '中国电信',
            default => '未知运营商',
        };
    }

    /**
     * 获取省份
     *
     * @param  string  $phone  手机号码
     */
    protected function getProvince(string $phone): string
    {
        try {
            // 这里应该调用手机号归属地服务
            // 暂时返回默认值
            return '未知省份';
        } catch (\Exception $e) {
            return '未知省份';
        }
    }

    /**
     * 获取城市
     *
     * @param  string  $phone  手机号码
     */
    protected function getCity(string $phone): string
    {
        try {
            // 这里应该调用手机号归属地服务
            // 暂时返回默认值
            return '未知城市';
        } catch (\Exception $e) {
            return '未知城市';
        }
    }

    /**
     * 静态验证方法
     *
     * @param  string  $phone  手机号码
     */
    public static function validatePhone(string $phone): bool
    {
        try {
            $instance = new static(null);

            return $instance->isValidFormat($phone) &&
                ! $instance->isVirtualNumber($phone) &&
                ! $instance->isInBlacklist($phone) &&
                $instance->isSupportedCarrier($phone);
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * 获取手机信息
     *
     * @param  string  $phone  手机号码
     */
    public static function getPhoneInfo(string $phone): array
    {
        try {
            $instance = new static(null);

            return [
                'phone' => $phone,
                'valid_format' => $instance->isValidFormat($phone),
                'type' => $instance->getPhoneType($phone),
                'carrier' => $instance->getCarrier($phone),
                'province' => $instance->getProvince($phone),
                'city' => $instance->getCity($phone),
                'is_virtual' => $instance->isVirtualNumber($phone),
                'in_blacklist' => $instance->isInBlacklist($phone),
                'supported' => $instance->isSupportedCarrier($phone),
            ];
        } catch (\Exception $e) {
            return [
                'phone' => $phone,
                'valid_format' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * 格式化手机号码
     *
     * @param  string  $phone  手机号码
     */
    public static function formatPhone(string $phone): string
    {
        // 移除非数字字符
        $cleaned = preg_replace('/\D/', '', $phone);

        // 只保留11位
        return substr($cleaned, 0, 11);
    }

    /**
     * 隐藏手机号码中间部分
     *
     * @param  string  $phone  手机号码
     */
    public static function maskPhone(string $phone): string
    {
        if (strlen($phone) !== 11) {
            return $phone;
        }

        return substr($phone, 0, 3) . '****' . substr($phone, 7);
    }

    /**
     * 创建静态实例
     *
     * @param  mixed  $validation  验证对象
     * @param  array  $args  参数数组
     * @param  string|null  $message  自定义错误消息
     */
    public static function make($validation, array $args = [], ?string $message = null): static
    {
        return new static($validation, $args, $message);
    }
}
